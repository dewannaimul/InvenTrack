<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PointOfSale extends Page
{
    protected string $view = 'filament.pages.point-of-sale';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected static ?int $navigationSort = -10;

    public ?int $warehouseId = null;

    public ?int $customerId = null;

    public string $search = '';

    public ?int $categoryId = null;

    public string $barcodeInput = '';

    /** @var array<string, array<string, mixed>> */
    public array $cart = [];

    public bool $showCheckoutModal = false;

    public bool $showNewCustomerModal = false;

    /** @var array<int, array<string, mixed>> */
    public array $tenders = [];

    public string $newCustomerName = '';

    public string $newCustomerPhone = '';

    public ?string $checkoutError = null;

    public static function getNavigationLabel(): string
    {
        return 'Point of Sale';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('View:PointOfSale') ?? false;
    }

    public function mount(): void
    {
        $this->warehouseId = CompanySetting::current()->default_warehouse_id
            ?? Warehouse::query()->where('is_default', true)->value('id')
            ?? Warehouse::query()->value('id');

        $this->customerId = $this->walkInCustomer()->id;
        $this->resetTenders();
    }

    protected function walkInCustomer(): Customer
    {
        return Customer::query()->firstOrCreate(
            ['name' => 'Walk-in Customer'],
            ['customer_type' => 'retail', 'is_active' => true],
        );
    }

    public function getProductsProperty()
    {
        return Product::query()
            ->where('is_active', true)
            ->where('track_stock', true)
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%")
                        ->orWhere('barcode', 'like', "%{$this->search}%");
                });
            })
            ->with(['variants' => fn ($q) => $q->where('is_active', true)])
            ->withSum(['stocks as stock_quantity' => fn ($q) => $q->where('warehouse_id', $this->warehouseId)], 'quantity')
            ->orderBy('name')
            ->limit(60)
            ->get();
    }

    public function getCategoriesProperty()
    {
        return Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getHeldSalesProperty()
    {
        return SalesOrder::query()
            ->where('source', SalesOrder::SOURCE_POS)
            ->where('status', SalesOrder::STATUS_DRAFT)
            ->with('customer')
            ->latest()
            ->get();
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function scanBarcode(): void
    {
        $code = trim($this->barcodeInput);
        $this->barcodeInput = '';

        if ($code === '') {
            return;
        }

        $variant = ProductVariant::query()->where('barcode', $code)->first();

        if ($variant) {
            $this->addToCart($variant->product_id, $variant->id);

            return;
        }

        $product = Product::query()->where('barcode', $code)->orWhere('sku', $code)->first();

        if ($product) {
            $this->addToCart($product->id, null);

            return;
        }

        Notification::make()
            ->title("No product found for \"{$code}\"")
            ->warning()
            ->send();
    }

    public function addToCart(int $productId, ?int $variantId = null): void
    {
        $product = Product::query()->findOrFail($productId);
        $variant = $variantId ? ProductVariant::query()->findOrFail($variantId) : null;

        $available = app(StockService::class)->quantityOnHand($productId, $variantId, $this->warehouseId);
        $key = $productId.'-'.($variantId ?? '0');

        $currentQty = $this->cart[$key]['quantity'] ?? 0;

        if ($product->track_stock && $currentQty + 1 > $available) {
            Notification::make()
                ->title('Not enough stock')
                ->body("Only {$available} available for {$product->name}.")
                ->warning()
                ->send();

            return;
        }

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity']++;
        } else {
            $this->cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => $product->name.($variant ? ' ('.$variant->label.')' : ''),
                'sku' => $variant?->sku ?? $product->sku,
                'unit_price' => (float) ($variant?->selling_price ?? $product->selling_price),
                'quantity' => 1,
                'discount' => 0,
                'available' => $available,
            ];
        }
    }

    public function incrementQty(string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        if ($this->cart[$key]['quantity'] + 1 > $this->cart[$key]['available']) {
            Notification::make()->title('Not enough stock')->warning()->send();

            return;
        }

        $this->cart[$key]['quantity']++;
    }

    public function decrementQty(string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['quantity']--;

        if ($this->cart[$key]['quantity'] <= 0) {
            unset($this->cart[$key]);
        }
    }

    public function removeFromCart(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->customerId = $this->walkInCustomer()->id;
    }

    public function getCartTotalsProperty(): array
    {
        $subtotal = 0;
        $discount = 0;

        foreach ($this->cart as $line) {
            $subtotal += $line['unit_price'] * $line['quantity'];
            $discount += $line['discount'];
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => max(0, $subtotal - $discount),
        ];
    }

    public function openNewCustomerModal(): void
    {
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->showNewCustomerModal = true;
    }

    public function saveNewCustomer(): void
    {
        if (trim($this->newCustomerName) === '') {
            return;
        }

        $customer = Customer::create([
            'name' => $this->newCustomerName,
            'phone' => $this->newCustomerPhone ?: null,
            'customer_type' => 'retail',
            'is_active' => true,
        ]);

        $this->customerId = $customer->id;
        $this->showNewCustomerModal = false;

        Notification::make()->title('Customer added')->success()->send();
    }

    public function holdSale(): void
    {
        if (empty($this->cart)) {
            return;
        }

        $order = $this->persistOrder(SalesOrder::STATUS_DRAFT);

        $this->cart = [];
        $this->customerId = $this->walkInCustomer()->id;

        Notification::make()->title("Sale held as {$order->order_number}")->success()->send();
    }

    public function resumeSale(int $salesOrderId): void
    {
        $order = SalesOrder::with('items')->findOrFail($salesOrderId);

        $this->cart = [];

        foreach ($order->items as $item) {
            $key = $item->product_id.'-'.($item->product_variant_id ?? '0');
            $available = app(StockService::class)->quantityOnHand($item->product_id, $item->product_variant_id, $this->warehouseId);

            $this->cart[$key] = [
                'product_id' => $item->product_id,
                'variant_id' => $item->product_variant_id,
                'name' => $item->product->name,
                'sku' => $item->productVariant?->sku ?? $item->product->sku,
                'unit_price' => (float) $item->unit_price,
                'quantity' => $item->quantity,
                'discount' => (float) $item->discount,
                'available' => $available,
            ];
        }

        $this->customerId = $order->customer_id;
        $this->warehouseId = $order->warehouse_id;

        $order->items()->delete();
        $order->delete();

        Notification::make()->title('Sale resumed')->success()->send();
    }

    public function deleteHeldSale(int $salesOrderId): void
    {
        $order = SalesOrder::findOrFail($salesOrderId);
        $order->items()->delete();
        $order->delete();

        Notification::make()->title('Held sale discarded')->success()->send();
    }

    public function resetTenders(): void
    {
        $this->tenders = [
            ['method' => 'cash', 'amount' => 0],
        ];
    }

    public function openCheckout(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Cart is empty')->warning()->send();

            return;
        }

        $this->resetTenders();
        $this->tenders[0]['amount'] = $this->cartTotals['total'];
        $this->checkoutError = null;
        $this->showCheckoutModal = true;
    }

    public function addTenderLine(): void
    {
        $this->tenders[] = ['method' => 'cash', 'amount' => 0];
    }

    public function removeTenderLine(int $index): void
    {
        unset($this->tenders[$index]);
        $this->tenders = array_values($this->tenders);
    }

    public function getTenderTotalProperty(): float
    {
        return array_sum(array_column($this->tenders, 'amount'));
    }

    public function getChangeDueProperty(): float
    {
        $cashTendered = collect($this->tenders)->where('method', 'cash')->sum('amount');
        $nonCashTendered = collect($this->tenders)->where('method', '!=', 'cash')->sum('amount');
        $total = $this->cartTotals['total'];
        $remainingAfterNonCash = max(0, $total - $nonCashTendered);

        return max(0, $cashTendered - $remainingAfterNonCash);
    }

    protected function persistOrder(string $status): SalesOrder
    {
        return DB::transaction(function () use ($status) {
            $totals = $this->cartTotals;

            $order = SalesOrder::create([
                'order_number' => 'SO-'.now()->format('Ymd').'-'.strtoupper(Str::random(4)),
                'customer_id' => $this->customerId,
                'warehouse_id' => $this->warehouseId,
                'status' => $status,
                'source' => SalesOrder::SOURCE_POS,
                'order_date' => now(),
                'subtotal' => $totals['subtotal'],
                'tax_total' => 0,
                'discount_total' => $totals['discount'],
                'total' => $totals['total'],
                'amount_paid' => 0,
                'payment_status' => 'unpaid',
                'created_by' => auth()->id(),
            ]);

            foreach ($this->cart as $line) {
                $order->items()->create([
                    'product_id' => $line['product_id'],
                    'product_variant_id' => $line['variant_id'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount' => $line['discount'],
                    'subtotal' => ($line['unit_price'] * $line['quantity']) - $line['discount'],
                ]);
            }

            return $order;
        });
    }

    public function completeSale()
    {
        $totals = $this->cartTotals;

        if (round($this->tenderTotal - $this->changeDue, 2) < round($totals['total'], 2)) {
            $this->checkoutError = 'Amount tendered is less than the total due.';

            return;
        }

        try {
            $order = DB::transaction(function () use ($totals) {
                $order = $this->persistOrder(SalesOrder::STATUS_CONFIRMED);

                $stockService = app(StockService::class);

                foreach ($order->items as $item) {
                    $stockService->move(
                        productId: $item->product_id,
                        productVariantId: $item->product_variant_id,
                        warehouseId: $order->warehouse_id,
                        quantityDelta: -$item->quantity,
                        type: StockMovement::TYPE_SALE,
                        reference: $order,
                        note: "POS sale {$order->order_number}",
                        userId: auth()->id(),
                    );
                }

                $remainingChange = $this->changeDue;

                foreach ($this->tenders as $tender) {
                    $amount = (float) $tender['amount'];

                    if ($amount <= 0) {
                        continue;
                    }

                    $isCash = $tender['method'] === 'cash';
                    $changeForLine = null;
                    $appliedAmount = $amount;

                    if ($isCash && $remainingChange > 0) {
                        $changeForLine = min($remainingChange, $amount);
                        $appliedAmount = $amount - $changeForLine;
                        $remainingChange -= $changeForLine;
                    }

                    $order->payments()->create([
                        'amount' => $appliedAmount,
                        'tendered_amount' => $isCash ? $amount : null,
                        'change_due' => $changeForLine,
                        'method' => $tender['method'],
                        'paid_on' => now(),
                        'created_by' => auth()->id(),
                    ]);
                }

                $order->update([
                    'amount_paid' => $totals['total'],
                    'payment_status' => 'paid',
                    'status' => SalesOrder::STATUS_COMPLETED,
                ]);

                return $order;
            });
        } catch (RuntimeException $exception) {
            $this->checkoutError = $exception->getMessage();

            return;
        }

        $this->cart = [];
        $this->customerId = $this->walkInCustomer()->id;
        $this->showCheckoutModal = false;

        $this->redirect(route('documents.pos.receipt', $order), navigate: false);
    }
}

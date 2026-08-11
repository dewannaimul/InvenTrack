<x-filament-panels::page>
    <style>
        .pos-wrap { display: grid; grid-template-columns: 1fr 380px; gap: 1rem; align-items: start; }
        @media (max-width: 1024px) { .pos-wrap { grid-template-columns: 1fr; } }

        .pos-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1rem; }
        html.dark .pos-panel { background: #1e293b; border-color: #334155; }

        .pos-toolbar { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .pos-toolbar > * { flex: 1 1 200px; }

        .pos-input {
            width: 100%; border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem 0.75rem;
            font-size: 0.875rem; background: #fff; color: #111827;
        }
        .pos-input:focus { outline: 2px solid #6366f1; outline-offset: 1px; }
        html.dark .pos-input { background: #0f172a; border-color: #334155; color: #f1f5f9; }

        .pos-barcode-input { font-family: ui-monospace, monospace; border-color: #6366f1; }

        .pos-categories { display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.75rem; margin-bottom: 0.75rem; border-bottom: 1px solid #e5e7eb; }
        html.dark .pos-categories { border-color: #334155; }
        .pos-tab {
            white-space: nowrap; padding: 0.375rem 0.875rem; border-radius: 9999px; font-size: 0.8125rem;
            font-weight: 500; border: 1px solid #d1d5db; background: #fff; color: #374151; cursor: pointer;
        }
        .pos-tab.active { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        html.dark .pos-tab { background: #0f172a; border-color: #334155; color: #cbd5e1; }
        html.dark .pos-tab.active { background: #6366f1; border-color: #6366f1; color: #fff; }

        .pos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.75rem; max-height: 65vh; overflow-y: auto; padding: 0.25rem; }

        .pos-card {
            border: 1px solid #e5e7eb; border-radius: 0.625rem; padding: 0.75rem; cursor: pointer; background: #fff;
            display: flex; flex-direction: column; gap: 0.25rem; transition: border-color .1s, transform .05s;
            font: inherit; text-align: left; appearance: none; -webkit-appearance: none;
        }
        .pos-card:hover { border-color: #6366f1; transform: translateY(-1px); }
        .pos-card:active { transform: translateY(0); }
        .pos-card:focus-visible { outline: 2px solid #4f46e5; outline-offset: 2px; }
        html.dark .pos-card { background: #0f172a; border-color: #334155; }

        .pos-variant-item {
            display: block; width: 100%; text-align: left; padding: 0.5rem 0.75rem; font-size: 0.75rem;
            cursor: pointer; border: none; border-bottom: 1px solid #f3f4f6; background: #fff; font: inherit; color: #111827;
        }
        .pos-variant-item:hover, .pos-variant-item:focus-visible { background: #eef2ff; }
        .pos-variant-item:focus-visible { outline: 2px solid #4f46e5; outline-offset: -2px; }
        html.dark .pos-variant-item { background: #0f172a; color: #f1f5f9; border-color: #1e293b; }
        html.dark .pos-variant-item:hover, html.dark .pos-variant-item:focus-visible { background: #312e81; }
        .pos-card-name { font-size: 0.8125rem; font-weight: 600; color: #111827; line-height: 1.2; }
        html.dark .pos-card-name { color: #f1f5f9; }
        .pos-card-sku { font-size: 0.6875rem; color: #6b7280; }
        html.dark .pos-card-sku { color: #94a3b8; }
        .pos-card-price { font-size: 0.9375rem; font-weight: 700; color: #4f46e5; margin-top: auto; }
        html.dark .pos-card-price { color: #818cf8; }
        .pos-stock-badge { font-size: 0.6875rem; font-weight: 600; padding: 0.0625rem 0.375rem; border-radius: 9999px; width: fit-content; }
        .pos-stock-ok { background: #dcfce7; color: #166534; }
        .pos-stock-low { background: #fee2e2; color: #991b1b; }
        html.dark .pos-stock-ok { background: #14532d; color: #86efac; }
        html.dark .pos-stock-low { background: #7f1d1d; color: #fca5a5; }

        .pos-cart { position: sticky; top: 1rem; display: flex; flex-direction: column; max-height: calc(100vh - 8rem); }
        .pos-cart-lines { overflow-y: auto; flex: 1; margin: 0.5rem 0; }
        .pos-cart-line { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; }
        html.dark .pos-cart-line { border-color: #1e293b; }
        .pos-cart-line-name { font-size: 0.8125rem; font-weight: 600; color: #111827; }
        html.dark .pos-cart-line-name { color: #f1f5f9; }
        .pos-cart-line-meta { font-size: 0.6875rem; color: #6b7280; }
        html.dark .pos-cart-line-meta { color: #94a3b8; }

        .pos-qty-group { display: flex; align-items: center; gap: 0.375rem; }
        .pos-qty-btn {
            width: 1.5rem; height: 1.5rem; border-radius: 0.375rem; border: 1px solid #d1d5db;
            background: #f9fafb; cursor: pointer; font-weight: 700; line-height: 1;
        }
        html.dark .pos-qty-btn { background: #1e293b; border-color: #334155; color: #f1f5f9; }

        .pos-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.375rem;
            border-radius: 0.5rem; padding: 0.5rem 1rem; font-size: 0.8125rem; font-weight: 600;
            cursor: pointer; border: 1px solid transparent;
        }
        .pos-btn-primary { background: #4f46e5; color: #fff; }
        .pos-btn-primary:hover { background: #4338ca; }
        .pos-btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
        .pos-btn-secondary { background: #fff; border-color: #d1d5db; color: #374151; }
        html.dark .pos-btn-secondary { background: #0f172a; border-color: #334155; color: #e2e8f0; }
        .pos-btn-danger { background: #fee2e2; color: #991b1b; }
        html.dark .pos-btn-danger { background: #7f1d1d; color: #fca5a5; }
        .pos-btn-block { width: 100%; }
        .pos-btn-icon { width: 1.75rem; height: 1.75rem; padding: 0; border-radius: 0.375rem; border: 1px solid #d1d5db; background: #fff; color: #6b7280; cursor: pointer; }
        html.dark .pos-btn-icon { background: #0f172a; border-color: #334155; color: #94a3b8; }

        .pos-totals-row { display: flex; justify-content: space-between; font-size: 0.8125rem; color: #4b5563; padding: 0.125rem 0; }
        html.dark .pos-totals-row { color: #94a3b8; }
        .pos-totals-row.total { font-size: 1.125rem; font-weight: 700; color: #111827; border-top: 1px solid #e5e7eb; margin-top: 0.375rem; padding-top: 0.5rem; }
        html.dark .pos-totals-row.total { color: #f1f5f9; border-color: #334155; }

        .pos-modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; z-index: 100; padding: 1rem; }
        .pos-modal { background: #fff; border-radius: 0.75rem; padding: 1.5rem; width: 100%; max-width: 28rem; max-height: 90vh; overflow-y: auto; }
        html.dark .pos-modal { background: #1e293b; }
        .pos-modal-title { font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem; color: #111827; }
        html.dark .pos-modal-title { color: #f1f5f9; }

        .pos-empty { text-align: center; color: #6b7280; font-size: 0.8125rem; padding: 2rem 0; }
        html.dark .pos-empty { color: #94a3b8; }

        .pos-held-list { max-height: 16rem; overflow-y: auto; display: flex; flex-direction: column; gap: 0.5rem; }
        .pos-held-item { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0.75rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; }
        html.dark .pos-held-item { border-color: #334155; }

        .pos-error { background: #fee2e2; color: #991b1b; border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.8125rem; margin-bottom: 0.75rem; }
        html.dark .pos-error { background: #7f1d1d; color: #fca5a5; }

        .pos-label { font-size: 0.75rem; font-weight: 600; color: #4b5563; margin-bottom: 0.25rem; display: block; }
        html.dark .pos-label { color: #94a3b8; }

        .pos-btn:focus-visible, .pos-tab:focus-visible, .pos-qty-btn:focus-visible, .pos-btn-icon:focus-visible {
            outline: 2px solid #4f46e5; outline-offset: 2px;
        }

        .sr-only {
            position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
        }
    </style>

    <div class="pos-wrap" x-data="{ showHeld: false }">
        {{-- Left: product browser --}}
        <div class="pos-panel">
            <div class="pos-toolbar">
                <div>
                    <label class="pos-label" for="pos-barcode-input">Barcode scan</label>
                    <input
                        id="pos-barcode-input"
                        type="text"
                        class="pos-input pos-barcode-input"
                        placeholder="Scan or type barcode/SKU + Enter"
                        wire:model="barcodeInput"
                        wire:keydown.enter="scanBarcode"
                        autofocus
                    />
                </div>
                <div>
                    <label class="pos-label" for="pos-search-input">Search products</label>
                    <input id="pos-search-input" type="text" class="pos-input" placeholder="Search by name, SKU..." wire:model.live.debounce.300ms="search" />
                </div>
                <div>
                    <label class="pos-label" for="pos-warehouse-select">Warehouse</label>
                    <select id="pos-warehouse-select" class="pos-input" wire:model.live="warehouseId">
                        @foreach(\App\Models\Warehouse::where('is_active', true)->get() as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="flex: 0 0 auto; align-self: flex-end;">
                    <button type="button" class="pos-btn pos-btn-secondary" @click="showHeld = !showHeld">
                        Held sales ({{ $this->heldSales->count() }})
                    </button>
                </div>
            </div>

            <div x-show="showHeld" x-cloak class="pos-panel" style="margin-bottom: 1rem;">
                <div class="pos-held-list">
                    @forelse($this->heldSales as $held)
                        <div class="pos-held-item">
                            <div>
                                <div class="pos-cart-line-name">{{ $held->order_number }}</div>
                                <div class="pos-cart-line-meta">{{ $held->customer->name }} &middot; {{ $held->created_at->diffForHumans() }}</div>
                            </div>
                            <div style="display:flex; gap:0.375rem;">
                                <button type="button" class="pos-btn pos-btn-primary" wire:click="resumeSale({{ $held->id }})" @click="showHeld = false">Resume</button>
                                <button type="button" class="pos-btn pos-btn-danger" wire:click="deleteHeldSale({{ $held->id }})" wire:confirm="Discard this held sale?">Discard</button>
                            </div>
                        </div>
                    @empty
                        <p class="pos-empty">No held sales.</p>
                    @endforelse
                </div>
            </div>

            <div class="pos-categories" role="group" aria-label="Filter products by category">
                <button type="button" class="pos-tab {{ $categoryId === null ? 'active' : '' }}" aria-pressed="{{ $categoryId === null ? 'true' : 'false' }}" wire:click="selectCategory(null)">All</button>
                @foreach($this->categories as $category)
                    <button type="button" class="pos-tab {{ $categoryId === $category->id ? 'active' : '' }}" aria-pressed="{{ $categoryId === $category->id ? 'true' : 'false' }}" wire:click="selectCategory({{ $category->id }})">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>

            <div class="pos-grid" wire:loading.class="opacity-50">
                @forelse($this->products as $product)
                    @php $stock = (int) ($product->stock_quantity ?? 0); @endphp
                    @if($product->has_variants && $product->variants->isNotEmpty())
                        <div x-data="{ open: false }" style="position: relative;" @keydown.escape="open = false">
                            <button
                                type="button"
                                class="pos-card"
                                style="width:100%;"
                                @click="open = !open"
                                :aria-expanded="open.toString()"
                                aria-haspopup="true"
                                aria-label="{{ $product->name }}, choose a variant"
                            >
                                <span class="pos-card-name">{{ $product->name }}</span>
                                <span class="pos-card-sku">{{ $product->sku }} &middot; has variants</span>
                                <span class="pos-stock-badge {{ $stock > $product->reorder_level ? 'pos-stock-ok' : 'pos-stock-low' }}">{{ $stock }} in stock</span>
                                <span class="pos-card-price">${{ number_format($product->selling_price, 2) }}</span>
                            </button>
                            <div x-show="open" x-cloak @click.outside="open = false" role="menu" aria-label="Variants for {{ $product->name }}" style="position: absolute; top: 100%; left: 0; right: 0; z-index: 20; background: #fff; border: 1px solid #e5e7eb; border-radius: 0.5rem; margin-top: 0.25rem; box-shadow: 0 4px 12px rgba(0,0,0,.15);">
                                @foreach($product->variants as $variant)
                                    <button
                                        type="button"
                                        role="menuitem"
                                        wire:click="addToCart({{ $product->id }}, {{ $variant->id }})"
                                        @click="open = false"
                                        class="pos-variant-item"
                                    >
                                        {{ $variant->label }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <button type="button" class="pos-card" wire:click="addToCart({{ $product->id }})" aria-label="Add {{ $product->name }} to cart, {{ $stock }} in stock, ${{ number_format($product->selling_price, 2) }}">
                            <span class="pos-card-name">{{ $product->name }}</span>
                            <span class="pos-card-sku">{{ $product->sku }}</span>
                            <span class="pos-stock-badge {{ $stock > $product->reorder_level ? 'pos-stock-ok' : 'pos-stock-low' }}">{{ $stock }} in stock</span>
                            <span class="pos-card-price">${{ number_format($product->selling_price, 2) }}</span>
                        </button>
                    @endif
                @empty
                    <p class="pos-empty">No products match.</p>
                @endforelse
            </div>
        </div>

        {{-- Right: cart --}}
        <div class="pos-panel pos-cart">
            <div>
                <label class="pos-label" for="pos-customer-select">Customer</label>
                <div style="display:flex; gap:0.375rem;">
                    <select id="pos-customer-select" class="pos-input" wire:model="customerId">
                        @foreach(\App\Models\Customer::where('is_active', true)->orderBy('name')->get() as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="pos-btn-icon" wire:click="openNewCustomerModal" aria-label="Add new customer" title="New customer">+</button>
                </div>
            </div>

            <div class="pos-cart-lines">
                @forelse($cart as $key => $line)
                    <div class="pos-cart-line">
                        <div style="flex: 1;">
                            <div class="pos-cart-line-name">{{ $line['name'] }}</div>
                            <div class="pos-cart-line-meta">{{ $line['sku'] }} &middot; ${{ number_format($line['unit_price'], 2) }} each</div>
                            <div class="pos-qty-group" style="margin-top: 0.25rem;">
                                <button type="button" class="pos-qty-btn" wire:click="decrementQty('{{ $key }}')" aria-label="Decrease quantity of {{ $line['name'] }}">-</button>
                                <span style="min-width: 1.25rem; text-align: center; font-size: 0.8125rem;" aria-live="polite">{{ $line['quantity'] }}</span>
                                <button type="button" class="pos-qty-btn" wire:click="incrementQty('{{ $key }}')" aria-label="Increase quantity of {{ $line['name'] }}">+</button>
                                <label class="sr-only" for="discount-{{ $key }}">Discount for {{ $line['name'] }}</label>
                                <input id="discount-{{ $key }}" type="number" min="0" step="0.01" class="pos-input" style="width: 5rem; padding: 0.25rem 0.5rem;" placeholder="Discount" wire:model.live="cart.{{ $key }}.discount" />
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:0.8125rem; font-weight:700;">${{ number_format(($line['unit_price'] * $line['quantity']) - $line['discount'], 2) }}</div>
                            <button type="button" class="pos-btn-icon" wire:click="removeFromCart('{{ $key }}')" aria-label="Remove {{ $line['name'] }} from cart" title="Remove" style="margin-top:0.25rem;">&times;</button>
                        </div>
                    </div>
                @empty
                    <p class="pos-empty">Cart is empty.<br>Scan a barcode or tap a product.</p>
                @endforelse
            </div>

            <div>
                <div class="pos-totals-row"><span>Subtotal</span><span>${{ number_format($this->cartTotals['subtotal'], 2) }}</span></div>
                <div class="pos-totals-row"><span>Discount</span><span>-${{ number_format($this->cartTotals['discount'], 2) }}</span></div>
                <div class="pos-totals-row total"><span>Total</span><span>${{ number_format($this->cartTotals['total'], 2) }}</span></div>

                <div style="display:flex; gap:0.5rem; margin-top:0.75rem;">
                    <button type="button" class="pos-btn pos-btn-secondary" style="flex:1;" wire:click="clearCart" @if(empty($cart)) disabled @endif>Clear</button>
                    <button type="button" class="pos-btn pos-btn-secondary" style="flex:1;" wire:click="holdSale" @if(empty($cart)) disabled @endif>Hold</button>
                </div>
                <button type="button" class="pos-btn pos-btn-primary pos-btn-block" style="margin-top:0.5rem;" wire:click="openCheckout" @if(empty($cart)) disabled @endif>
                    Charge ${{ number_format($this->cartTotals['total'], 2) }}
                </button>
            </div>
        </div>
    </div>

    {{-- Checkout modal --}}
    @if($showCheckoutModal)
        <div class="pos-modal-backdrop" wire:click.self="$set('showCheckoutModal', false)" wire:keydown.escape.window="$set('showCheckoutModal', false)" role="dialog" aria-modal="true" aria-labelledby="checkout-modal-title">
            <div class="pos-modal">
                <div class="pos-modal-title" id="checkout-modal-title">Checkout &mdash; ${{ number_format($this->cartTotals['total'], 2) }}</div>

                @if($checkoutError)
                    <div class="pos-error">{{ $checkoutError }}</div>
                @endif

                @foreach($tenders as $i => $tender)
                    <div style="display:flex; gap:0.5rem; margin-bottom:0.5rem; align-items:flex-end;">
                        <div style="flex:1;">
                            <label class="pos-label" for="tender-method-{{ $i }}">Method</label>
                            <select id="tender-method-{{ $i }}" class="pos-input" wire:model.live="tenders.{{ $i }}.method">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank transfer</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <div style="flex:1;">
                            <label class="pos-label" for="tender-amount-{{ $i }}">Amount</label>
                            <input id="tender-amount-{{ $i }}" type="number" min="0" step="0.01" class="pos-input" wire:model.live="tenders.{{ $i }}.amount" />
                        </div>
                        @if(count($tenders) > 1)
                            <button type="button" class="pos-btn-icon" wire:click="removeTenderLine({{ $i }})" aria-label="Remove payment method {{ $i + 1 }}">&times;</button>
                        @endif
                    </div>
                @endforeach

                <button type="button" class="pos-btn pos-btn-secondary" style="margin-bottom:1rem;" wire:click="addTenderLine">+ Add payment method</button>

                <div class="pos-totals-row"><span>Tendered</span><span>${{ number_format($this->tenderTotal, 2) }}</span></div>
                <div class="pos-totals-row total"><span>Change due</span><span>${{ number_format($this->changeDue, 2) }}</span></div>

                <div style="display:flex; gap:0.5rem; margin-top:1rem;">
                    <button type="button" class="pos-btn pos-btn-secondary" style="flex:1;" wire:click="$set('showCheckoutModal', false)">Cancel</button>
                    <button type="button" class="pos-btn pos-btn-primary" style="flex:1;" wire:click="completeSale">Complete sale</button>
                </div>
            </div>
        </div>
    @endif

    {{-- New customer modal --}}
    @if($showNewCustomerModal)
        <div class="pos-modal-backdrop" wire:click.self="$set('showNewCustomerModal', false)" wire:keydown.escape.window="$set('showNewCustomerModal', false)" role="dialog" aria-modal="true" aria-labelledby="new-customer-modal-title">
            <div class="pos-modal" style="max-width: 22rem;">
                <div class="pos-modal-title" id="new-customer-modal-title">New customer</div>
                <label class="pos-label" for="pos-new-customer-name">Name</label>
                <input id="pos-new-customer-name" type="text" class="pos-input" style="margin-bottom:0.75rem;" wire:model="newCustomerName" />
                <label class="pos-label" for="pos-new-customer-phone">Phone</label>
                <input id="pos-new-customer-phone" type="text" class="pos-input" style="margin-bottom:1rem;" wire:model="newCustomerPhone" />
                <div style="display:flex; gap:0.5rem;">
                    <button type="button" class="pos-btn pos-btn-secondary" style="flex:1;" wire:click="$set('showNewCustomerModal', false)">Cancel</button>
                    <button type="button" class="pos-btn pos-btn-primary" style="flex:1;" wire:click="saveNewCustomer">Save</button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>

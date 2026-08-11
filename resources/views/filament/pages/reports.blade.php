<x-filament-panels::page>
    <style>
        .rp-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1.25rem; margin-bottom: 1.25rem; }
        html.dark .rp-panel { background: #1e293b; border-color: #334155; }

        .rp-filter-row { display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; }
        .rp-label { font-size: 0.75rem; font-weight: 600; color: #4b5563; margin-bottom: 0.25rem; display: block; }
        html.dark .rp-label { color: #94a3b8; }
        .rp-input {
            border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; font-size: 0.875rem;
            background: #fff; color: #111827;
        }
        html.dark .rp-input { background: #0f172a; border-color: #334155; color: #f1f5f9; }
        .rp-input:focus-visible { outline: 2px solid #4f46e5; outline-offset: 1px; }

        .rp-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; }
        .rp-stat { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1rem; }
        html.dark .rp-stat { background: #1e293b; border-color: #334155; }
        .rp-stat-label { font-size: 0.75rem; color: #6b7280; font-weight: 600; }
        html.dark .rp-stat-label { color: #94a3b8; }
        .rp-stat-value { font-size: 1.5rem; font-weight: 700; color: #111827; margin-top: 0.25rem; }
        html.dark .rp-stat-value { color: #f1f5f9; }

        .rp-section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
        .rp-section-title { font-size: 1rem; font-weight: 700; color: #111827; }
        html.dark .rp-section-title { color: #f1f5f9; }
        .rp-export-link {
            font-size: 0.75rem; font-weight: 600; color: #4f46e5; text-decoration: none; display: inline-flex;
            align-items: center; gap: 0.25rem; border: 1px solid #e0e7ff; padding: 0.375rem 0.75rem; border-radius: 0.5rem;
        }
        .rp-export-link:hover { background: #eef2ff; }
        .rp-export-link:focus-visible { outline: 2px solid #4f46e5; outline-offset: 1px; }
        html.dark .rp-export-link { color: #a5b4fc; border-color: #3730a3; }
        html.dark .rp-export-link:hover { background: #312e81; }

        table.rp-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
        table.rp-table th {
            text-align: left; padding: 0.5rem 0.625rem; font-size: 0.6875rem; text-transform: uppercase;
            letter-spacing: 0.03em; color: #6b7280; border-bottom: 2px solid #e5e7eb;
        }
        html.dark table.rp-table th { color: #94a3b8; border-color: #334155; }
        table.rp-table td { padding: 0.5rem 0.625rem; border-bottom: 1px solid #f3f4f6; color: #111827; }
        html.dark table.rp-table td { border-color: #1e293b; color: #e2e8f0; }
        table.rp-table .num { text-align: right; font-variant-numeric: tabular-nums; }
        .rp-empty { text-align: center; color: #6b7280; padding: 1.5rem; font-size: 0.8125rem; }
        html.dark .rp-empty { color: #94a3b8; }
        .rp-muted { color: #6b7280; }
        html.dark .rp-muted { color: #94a3b8; }
        .rp-scroll { overflow-x: auto; }
    </style>

    <div class="rp-panel">
        <div class="rp-filter-row">
            <div>
                <label class="rp-label" for="rp-from">From</label>
                <input id="rp-from" type="date" class="rp-input" wire:model.live="dateFrom">
            </div>
            <div>
                <label class="rp-label" for="rp-to">To</label>
                <input id="rp-to" type="date" class="rp-input" wire:model.live="dateTo">
            </div>
        </div>
    </div>

    <div class="rp-stats">
        <div class="rp-stat">
            <div class="rp-stat-label">Orders</div>
            <div class="rp-stat-value">{{ $this->salesSummary['order_count'] }}</div>
        </div>
        <div class="rp-stat">
            <div class="rp-stat-label">Revenue</div>
            <div class="rp-stat-value">${{ number_format($this->salesSummary['revenue'], 2) }}</div>
        </div>
        <div class="rp-stat">
            <div class="rp-stat-label">Tax collected</div>
            <div class="rp-stat-value">${{ number_format($this->salesSummary['tax_collected'], 2) }}</div>
        </div>
        <div class="rp-stat">
            <div class="rp-stat-label">Avg order value</div>
            <div class="rp-stat-value">${{ number_format($this->salesSummary['avg_order_value'] ?? 0, 2) }}</div>
        </div>
        <div class="rp-stat">
            <div class="rp-stat-label">Inventory value</div>
            <div class="rp-stat-value">${{ number_format($this->inventoryValuationTotal, 2) }}</div>
        </div>
    </div>

    {{-- Sales by day --}}
    <div class="rp-panel">
        <div class="rp-section-header">
            <div class="rp-section-title">Sales by day</div>
            <a class="rp-export-link" href="{{ route('reports.export.daily-sales', ['from' => $dateFrom, 'to' => $dateTo]) }}">Export CSV</a>
        </div>
        <div class="rp-scroll">
            <table class="rp-table">
                <thead><tr><th>Date</th><th class="num">Orders</th><th class="num">Revenue</th></tr></thead>
                <tbody>
                    @forelse($this->dailySales as $row)
                        <tr><td>{{ $row->day }}</td><td class="num">{{ $row->orders }}</td><td class="num">${{ number_format($row->revenue, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="rp-empty">No sales in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
        {{-- Top products --}}
        <div class="rp-panel">
            <div class="rp-section-header">
                <div class="rp-section-title">Top products</div>
                <a class="rp-export-link" href="{{ route('reports.export.top-products', ['from' => $dateFrom, 'to' => $dateTo]) }}">Export CSV</a>
            </div>
            <div class="rp-scroll">
                <table class="rp-table">
                    <thead><tr><th>Product</th><th class="num">Qty</th><th class="num">Revenue</th></tr></thead>
                    <tbody>
                        @forelse($this->topProducts as $row)
                            <tr><td>{{ $row->name }}<br><span class="rp-muted" style="font-size:0.6875rem;">{{ $row->sku }}</span></td><td class="num">{{ $row->qty_sold }}</td><td class="num">${{ number_format($row->revenue, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="rp-empty">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top customers --}}
        <div class="rp-panel">
            <div class="rp-section-header">
                <div class="rp-section-title">Top customers</div>
                <a class="rp-export-link" href="{{ route('reports.export.top-customers', ['from' => $dateFrom, 'to' => $dateTo]) }}">Export CSV</a>
            </div>
            <div class="rp-scroll">
                <table class="rp-table">
                    <thead><tr><th>Customer</th><th class="num">Orders</th><th class="num">Revenue</th><th class="num">Outstanding</th></tr></thead>
                    <tbody>
                        @forelse($this->topCustomers as $row)
                            <tr><td>{{ $row->name }}</td><td class="num">{{ $row->orders }}</td><td class="num">${{ number_format($row->revenue, 2) }}</td><td class="num">${{ number_format($row->outstanding, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="rp-empty">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
        {{-- Sales by staff --}}
        <div class="rp-panel">
            <div class="rp-section-header">
                <div class="rp-section-title">Sales by staff</div>
                <a class="rp-export-link" href="{{ route('reports.export.sales-by-staff', ['from' => $dateFrom, 'to' => $dateTo]) }}">Export CSV</a>
            </div>
            <div class="rp-scroll">
                <table class="rp-table">
                    <thead><tr><th>Staff</th><th class="num">Orders</th><th class="num">Revenue</th></tr></thead>
                    <tbody>
                        @forelse($this->salesByStaff as $row)
                            <tr><td>{{ $row->name }}</td><td class="num">{{ $row->orders }}</td><td class="num">${{ number_format($row->revenue, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="rp-empty">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Profit margin --}}
        <div class="rp-panel">
            <div class="rp-section-header">
                <div class="rp-section-title">Profit margin by product</div>
                <a class="rp-export-link" href="{{ route('reports.export.margin', ['from' => $dateFrom, 'to' => $dateTo]) }}">Export CSV</a>
            </div>
            <div class="rp-scroll">
                <table class="rp-table">
                    <thead><tr><th>Product</th><th class="num">Revenue</th><th class="num">Cost</th><th class="num">Margin</th></tr></thead>
                    <tbody>
                        @forelse($this->marginByProduct as $row)
                            <tr><td>{{ $row->name }}</td><td class="num">${{ number_format($row->revenue, 2) }}</td><td class="num">${{ number_format($row->cost, 2) }}</td><td class="num">${{ number_format($row->margin, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="rp-empty">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="rp-muted" style="font-size:0.6875rem; margin-top:0.5rem;">Cost is estimated using each product's current cost price, not the historical cost at time of sale.</p>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
        {{-- Inventory valuation --}}
        <div class="rp-panel">
            <div class="rp-section-header">
                <div class="rp-section-title">Inventory valuation</div>
                <a class="rp-export-link" href="{{ route('reports.export.inventory-valuation') }}">Export CSV</a>
            </div>
            <div class="rp-scroll">
                <table class="rp-table">
                    <thead><tr><th>Product</th><th>Warehouse</th><th class="num">Qty</th><th class="num">Value</th></tr></thead>
                    <tbody>
                        @forelse($this->inventoryValuation as $row)
                            <tr><td>{{ $row->name }}</td><td>{{ $row->warehouse_name }}</td><td class="num">{{ $row->quantity }}</td><td class="num">${{ number_format($row->value, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="rp-empty">No stock.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Low stock --}}
        <div class="rp-panel">
            <div class="rp-section-header">
                <div class="rp-section-title">Low stock / reorder</div>
                <a class="rp-export-link" href="{{ route('reports.export.low-stock') }}">Export CSV</a>
            </div>
            <div class="rp-scroll">
                <table class="rp-table">
                    <thead><tr><th>Product</th><th class="num">In stock</th><th class="num">Reorder at</th></tr></thead>
                    <tbody>
                        @forelse($this->lowStockProducts as $row)
                            <tr><td>{{ $row->name }}</td><td class="num">{{ (int) ($row->stock_quantity ?? 0) }}</td><td class="num">{{ $row->reorder_level }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="rp-empty">Nothing is low on stock.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
        {{-- Spend by supplier --}}
        <div class="rp-panel">
            <div class="rp-section-header">
                <div class="rp-section-title">Spend by supplier</div>
                <a class="rp-export-link" href="{{ route('reports.export.spend-by-supplier', ['from' => $dateFrom, 'to' => $dateTo]) }}">Export CSV</a>
            </div>
            <div class="rp-scroll">
                <table class="rp-table">
                    <thead><tr><th>Supplier</th><th class="num">Orders</th><th class="num">Spend</th><th class="num">Outstanding</th></tr></thead>
                    <tbody>
                        @forelse($this->spendBySupplier as $row)
                            <tr><td>{{ $row->name }}</td><td class="num">{{ $row->orders }}</td><td class="num">${{ number_format($row->spend, 2) }}</td><td class="num">${{ number_format($row->outstanding, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="rp-empty">No purchases in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Outstanding POs --}}
        <div class="rp-panel">
            <div class="rp-section-header">
                <div class="rp-section-title">Outstanding purchase orders</div>
                <a class="rp-export-link" href="{{ route('reports.export.outstanding-purchase-orders') }}">Export CSV</a>
            </div>
            <div class="rp-scroll">
                <table class="rp-table">
                    <thead><tr><th>PO #</th><th>Supplier</th><th>Expected</th><th class="num">Total</th></tr></thead>
                    <tbody>
                        @forelse($this->outstandingPurchaseOrders as $row)
                            <tr><td>{{ $row->po_number }}</td><td>{{ $row->supplier->name }}</td><td>{{ $row->expected_date?->format('Y-m-d') ?? '-' }}</td><td class="num">${{ number_format($row->total, 2) }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="rp-empty">Nothing outstanding.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

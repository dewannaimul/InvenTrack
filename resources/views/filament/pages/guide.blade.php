<x-filament-panels::page>
    <style>
        .gd-panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1.5rem; margin-bottom: 1.25rem; }
        html.dark .gd-panel { background: #1e293b; border-color: #334155; }

        .gd-progress-bar { background: #e5e7eb; border-radius: 9999px; height: 0.5rem; overflow: hidden; margin: 0.75rem 0; }
        html.dark .gd-progress-bar { background: #334155; }
        .gd-progress-fill { background: #4f46e5; height: 100%; transition: width .3s; }

        .gd-checklist { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.5rem; }
        .gd-check-item {
            display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 0.75rem; border-radius: 0.5rem;
            border: 1px solid #e5e7eb; text-decoration: none; color: inherit;
        }
        html.dark .gd-check-item { border-color: #334155; }
        .gd-check-item:hover { border-color: #4f46e5; }
        .gd-check-item:focus-visible { outline: 2px solid #4f46e5; outline-offset: 2px; }
        .gd-check-icon { width: 1.25rem; height: 1.25rem; border-radius: 9999px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; }
        .gd-check-icon.done { background: #dcfce7; color: #166534; }
        .gd-check-icon.pending { background: #f3f4f6; color: #9ca3af; border: 2px solid #d1d5db; }
        html.dark .gd-check-icon.done { background: #14532d; color: #86efac; }
        html.dark .gd-check-icon.pending { background: #1e293b; border-color: #475569; }
        .gd-check-label { font-size: 0.875rem; color: #111827; }
        html.dark .gd-check-label { color: #f1f5f9; }
        .gd-check-item.done .gd-check-label { color: #6b7280; text-decoration: line-through; }
        html.dark .gd-check-item.done .gd-check-label { color: #94a3b8; }

        .gd-steps { counter-reset: gd-step; list-style: none; margin: 0; padding: 0; }
        .gd-step { position: relative; padding: 0 0 1.5rem 2.5rem; border-left: 2px solid #e0e7ff; }
        html.dark .gd-step { border-color: #3730a3; }
        .gd-step:last-child { border-left-color: transparent; padding-bottom: 0; }
        .gd-step::before {
            counter-increment: gd-step; content: counter(gd-step); position: absolute; left: -1rem; top: 0;
            width: 2rem; height: 2rem; border-radius: 9999px; background: #4f46e5; color: #fff; font-weight: 700;
            display: flex; align-items: center; justify-content: center; font-size: 0.875rem;
        }
        .gd-step-title { font-weight: 700; font-size: 0.9375rem; color: #111827; margin-bottom: 0.25rem; }
        html.dark .gd-step-title { color: #f1f5f9; }
        .gd-step-body { font-size: 0.8125rem; color: #4b5563; line-height: 1.5; }
        html.dark .gd-step-body { color: #94a3b8; }
        .gd-step-body a { color: #4f46e5; text-decoration: underline; }
        html.dark .gd-step-body a { color: #a5b4fc; }
    </style>

    <div class="gd-panel">
        <h2 style="font-size:1.125rem; font-weight:700; margin-bottom:0.25rem;">Setup checklist</h2>
        <p style="font-size:0.8125rem; color:#6b7280;">{{ $this->checklistProgress['done'] }} of {{ $this->checklistProgress['total'] }} complete</p>
        <div class="gd-progress-bar">
            <div class="gd-progress-fill" style="width: {{ $this->checklistProgress['total'] ? round(($this->checklistProgress['done'] / $this->checklistProgress['total']) * 100) : 0 }}%"></div>
        </div>
        <ul class="gd-checklist">
            @foreach($this->checklist as $item)
                <li>
                    <a href="{{ $item['url'] }}" class="gd-check-item {{ $item['done'] ? 'done' : '' }}">
                        <span class="gd-check-icon {{ $item['done'] ? 'done' : 'pending' }}" aria-hidden="true">{{ $item['done'] ? '✓' : '' }}</span>
                        <span class="gd-check-label">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="gd-panel">
        <h2 style="font-size:1.125rem; font-weight:700; margin-bottom:1.25rem;">How the system fits together</h2>
        <ol class="gd-steps">
            <li class="gd-step">
                <div class="gd-step-title">Catalog</div>
                <div class="gd-step-body">
                    Start under <strong>Catalog</strong>: create <a href="{{ route('filament.admin.resources.categories.index') }}">Categories</a>,
                    <a href="{{ route('filament.admin.resources.brands.index') }}">Brands</a>, and
                    <a href="{{ route('filament.admin.resources.units.index') }}">Units</a> (e.g. Pieces, Kg). Then add
                    <a href="{{ route('filament.admin.resources.products.index') }}">Products</a> with SKU, barcode, cost/selling price,
                    and a reorder level &mdash; that threshold is what powers the low-stock alerts everywhere else in the system.
                </div>
            </li>
            <li class="gd-step">
                <div class="gd-step-title">Inventory</div>
                <div class="gd-step-body">
                    Under <strong>Inventory</strong>, set up your <a href="{{ route('filament.admin.resources.warehouses.index') }}">Warehouses</a>.
                    Every stock number in the system is tracked per warehouse, and every change &mdash; a sale, a receipt,
                    a transfer, a manual correction &mdash; is written to the <a href="{{ route('filament.admin.resources.stock-movements.index') }}">Stock ledger</a>
                    so you always have a full audit trail of why a quantity is what it is.
                </div>
            </li>
            <li class="gd-step">
                <div class="gd-step-title">Your first purchase</div>
                <div class="gd-step-body">
                    Go to <strong>Purchasing → <a href="{{ route('filament.admin.resources.purchase-orders.index') }}">Purchase Orders</a></strong>,
                    add a <a href="{{ route('filament.admin.resources.suppliers.index') }}">Supplier</a> if needed, and create an order.
                    Once goods arrive, open the order and use <strong>Receive stock</strong> &mdash; partial receipts are supported,
                    and stock only increases for what you actually record as received.
                </div>
            </li>
            <li class="gd-step">
                <div class="gd-step-title">Your first sale</div>
                <div class="gd-step-body">
                    For counter sales, use <a href="{{ route('filament.admin.pages.point-of-sale') }}"><strong>Point of Sale</strong></a> &mdash;
                    scan or search a product, adjust quantities, and check out with cash, card, or a split payment.
                    For quotes or B2B orders placed by staff on behalf of a customer, use the back-office
                    <a href="{{ route('filament.admin.resources.sales-orders.index') }}">Sales Orders</a> form instead. Both write to the
                    same stock and ledger, so your numbers stay consistent either way.
                </div>
            </li>
            <li class="gd-step">
                <div class="gd-step-title">Reports</div>
                <div class="gd-step-body">
                    Head to <a href="{{ route('filament.admin.pages.reports') }}"><strong>Reports</strong></a> for sales trends, top products
                    and customers, inventory valuation, low-stock items, and supplier spend &mdash; every table there can be exported to CSV.
                </div>
            </li>
        </ol>
    </div>

    <div class="gd-panel">
        <h2 style="font-size:1.125rem; font-weight:700; margin-bottom:0.75rem;">Roles &amp; permissions</h2>
        <p style="font-size:0.8125rem; color:#4b5563; line-height:1.6;">
            Access is controlled per role under <a href="{{ route('filament.admin.resources.shield.roles.index') }}" style="color:#4f46e5; text-decoration:underline;">Administration → Roles</a>,
            with a separate View/Create/Edit/Delete toggle for every module. Three roles are set up by default:
            <strong>Super Admin</strong> (full access), <strong>Manager</strong> (day-to-day operations, no user/role management), and
            <strong>Staff</strong> (view access plus Point of Sale). Adjust any of these, or add new roles, at any time &mdash;
            changes apply immediately, no re-login required.
        </p>
    </div>
</x-filament-panels::page>

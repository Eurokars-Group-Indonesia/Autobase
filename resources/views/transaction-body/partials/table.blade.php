<div class="table-responsive">
@php
    $canViewCostPrice = auth()->user()->hasPermission('cost.price.view');
@endphp
    <table class="table table-hover table-sm table-nowrap">
        <thead>
            <tr>
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="part_no">
                    Part No <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="invoice_no">
                    Invoice No <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 150px; cursor: pointer;" class="sortable-header" data-column="pos_code">
                    POS Code <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="wip_no">
                    WIP No <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 250px; cursor: pointer;" class="sortable-header" data-column="description">
                    Description <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 100px; cursor: pointer;" class="sortable-header" data-column="date_decard">
                    Date Decard <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 80px; cursor: pointer;" class="sortable-header" data-column="qty">
                    Qty <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 80px; cursor: pointer;" class="sortable-header" data-column="unit">
                    Unit <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                @if($canViewCostPrice)
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="cost_price">
                    Cost Price <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                @endif
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="selling_price">
                    Selling Price <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 100px; cursor: pointer;" class="sortable-header" data-column="discount">
                    Discount <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 130px; cursor: pointer;" class="sortable-header" data-column="extended_price">
                    Extended Price <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 100px; cursor: pointer;" class="sortable-header" data-column="part_or_labour">
                    Part/Labour <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 100px; cursor: pointer;" class="sortable-header" data-column="invoice_status">
                    Status <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 150px;">Operator Name</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->part_no }}</td>
                    <td>{{ $transaction->invoice_no }}</td>
                    <td>{{ $transaction->brand->brand_code ?? '-' }} - {{ $transaction->brand->brand_name ?? '-' }}</td>
                    <td>{{ $transaction->wip_no }}</td>
                    <td>{{ $transaction->description ?? '-' }}</td>
                    <td>{{ $transaction->date_decard ? $transaction->date_decard->format('d M Y') : '-' }}</td>
                    <td class="text-end">{{ number_format($transaction->qty, 2) }}</td>
                    <td>{{ $transaction->unit }}</td>
                    @if($canViewCostPrice)
                    <td class="text-end">{{ number_format($transaction->cost_price ?? 0, 2) }}</td>
                    @endif
                    <td class="text-end">{{ number_format($transaction->selling_price, 2) }}</td>
                    <td class="text-end">{{ number_format($transaction->discount, 2) }}%</td>
                    <td class="text-end">{{ number_format($transaction->extended_price, 2) }}</td>
                    <td>
                        <span class="badge bg-{{ $transaction->part_or_labour === 'P' ? 'primary' : 'success' }}">
                            {{ $transaction->getPartOrLabourLabel() }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-{{ $transaction->invoice_status === 'X' ? 'danger' : 'success' }}">
                            {{ $transaction->getInvoiceStatusLabel() }}
                        </span>
                    </td>
                    <td>{{ $transaction->operator_name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canViewCostPrice ? 15 : 14 }}" class="text-center">No transaction body found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

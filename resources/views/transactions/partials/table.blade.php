<div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
@php
    $canViewCostPrice = auth()->user()->hasPermission('cost.price.view');
@endphp
@if($hasFilter)
    {{-- When filtering, show header labels first --}}
    @forelse($transactions as $transaction)
        <div class="transaction-group">
            <table class="table table-hover table-sm table-nowrap mb-0">
                <thead class="table-light" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th style="min-width: 80px;">Actions</th>
                        <th style="min-width: 120px;">Invoice No</th>
                        <th style="min-width: 120px;">WIP No</th>
                        <th style="min-width: 120px;">Invoice Date</th>
                        <th style="min-width: 150px;">Account Code</th>
                        <th style="min-width: 200px;">Account Name</th>
                        <th style="min-width: 200px;">Customer Name</th>
                        <th style="min-width: 150px;">Registration No</th>
                        <th style="min-width: 180px;">Chassis</th>
                        <th style="min-width: 120px;">Document Type</th>
                        <th style="min-width: 120px;">POS Code</th>
                        <th style="min-width: 150px;">Phone 1</th>
                        <th style="min-width: 150px;">Phone 2</th>
                        <th style="min-width: 150px;">Phone 3</th>
                        <th style="min-width: 150px;">Phone 4</th>
                        <th style="min-width: 130px;">Gross Value</th>
                        <th style="min-width: 130px;">Net Value</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Header Row -->
                    <tr class="header-row">
                        <td>
                            @if(isset($transaction->bodies) && count($transaction->bodies) > 0)
                            <span class="badge bg-success">{{ count($transaction->bodies) }} items</span>
                            @else
                            <span class="badge bg-secondary">No items</span>
                            @endif
                        </td>
                        <td>{{ $transaction->invoice_no }}</td>
                        <td>{{ $transaction->wip_no }}</td>
                        <td>{{ $transaction->invoice_date ? $transaction->invoice_date->format('d M Y') : '-' }}</td>
                        <td>{{ $transaction->account_code ?? '-' }}</td>
                        <td>{{ $transaction->account_name ?? '-' }}</td>
                        <td>{{ $transaction->customer_name ?? '-' }}</td>
                        <td>{{ $transaction->registration_no ?? '-' }}</td>
                        <td>{{ $transaction->chassis ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $transaction->document_type === 'I' ? 'primary' : 'warning' }}">
                                {{ $transaction->getDocumentTypeLabel() }}
                            </span>
                        </td>
                        <td>{{ $transaction->brand->brand_code ?? '-' }} - {{ $transaction->brand->brand_name ?? '-' }}</td>
                        <td>{{ $transaction->phone_number_1 ?? '-' }}</td>
                        <td>{{ $transaction->phone_number_2 ?? '-' }}</td>
                        <td>{{ $transaction->phone_number_3 ?? '-' }}</td>
                        <td>{{ $transaction->phone_number_4 ?? '-' }}</td>
                        <td class="text-end">{{ $transaction->currency_code }} {{ number_format($transaction->gross_value, 2) }}</td>
                        <td class="text-end">{{ $transaction->currency_code }} {{ number_format($transaction->net_value, 2) }}</td>
                    </tr>

                    <!-- Body Details Row -->
                    @if(isset($transaction->bodies) && count($transaction->bodies) > 0)
                    <tr class="body-details-row">
                        <td colspan="{{ $canViewCostPrice ? 17 : 16 }}" class="p-3">
                            <h6 class="mb-3 text-primary">
                            </h6>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-secondary" style="position: sticky; top: 0; z-index: 5;">
                                        <tr>
                                            <th class="text-center" style="width: 50px;">No</th>
                                            <th class="text-center sortable-header-inline" data-column="part_no" style="cursor: pointer;">
                                                Part No <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                            <th class="text-center sortable-header-inline" data-column="description" style="cursor: pointer;">
                                                Description <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                            <th class="text-center sortable-header-inline" data-column="date_decard" style="cursor: pointer;">
                                                Date Decard <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                            <th class="text-center sortable-header-inline" data-column="qty" style="cursor: pointer;">
                                                Qty <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                            @if($canViewCostPrice)
                                            <th class="text-center sortable-header-inline" data-column="cost_price" style="cursor: pointer;">
                                                Cost Price <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                            @endif
                                            <th class="text-center sortable-header-inline" data-column="selling_price" style="cursor: pointer;">
                                                Selling Price <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                            <th class="text-center sortable-header-inline" data-column="discount" style="cursor: pointer;">
                                                Discount % <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                            <th class="text-center sortable-header-inline" data-column="extended_price" style="cursor: pointer;">
                                                Extended Price <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                            <th class="text-center sortable-header-inline" data-column="unit" style="cursor: pointer;">
                                                Unit <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                            <th class="text-center sortable-header-inline" data-column="part_or_labour" style="cursor: pointer;">
                                                Part/Labour <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="body-details-tbody" data-wipno="{{ $transaction->wip_no }}" data-invno="{{ $transaction->invoice_no }}" data-poscode="{{ $transaction->pos_code }}" data-magicid="{{ $transaction->magic_id }}">
                                        @php $totalExtPrice = 0; @endphp
                                        @foreach($transaction->bodies as $index => $body)
                                            @php $totalExtPrice += $body->extended_price; @endphp
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>{{ $body->part_no }}</td>
                                                <td>{{ $body->description ?? '-' }}</td>
                                                <td class="text-center">
                                                    @if($body->date_decard)
                                                        {{ \Carbon\Carbon::parse($body->date_decard)->format('d M Y') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="text-end">{{ number_format($body->qty, 2) }}</td>
                                                @if($canViewCostPrice)
                                                <td class="text-end">{{ number_format($body->cost_price ?? 0, 2) }}</td>
                                                @endif
                                                <td class="text-end">{{ number_format($body->selling_price, 2) }}</td>
                                                <td class="text-end">{{ number_format($body->discount, 2) }}%</td>
                                                <td class="text-end">{{ number_format($body->extended_price, 2) }}</td>
                                                <td>{{ $body->unit }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $body->part_or_labour === 'P' ? 'primary' : 'success' }}">
                                                        {{ $body->part_or_labour === 'P' ? 'Part' : 'Labour' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-secondary" style="position: sticky; bottom: 0; z-index: 5;">
                                        <tr>
                                            <th colspan="{{ $canViewCostPrice ? 9 : 8 }}" class="text-end">Total Extended Price :</th>
                                            <th class="text-end">{{ number_format($totalExtPrice, 2) }}</th>
                                            <th colspan="2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @empty
        <div class="text-center py-4">No transactions found</div>
    @endforelse
@else
    {{-- When not filtering, show normal table --}}
    <table class="table table-hover table-sm table-nowrap">
        <thead style="position: sticky; top: 0; z-index: 10; background-color: var(--bs-table-bg);">
            <tr>
                <th style="min-width: 80px;">Actions</th>
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="invoice_no">
                    Invoice No <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="wip_no">
                    WIP No <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="invoice_date">
                    Invoice Date <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 150px; cursor: pointer;" class="sortable-header" data-column="account_code">
                    Account Code <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 200px; cursor: pointer;" class="sortable-header" data-column="account_name">
                    Account Name <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 200px; cursor: pointer;" class="sortable-header" data-column="customer_name">
                    Customer Name <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 150px; cursor: pointer;" class="sortable-header" data-column="registration_no">
                    Registration No <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 180px; cursor: pointer;" class="sortable-header" data-column="chassis">
                    Chassis <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="document_type">
                    Document Type <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 120px; cursor: pointer;" class="sortable-header" data-column="pos_code">
                    POS Code <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 150px;">Phone 1</th>
                <th style="min-width: 150px;">Phone 2</th>
                <th style="min-width: 150px;">Phone 3</th>
                <th style="min-width: 150px;">Phone 4</th>
                <th style="min-width: 130px; cursor: pointer;" class="sortable-header" data-column="gross_value">
                    Gross Value <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
                <th style="min-width: 130px; cursor: pointer;" class="sortable-header" data-column="net_value">
                    Net Value <span class="sort-icon"><i class="bi bi-arrow-up"></i><i class="bi bi-arrow-down"></i></span>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>
                        <button class="btn btn-sm btn-info view-details"
                                data-wipno="{{ $transaction->wip_no }}"
                                data-invno="{{ $transaction->invoice_no }}"
                                data-poscode="{{ $transaction->pos_code }}"
                                data-magicid="{{ $transaction->magic_id }}"
                                title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                    <td>{{ $transaction->invoice_no }}</td>
                    <td>{{ $transaction->wip_no }}</td>
                    <td>{{ $transaction->invoice_date ? $transaction->invoice_date->format('d M Y') : '-' }}</td>
                    <td>{{ $transaction->account_code ?? '-' }}</td>
                    <td>{{ $transaction->account_name ?? '-' }}</td>
                    <td>{{ $transaction->customer_name ?? '-' }}</td>
                    <td>{{ $transaction->registration_no ?? '-' }}</td>
                    <td>{{ $transaction->chassis ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $transaction->document_type === 'I' ? 'primary' : 'warning' }}">
                            {{ $transaction->getDocumentTypeLabel() }}
                        </span>
                    </td>
                    <td>{{ $transaction->brand->brand_code ?? '-' }} - {{ $transaction->brand->brand_name ?? '-' }}</td>
                    <td>{{ $transaction->phone_number_1 ?? '-' }}</td>
                    <td>{{ $transaction->phone_number_2 ?? '-' }}</td>
                    <td>{{ $transaction->phone_number_3 ?? '-' }}</td>
                    <td>{{ $transaction->phone_number_4 ?? '-' }}</td>
                    <td class="text-end">{{ $transaction->currency_code }} {{ number_format($transaction->gross_value, 2) }}</td>
                    <td class="text-end">{{ $transaction->currency_code }} {{ number_format($transaction->net_value, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" class="text-center">No transactions found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endif
</div>

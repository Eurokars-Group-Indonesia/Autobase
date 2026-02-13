<div class="table-responsive">
    <table class="table table-hover table-sm table-nowrap">
        <thead>
            <tr>
                <th style="min-width: 120px;">Part No</th>
                <th style="min-width: 120px;">Invoice No</th>
                <th style="min-width: 150px;">POS Code</th>
                <th style="min-width: 120px;">WIP No</th>
                <th style="min-width: 250px;">Description</th>
                <th style="min-width: 100px;">Date Decard</th>
                <th style="min-width: 80px;">Qty</th>
                <th style="min-width: 80px;">Unit</th>
                <th style="min-width: 120px;">Cost Price</th>
                <th style="min-width: 120px;">Selling Price</th>
                <th style="min-width: 100px;">Discount</th>
                <th style="min-width: 130px;">Extended Price</th>
                <th style="min-width: 100px;">Part/Labour</th>
                <th style="min-width: 100px;">Status</th>
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
                    <td class="text-end">{{ number_format($transaction->cost_price ?? 0, 2) }}</td>
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
                </tr>
            @empty
                <tr>
                    <td colspan="14" class="text-center">No transaction body found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

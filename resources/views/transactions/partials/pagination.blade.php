<div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
    <div class="text-center text-md-start">
        Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
    </div>
    <div>
        {{ $transactions->links('vendor.pagination.custom') }}
    </div>
</div>

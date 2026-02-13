@extends('layouts.app')

@section('title', 'Dealers Management')

@php
    $breadcrumbs = [
        ['title' => 'Dealers', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shop"></i> Dealers Management</span>
                @if(auth()->user()->hasPermission('dealers.create'))
                    <a href="{{ route('dealers.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Add Dealer
                    </a>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6 ms-auto">
                        <form action="{{ route('dealers.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by code, name, city..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('dealers.index') }}" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Clear
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>City</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dealers as $dealer)
                                <tr>
                                    <td>{{ ($dealers->currentPage() - 1) * $dealers->perPage() + $loop->iteration }}</td>
                                    <td>{{ $dealer->dealer_code }}</td>
                                    <td>{{ $dealer->dealer_name }}</td>
                                    <td>{{ $dealer->city ?? '-' }}</td>
                                    <td>
                                        @if(auth()->user()->hasPermission('dealers.edit'))
                                            <a href="{{ route('dealers.edit', $dealer->unique_id) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('dealers.delete'))
                                            <form action="{{ route('dealers.destroy', $dealer->unique_id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No dealers found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-center text-md-start">
                        Showing {{ $dealers->firstItem() ?? 0 }} to {{ $dealers->lastItem() ?? 0 }} of {{ $dealers->total() }} entries
                    </div>
                    <div>
                        {{ $dealers->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

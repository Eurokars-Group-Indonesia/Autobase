@extends('layouts.app')

@section('title', 'Brands Management')

@php
    $breadcrumbs = [
        ['title' => 'Brands', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-tag"></i> Brands Management</span>
                @if(auth()->user()->hasPermission('brands.create'))
                    <a href="{{ route('brands.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Add Brand
                    </a>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6 ms-auto">
                        <form action="{{ route('brands.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by code, name, group, country..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('brands.index') }}" class="btn btn-secondary">
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
                                <th>Brand Group</th>
                                <th>Country Origin</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($brands as $brand)
                                <tr>
                                    <td>{{ ($brands->currentPage() - 1) * $brands->perPage() + $loop->iteration }}</td>
                                    <td>{{ $brand->brand_code }}</td>
                                    <td>{{ $brand->brand_name }}</td>
                                    <td>{{ $brand->brand_group ?? '-' }}</td>
                                    <td>{{ $brand->country_origin ?? '-' }}</td>
                                    <td>
                                        @if(auth()->user()->hasPermission('brands.edit'))
                                            <a href="{{ route('brands.edit', $brand->unique_id) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('brands.delete'))
                                            <form action="{{ route('brands.destroy', $brand->unique_id) }}" method="POST" class="d-inline">
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
                                    <td colspan="6" class="text-center">No brands found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-center text-md-start">
                        Showing {{ $brands->firstItem() ?? 0 }} to {{ $brands->lastItem() ?? 0 }} of {{ $brands->total() }} entries
                    </div>
                    <div>
                        {{ $brands->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

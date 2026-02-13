@extends('layouts.app')

@section('title', 'Menus Management')

@php
    $breadcrumbs = [
        ['title' => 'Menus', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-menu-button-wide"></i> Menus Management</span>
                @if(auth()->user()->hasPermission('menus.create'))
                    <a href="{{ route('menus.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Add Menu
                    </a>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6 ms-auto">
                        <form action="{{ route('menus.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by code, name, url..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('menus.index') }}" class="btn btn-secondary">
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
                                <th>URL</th>
                                <th>Icon</th>
                                <th>Parent</th>
                                <th>Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($menus as $menu)
                                <tr>
                                    <td>{{ ($menus->currentPage() - 1) * $menus->perPage() + $loop->iteration }}</td>
                                    <td>{{ $menu->menu_code }}</td>
                                    <td>
                                        <i class="bi {{ $menu->menu_icon }}"></i> {{ $menu->menu_name }}
                                    </td>
                                    <td>{{ $menu->menu_url ?? '-' }}</td>
                                    <td><i class="bi {{ $menu->menu_icon }}"></i></td>
                                    <td>{{ $menu->parent ? $menu->parent->menu_name : '-' }}</td>
                                    <td>{{ $menu->menu_order }}</td>
                                    <td>
                                        @if(auth()->user()->hasPermission('menus.edit'))
                                            <a href="{{ route('menus.edit', $menu->unique_id) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('menus.delete'))
                                            <form action="{{ route('menus.destroy', $menu->unique_id) }}" method="POST" class="d-inline">
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
                                    <td colspan="8" class="text-center">No menus found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-center text-md-start">
                        Showing {{ $menus->firstItem() ?? 0 }} to {{ $menus->lastItem() ?? 0 }} of {{ $menus->total() }} entries
                    </div>
                    <div>
                        {{ $menus->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

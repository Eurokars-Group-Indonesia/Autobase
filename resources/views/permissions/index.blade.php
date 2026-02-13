@extends('layouts.app')

@section('title', 'Permissions Management')

@php
    $breadcrumbs = [
        ['title' => 'Permissions', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-key"></i> Permissions Management</span>
                @if(auth()->user()->hasPermission('permissions.create'))
                    <a href="{{ route('permissions.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Add Permission
                    </a>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6 ms-auto">
                        <form action="{{ route('permissions.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by code, name..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $permission)
                                <tr>
                                    <td>{{ ($permissions->currentPage() - 1) * $permissions->perPage() + $loop->iteration }}</td>
                                    <td>{{ $permission->permission_code }}</td>
                                    <td>{{ $permission->permission_name }}</td>
                                    <td>
                                        @if(auth()->user()->hasPermission('permissions.edit'))
                                            <a href="{{ route('permissions.edit', $permission->unique_id) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('permissions.delete'))
                                            <form action="{{ route('permissions.destroy', $permission->unique_id) }}" method="POST" class="d-inline">
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
                                    <td colspan="4" class="text-center">No permissions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-center text-md-start">
                        Showing {{ $permissions->firstItem() ?? 0 }} to {{ $permissions->lastItem() ?? 0 }} of {{ $permissions->total() }} entries
                    </div>
                    <div>
                        {{ $permissions->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

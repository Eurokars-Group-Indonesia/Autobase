@extends('layouts.app')

@section('title', 'Roles Management')

@php
    $breadcrumbs = [
        ['title' => 'Roles', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shield-check"></i> Roles Management</span>
                @if(auth()->user()->hasPermission('roles.create'))
                    <a href="{{ route('roles.create') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-plus-circle"></i> Add Role
                    </a>
                @endif
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6 ms-auto">
                        <form action="{{ route('roles.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="Search by code, name, description..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <i class="bi bi-search"></i> Search
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
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
                                <th>Description</th>
                                <th>Permissions</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td>{{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}</td>
                                    <td>{{ $role->role_code }}</td>
                                    <td>{{ $role->role_name }}</td>
                                    <td>{{ $role->role_description }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $role->permissions_count }} permissions</span>
                                    </td>
                                    <td>
                                        @if(auth()->user()->hasPermission('roles.edit'))
                                            <a href="{{ route('roles.edit', $role->unique_id) }}" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('roles.delete'))
                                            @if($role->role_code === 'ADMIN')
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="ADMIN role cannot be deleted">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('roles.destroy', $role->unique_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No roles found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                    <div class="text-center text-md-start">
                        Showing {{ $roles->firstItem() ?? 0 }} to {{ $roles->lastItem() ?? 0 }} of {{ $roles->total() }} entries
                    </div>
                    <div>
                        {{ $roles->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Create Role')

@php
    $breadcrumbs = [
        ['title' => 'Roles', 'url' => route('roles.index')],
        ['title' => 'Create', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-shield-plus"></i> Create New Role
            </div>
            <div class="card-body">
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Role Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('role_code') is-invalid @enderror" 
                                   name="role_code" value="{{ old('role_code') }}" required maxlength="10">
                            @error('role_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('role_name') is-invalid @enderror" 
                                   name="role_name" value="{{ old('role_name') }}" required maxlength="50">
                            @error('role_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('role_description') is-invalid @enderror" 
                                  name="role_description" rows="3" required maxlength="200">{{ old('role_description') }}</textarea>
                        @error('role_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Permissions</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllPermissions">
                                <i class="bi bi-check-all"></i> Select All
                            </button>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    @foreach($permissions as $permission)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input class="form-check-input permission-checkbox" type="checkbox" name="permissions[]" 
                                                       value="{{ $permission->permission_id }}" id="perm{{ $permission->permission_id }}">
                                                <label class="form-check-label" for="perm{{ $permission->permission_id }}">
                                                    {{ $permission->permission_name }}
                                                    <br><small class="text-muted">{{ $permission->permission_code }}</small>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Menus</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllMenus">
                                <i class="bi bi-check-all"></i> Select All
                            </button>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    @foreach($menus as $menu)
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input menu-checkbox" type="checkbox" name="menus[]" 
                                                       value="{{ $menu->menu_id }}" id="menu{{ $menu->menu_id }}">
                                                <label class="form-check-label fw-bold" for="menu{{ $menu->menu_id }}">
                                                    <i class="bi {{ $menu->menu_icon }}"></i> {{ $menu->menu_name }}
                                                </label>
                                            </div>
                                            @if($menu->children->count() > 0)
                                                <div class="ms-4">
                                                    @foreach($menu->children as $child)
                                                        <div class="form-check">
                                                            <input class="form-check-input menu-checkbox" type="checkbox" name="menus[]" 
                                                                   value="{{ $child->menu_id }}" id="menu{{ $child->menu_id }}">
                                                            <label class="form-check-label" for="menu{{ $child->menu_id }}">
                                                                <i class="bi {{ $child->menu_icon }}"></i> {{ $child->menu_name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Select All Permissions
    document.getElementById('selectAllPermissions').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.permission-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });
        
        // Update button text and icon
        if (allChecked) {
            this.innerHTML = '<i class="bi bi-check-all"></i> Select All';
            this.classList.remove('btn-outline-danger');
            this.classList.add('btn-outline-primary');
        } else {
            this.innerHTML = '<i class="bi bi-x-circle"></i> Deselect All';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-outline-danger');
        }
    });

    // Select All Menus
    document.getElementById('selectAllMenus').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.menu-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });
        
        // Update button text and icon
        if (allChecked) {
            this.innerHTML = '<i class="bi bi-check-all"></i> Select All';
            this.classList.remove('btn-outline-danger');
            this.classList.add('btn-outline-primary');
        } else {
            this.innerHTML = '<i class="bi bi-x-circle"></i> Deselect All';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-outline-danger');
        }
    });
</script>
@endpush

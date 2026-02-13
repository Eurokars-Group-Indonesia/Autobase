@extends('layouts.app')

@section('title', 'Edit Permission')

@php
    $breadcrumbs = [
        ['title' => 'Permissions', 'url' => route('permissions.index')],
        ['title' => 'Edit', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> Edit Permission
            </div>
            <div class="card-body">
                <form action="{{ route('permissions.update', $permission->unique_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Permission Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('permission_code') is-invalid @enderror" 
                               name="permission_code" value="{{ old('permission_code', $permission->permission_code) }}" required maxlength="100">
                        @error('permission_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Permission Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('permission_name') is-invalid @enderror" 
                               name="permission_name" value="{{ old('permission_name', $permission->permission_name) }}" required maxlength="150">
                        @error('permission_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('permissions.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Permission
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

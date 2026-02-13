@extends('layouts.app')

@section('title', 'Edit User')

@php
    $breadcrumbs = [
        ['title' => 'Users', 'url' => route('users.index')],
        ['title' => 'Edit', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <strong>Validation Error:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle-fill"></i> 
            <strong>SSO User Information</strong>
            <p class="mb-0 mt-2">
                Name, Full Name, Email, and Phone are managed through Azure AD and cannot be edited here. Password is also managed through Azure AD.
            </p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> Edit User
            </div>
            <div class="card-body">
                <form action="{{ route('users.update', $user->unique_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" readonly disabled>
                            <small class="text-muted">Managed by Azure AD</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="{{ $user->full_name }}" readonly disabled>
                            <small class="text-muted">Managed by Azure AD</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" readonly disabled>
                            <small class="text-muted">Managed by Azure AD</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" value="{{ $user->phone }}" readonly disabled>
                            <small class="text-muted">Managed by Azure AD</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Dealer</label>
                        <select class="form-select @error('dealer_id') is-invalid @enderror" name="dealer_id">
                            <option value="">-- Select Dealer --</option>
                            @foreach($dealers as $dealer)
                                <option value="{{ $dealer->dealer_id }}" {{ old('dealer_id', $user->dealer_id) == $dealer->dealer_id ? 'selected' : '' }}>
                                    {{ $dealer->dealer_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('dealer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Roles</label>
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" 
                                               value="{{ $role->role_id }}" id="role{{ $role->role_id }}"
                                               {{ in_array($role->role_id, $userRoles) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="role{{ $role->role_id }}">
                                            {{ $role->role_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Brands</label>
                        <div class="row">
                            @foreach($brands as $brand)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="brands[]" 
                                               value="{{ $brand->brand_id }}" id="brand{{ $brand->brand_id }}"
                                               {{ in_array($brand->brand_id, $userBrands) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="brand{{ $brand->brand_id }}">
                                            {{ $brand->brand_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

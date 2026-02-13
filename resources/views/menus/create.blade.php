@extends('layouts.app')

@section('title', 'Create Menu')

@php
    $breadcrumbs = [
        ['title' => 'Menus', 'url' => route('menus.index')],
        ['title' => 'Create', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-menu-button-wide"></i> Create New Menu
            </div>
            <div class="card-body">
                <form action="{{ route('menus.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Menu Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('menu_code') is-invalid @enderror" 
                                   name="menu_code" value="{{ old('menu_code') }}" required maxlength="50">
                            @error('menu_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Menu Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('menu_name') is-invalid @enderror" 
                                   name="menu_name" value="{{ old('menu_name') }}" required maxlength="100">
                            @error('menu_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Menu URL</label>
                            <input type="text" class="form-control @error('menu_url') is-invalid @enderror" 
                                   name="menu_url" value="{{ old('menu_url') }}" placeholder="/dashboard" maxlength="255">
                            @error('menu_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Menu Icon</label>
                            <input type="text" class="form-control @error('menu_icon') is-invalid @enderror" 
                                   name="menu_icon" value="{{ old('menu_icon') }}" placeholder="bi-house" maxlength="50">
                            <small class="text-muted">Bootstrap Icons class (e.g., bi-house)</small>
                            @error('menu_icon')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Parent Menu</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" name="parent_id">
                                <option value="">-- No Parent --</option>
                                @foreach($parentMenus as $parent)
                                    <option value="{{ $parent->menu_id }}" {{ old('parent_id') == $parent->menu_id ? 'selected' : '' }}>
                                        {{ $parent->menu_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Menu Order <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('menu_order') is-invalid @enderror" 
                                   name="menu_order" value="{{ old('menu_order', 0) }}" required>
                            @error('menu_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Menu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

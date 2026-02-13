@extends('layouts.app')

@section('title', 'Edit Brand')

@php
    $breadcrumbs = [
        ['title' => 'Brands', 'url' => route('brands.index')],
        ['title' => 'Edit', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> Edit Brand
            </div>
            <div class="card-body">
                <form action="{{ route('brands.update', $brand->unique_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('brand_code') is-invalid @enderror" 
                                   name="brand_code" value="{{ old('brand_code', $brand->brand_code) }}" required maxlength="50">
                            @error('brand_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('brand_name') is-invalid @enderror" 
                                   name="brand_name" value="{{ old('brand_name', $brand->brand_name) }}" required maxlength="100">
                            @error('brand_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand Group</label>
                            <input type="text" class="form-control @error('brand_group') is-invalid @enderror" 
                                   name="brand_group" value="{{ old('brand_group', $brand->brand_group) }}" maxlength="100">
                            @error('brand_group')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Country Origin</label>
                            <input type="text" class="form-control @error('country_origin') is-invalid @enderror" 
                                   name="country_origin" value="{{ old('country_origin', $brand->country_origin) }}" maxlength="100">
                            @error('country_origin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('brands.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Brand
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

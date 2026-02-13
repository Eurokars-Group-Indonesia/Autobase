@extends('layouts.app')

@section('title', 'Edit Dealer')

@php
    $breadcrumbs = [
        ['title' => 'Dealers', 'url' => route('dealers.index')],
        ['title' => 'Edit', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil"></i> Edit Dealer
            </div>
            <div class="card-body">
                <form action="{{ route('dealers.update', $dealer->unique_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dealer Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('dealer_code') is-invalid @enderror" 
                                   name="dealer_code" value="{{ old('dealer_code', $dealer->dealer_code) }}" required maxlength="50">
                            @error('dealer_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dealer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('dealer_name') is-invalid @enderror" 
                                   name="dealer_name" value="{{ old('dealer_name', $dealer->dealer_name) }}" required maxlength="150">
                            @error('dealer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   name="city" value="{{ old('city', $dealer->city) }}" maxlength="100">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dealers.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Dealer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

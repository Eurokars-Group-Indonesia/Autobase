@extends('layouts.app')

@section('title', 'Create User')

@php
    $breadcrumbs = [
        ['title' => 'Users', 'url' => route('users.index')],
        ['title' => 'Create', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle-fill"></i> 
            <strong>User Creation Disabled</strong>
            <p class="mb-0 mt-2">
                Users are managed through Azure AD SSO. To add new users, please use the <strong>Sync from Azure</strong> feature on the Users page.
            </p>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>
</div>
@endsection

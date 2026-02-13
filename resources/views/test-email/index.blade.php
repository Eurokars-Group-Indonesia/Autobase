@extends('layouts.app')

@section('title', 'Test Email')

@php
    $breadcrumbs = [
        ['title' => 'Test Email', 'url' => '#']
    ];
@endphp

@section('content')
<div class="row">
    <div class="col-md-8 offset-md-2">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <i class="bi bi-envelope-check"></i> Test Email Configuration
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> <strong>Current SMTP Configuration:</strong>
                    <ul class="mb-0 mt-2">
                        <li><strong>Host:</strong> {{ config('mail.mailers.smtp.host') }}</li>
                        <li><strong>Port:</strong> {{ config('mail.mailers.smtp.port') }}</li>
                        <li><strong>Encryption:</strong> {{ config('mail.mailers.smtp.encryption') }}</li>
                        <li><strong>From Email:</strong> {{ config('mail.from.address') }}</li>
                        <li><strong>From Name:</strong> {{ config('mail.from.name') }}</li>
                    </ul>
                </div>

                <form action="{{ route('test-email.send') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">To Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('to_email') is-invalid @enderror" 
                               name="to_email" value="{{ old('to_email') }}" required 
                               placeholder="recipient@example.com">
                        @error('to_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Enter the email address where you want to send the test email</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               name="subject" value="{{ old('subject', 'Test Email from AutoBase') }}" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                  name="message" rows="6" required>{{ old('message', 'This is a test email from AutoBase system. If you receive this email, your SMTP configuration is working correctly.') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror>
                        <small class="text-muted">You can use HTML tags in the message</small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Send Test Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <i class="bi bi-gear"></i> SMTP Configuration Guide
            </div>
            <div class="card-body">
                <h6>To configure SMTP, update your <code>.env</code> file:</h6>
                <pre class="bg-light p-3 rounded"><code>MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"</code></pre>

                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle"></i> <strong>Important Notes:</strong>
                    <ul class="mb-0 mt-2">
                        <li>For Gmail, you need to use <strong>App Password</strong>, not your regular password</li>
                        <li>Enable 2-Step Verification in your Google Account</li>
                        <li>Generate App Password: <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a></li>
                        <li>After updating .env, run: <code>php artisan config:clear</code></li>
                    </ul>
                </div>

                <h6 class="mt-3">Common SMTP Providers:</h6>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Host</th>
                            <th>Port</th>
                            <th>Encryption</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Gmail</td>
                            <td>smtp.gmail.com</td>
                            <td>587</td>
                            <td>tls</td>
                        </tr>
                        <tr>
                            <td>Outlook/Hotmail</td>
                            <td>smtp-mail.outlook.com</td>
                            <td>587</td>
                            <td>tls</td>
                        </tr>
                        <tr>
                            <td>Yahoo</td>
                            <td>smtp.mail.yahoo.com</td>
                            <td>587</td>
                            <td>tls</td>
                        </tr>
                        <tr>
                            <td>SendGrid</td>
                            <td>smtp.sendgrid.net</td>
                            <td>587</td>
                            <td>tls</td>
                        </tr>
                        <tr>
                            <td>Mailgun</td>
                            <td>smtp.mailgun.org</td>
                            <td>587</td>
                            <td>tls</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

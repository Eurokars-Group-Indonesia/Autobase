<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - AutoBase</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
        }
        .reset-container {
            width: 100%;
            max-width: 450px;
            padding: 15px;
        }
        .reset-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,.2);
            overflow: hidden;
        }
        .reset-header {
            background: #002856;
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .reset-header h3 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }
        .reset-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        .reset-body {
            padding: 2.5rem 2rem;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #002856;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .form-control:disabled,
        .form-control[readonly] {
            background-color: #e9ecef !important;
            color: #6c757d !important;
            cursor: not-allowed;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
            cursor: pointer;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        .input-group:focus-within .input-group-text {
            border-color: #002856;
        }
        .input-group:focus-within .form-control {
            border-color: #002856;
        }
        .btn-primary {
            background: #002856;
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            transition: all 0.3s;
        }
        .btn-primary:hover:not(:disabled) {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
            color: white;
        }
        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .reset-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .password-req {
            color: #6c757d;
            font-size: 0.85rem;
            transition: color 0.3s ease;
        }
        .password-req.valid {
            color: #198754;
        }
        .password-req.valid i {
            color: #198754;
        }
        .password-req.invalid {
            color: #dc3545;
        }
        .password-req.invalid i {
            color: #dc3545;
        }
        .password-req i {
            font-size: 0.7rem;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <i class="bi bi-shield-lock reset-icon"></i>
                <p style="font-size: 24px;"><strong>AutoBase</strong> Reset Password</p>
            </div>
            <div class="reset-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('password.update') }}" method="POST" id="resetForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" name="email" 
                                   value="{{ $email }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   name="password" id="password" required maxlength="255"
                                   placeholder="Enter new password">
                            <span class="input-group-text" id="togglePassword" style="border-left: none; border-radius: 0 10px 10px 0;">
                                <i class="bi bi-eye" id="passwordIcon"></i>
                            </span>
                        </div>
                        @error('password')
                            <div class="text-danger mt-1">
                                <small><i class="bi bi-exclamation-circle"></i> {{ $message }}</small>
                            </div>
                        @enderror

                        <!-- Password Requirements -->
                        <div class="mt-2" id="password-requirements">
                            <small class="d-block mb-1 fw-bold text-muted">Password Requirements:</small>
                            <small class="d-block password-req" id="req-length">
                                <i class="bi bi-circle"></i> Minimum 12 characters
                            </small>
                            <small class="d-block password-req" id="req-uppercase">
                                <i class="bi bi-circle"></i> At least 1 uppercase letter
                            </small>
                            <small class="d-block password-req" id="req-lowercase">
                                <i class="bi bi-circle"></i> At least 1 lowercase letter
                            </small>
                            <small class="d-block password-req" id="req-symbol">
                                <i class="bi bi-circle"></i> At least 1 symbol (!@#$%^&*...)
                            </small>
                            <small class="d-block password-req" id="req-number">
                                <i class="bi bi-circle"></i> At least 1 number
                            </small>
                            <small class="d-block password-req" id="req-sequential">
                                <i class="bi bi-circle"></i> No sequential numbers (123, 234, 321, etc)
                            </small>
                            <small class="d-block password-req" id="req-sequential-alpha">
                                <i class="bi bi-circle"></i> No sequential alphabet (abc, xyz, cba, etc)
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control" 
                                   name="password_confirmation" id="passwordConfirmation" required maxlength="255"
                                   placeholder="Confirm new password">
                            <span class="input-group-text" id="togglePasswordConfirmation" style="border-left: none; border-radius: 0 10px 10px 0;">
                                <i class="bi bi-eye" id="passwordConfirmationIcon"></i>
                            </span>
                        </div>
                        <div id="passwordMatchError" class="text-danger mt-1" style="display: none;">
                            <small><i class="bi bi-exclamation-circle"></i> Password and confirm password do not match</small>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="submitButton">
                        <i class="bi bi-check-circle"></i> Reset Password
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" style="color: #002856; text-decoration: none; font-size: 0.9rem;">
                        <i class="bi bi-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
        <div class="text-center mt-3">
            <small class="text-muted">&copy; {{ date('Y') }} IT Team Eurokars Group Indonesia. All rights reserved.</small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const passwordInput = document.getElementById('password');
        const passwordConfirmationInput = document.getElementById('passwordConfirmation');
        const submitButton = document.getElementById('submitButton');

        // Password toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            document.getElementById('passwordIcon').className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        });

        document.getElementById('togglePasswordConfirmation').addEventListener('click', function() {
            const type = passwordConfirmationInput.type === 'password' ? 'text' : 'password';
            passwordConfirmationInput.type = type;
            document.getElementById('passwordConfirmationIcon').className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        });

        // Password validation
        function checkPasswordStrength() {
            const password = passwordInput.value;
            
            const hasLength = password.length >= 12;
            updateRequirement('req-length', hasLength);
            
            const hasUppercase = /[A-Z]/.test(password);
            updateRequirement('req-uppercase', hasUppercase);
            
            const hasLowercase = /[a-z]/.test(password);
            updateRequirement('req-lowercase', hasLowercase);
            
            const symbols = '!@#$%^&*(),.?":{}|<>_+=[]\/`-';
            let hasSymbol = false;
            for (let i = 0; i < password.length; i++) {
                if (symbols.includes(password[i])) {
                    hasSymbol = true;
                    break;
                }
            }
            updateRequirement('req-symbol', hasSymbol);
            
            const hasNumber = /[0-9]/.test(password);
            updateRequirement('req-number', hasNumber);
            
            const hasNoSequential = !hasSequentialNumbers(password);
            updateRequirement('req-sequential', hasNoSequential);
            
            const hasNoSequentialAlpha = !hasSequentialAlphabet(password);
            updateRequirement('req-sequential-alpha', hasNoSequentialAlpha);
            
            checkAllRequirements();
        }

        function updateRequirement(elementId, isValid) {
            const element = document.getElementById(elementId);
            if (isValid) {
                element.classList.add('valid');
                element.classList.remove('invalid');
                element.querySelector('i').className = 'bi bi-check-circle-fill';
            } else {
                element.classList.remove('valid');
                element.classList.add('invalid');
                element.querySelector('i').className = 'bi bi-x-circle-fill';
            }
        }

        function hasSequentialNumbers(password) {
            const numbers = password.match(/\d/g);
            if (!numbers || numbers.length < 3) return false;
            
            for (let i = 0; i < numbers.length - 2; i++) {
                if (parseInt(numbers[i + 1]) === parseInt(numbers[i]) + 1 &&
                    parseInt(numbers[i + 2]) === parseInt(numbers[i]) + 2) {
                    return true;
                }
            }
            
            for (let i = 0; i < numbers.length - 2; i++) {
                if (parseInt(numbers[i + 1]) === parseInt(numbers[i]) - 1 &&
                    parseInt(numbers[i + 2]) === parseInt(numbers[i]) - 2) {
                    return true;
                }
            }
            
            return false;
        }

        function hasSequentialAlphabet(password) {
            const passwordLower = password.toLowerCase();
            
            for (let i = 0; i < passwordLower.length - 2; i++) {
                const char1 = passwordLower.charCodeAt(i);
                const char2 = passwordLower.charCodeAt(i + 1);
                const char3 = passwordLower.charCodeAt(i + 2);
                
                if ((char1 >= 97 && char1 <= 122) && 
                    (char2 >= 97 && char2 <= 122) && 
                    (char3 >= 97 && char3 <= 122)) {
                    
                    if (char2 === char1 + 1 && char3 === char2 + 1) {
                        return true;
                    }
                    
                    if (char2 === char1 - 1 && char3 === char2 - 1) {
                        return true;
                    }
                }
            }
            
            return false;
        }

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const passwordConfirmation = passwordConfirmationInput.value;
            const errorElement = document.getElementById('passwordMatchError');
            
            if (passwordConfirmation.length > 0) {
                if (password !== passwordConfirmation) {
                    errorElement.style.display = 'block';
                    return false;
                } else {
                    errorElement.style.display = 'none';
                    return true;
                }
            } else {
                errorElement.style.display = 'none';
                return passwordConfirmation.length === 0;
            }
        }

        function checkAllRequirements() {
            const password = passwordInput.value;
            
            const hasLength = password.length >= 12;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const symbols = '!@#$%^&*(),.?":{}|<>_+=[]\/`-';
            let hasSymbol = false;
            for (let i = 0; i < password.length; i++) {
                if (symbols.includes(password[i])) {
                    hasSymbol = true;
                    break;
                }
            }
            const hasNumber = /[0-9]/.test(password);
            const hasNoSequential = !hasSequentialNumbers(password);
            const hasNoSequentialAlpha = !hasSequentialAlphabet(password);
            
            const passwordsMatch = checkPasswordMatch();
            
            const allValid = hasLength && hasUppercase && hasLowercase && hasSymbol && 
                            hasNumber && hasNoSequential && hasNoSequentialAlpha && passwordsMatch;
            
            if (password.length > 0) {
                submitButton.disabled = !allValid;
                submitButton.title = allValid ? '' : 'Please meet all password requirements and ensure passwords match';
            } else {
                submitButton.disabled = false;
            }
            
            return allValid;
        }

        passwordInput.addEventListener('input', checkPasswordStrength);
        passwordInput.addEventListener('keyup', checkPasswordStrength);
        passwordConfirmationInput.addEventListener('input', checkAllRequirements);
        passwordConfirmationInput.addEventListener('keyup', checkAllRequirements);
    </script>
</body>
</html>

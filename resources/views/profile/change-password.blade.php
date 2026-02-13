@extends('layouts.app')

@section('title', 'Change Password')

@php
    $breadcrumbs = [
        ['title' => 'Change Password', 'url' => '']
    ];
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-key"></i> Change Password
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle"></i>
                    <strong>Password Requirements:</strong>
                    <div class="mt-2" id="password-requirements">
                        <small class="d-block password-req" id="req-length">
                            <i class="bi bi-circle"></i> Minimum 12 characters
                        </small>
                        <small class="d-block password-req" id="req-uppercase">
                            <i class="bi bi-circle"></i> At least 1 uppercase letter (A-Z)
                        </small>
                        <small class="d-block password-req" id="req-lowercase">
                            <i class="bi bi-circle"></i> At least 1 lowercase letter (a-z)
                        </small>
                        <small class="d-block password-req" id="req-symbol">
                            <i class="bi bi-circle"></i> At least 1 symbol (!@#$%^&*...)
                        </small>
                        <small class="d-block password-req" id="req-number">
                            <i class="bi bi-circle"></i> At least 1 number (0-9)
                        </small>
                        <small class="d-block password-req" id="req-sequential">
                            <i class="bi bi-circle"></i> No sequential numbers (123, 234, 321, etc)
                        </small>
                        <small class="d-block password-req" id="req-sequential-alpha">
                            <i class="bi bi-circle"></i> No sequential alphabet (abc, xyz, cba, etc)
                        </small>
                        <small class="d-block password-req" id="req-name">
                            <i class="bi bi-circle"></i> Must not contain part of your full name
                        </small>
                        <small class="d-block password-req" id="req-different">
                            <i class="bi bi-circle"></i> Must be different from current password
                        </small>
                    </div>
                </div>

                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf

                    <!-- Current Password -->
                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control @error('current_password') is-invalid @enderror" 
                                id="current_password" 
                                name="current_password" 
                                required
                                autocomplete="current-password"
                            >
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                <i class="bi bi-eye" id="current_password_icon"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control @error('new_password') is-invalid @enderror" 
                                id="new_password" 
                                name="new_password" 
                                required
                                autocomplete="new-password"
                                onchange="checkPasswordStrength()"
                                oninput="checkPasswordStrength()"
                                onkeyup="checkPasswordStrength()"
                            >
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                <i class="bi bi-eye" id="new_password_icon"></i>
                            </button>
                        </div>
                        @error('new_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Enter a strong password that meets all requirements above.</small>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input 
                                type="password" 
                                class="form-control @error('new_password_confirmation') is-invalid @enderror" 
                                id="new_password_confirmation" 
                                name="new_password_confirmation" 
                                required
                                autocomplete="new-password"
                                onchange="checkPasswordMatch(); checkAllRequirements();"
                                oninput="checkPasswordMatch(); checkAllRequirements();"
                                onkeyup="checkPasswordMatch(); checkAllRequirements();"
                            >
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password_confirmation')">
                                <i class="bi bi-eye" id="new_password_confirmation_icon"></i>
                            </button>
                        </div>
                        @error('new_password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="passwordMatchError" class="text-danger mt-1" style="display: none;">
                            <small><i class="bi bi-exclamation-circle"></i> Password and confirm password not equal</small>
                        </div>
                        <small class="text-muted">Re-enter your new password to confirm.</small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitButton">
                            <i class="bi bi-check-circle"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Tips -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-shield-check"></i> Security Tips
                </h6>
                <ul class="mb-0 small">
                    <li>Change your password regularly (every 3-6 months)</li>
                    <li>Never share your password with anyone</li>
                    <li>Use a unique password for this account</li>
                    <li>Avoid using common words or personal information</li>
                    <li>Consider using a password manager</li>
                    <li>Log out when using shared computers</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .password-req {
        color: #6c757d;
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
    
    /* Style for disabled submit button */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>
@endpush

@push('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Password strength validation
const currentPasswordInput = document.getElementById('current_password');
const newPasswordInput = document.getElementById('new_password');
const confirmPasswordInput = document.getElementById('new_password_confirmation');
const fullName = "{{ auth()->user()->full_name }}";

function checkPasswordStrength() {
    const currentPassword = currentPasswordInput.value;
    const newPassword = newPasswordInput.value;
    
    // 1. Minimum 12 characters
    const hasLength = newPassword.length >= 12;
    updateRequirement('req-length', hasLength);
    
    // 2. At least 1 uppercase letter
    const hasUppercase = /[A-Z]/.test(newPassword);
    updateRequirement('req-uppercase', hasUppercase);
    
    // 3. At least 1 lowercase letter
    const hasLowercase = /[a-z]/.test(newPassword);
    updateRequirement('req-lowercase', hasLowercase);
    
    // 4. At least 1 symbol
    const symbols = '!@#$%^&*(),.?":{}|<>_+=[]\/`-';
    let hasSymbol = false;
    for (let i = 0; i < newPassword.length; i++) {
        if (symbols.includes(newPassword[i])) {
            hasSymbol = true;
            break;
        }
    }
    updateRequirement('req-symbol', hasSymbol);
    
    // 5. At least 1 number
    const hasNumber = /[0-9]/.test(newPassword);
    updateRequirement('req-number', hasNumber);
    
    // 6. No sequential numbers
    const hasNoSequential = !hasSequentialNumbers(newPassword);
    updateRequirement('req-sequential', hasNoSequential);
    
    // 7. No sequential alphabet
    const hasNoSequentialAlpha = !hasSequentialAlphabet(newPassword);
    updateRequirement('req-sequential-alpha', hasNoSequentialAlpha);
    
    // 8. Must not contain part of name
    const hasNoName = !containsNamePart(newPassword, fullName);
    updateRequirement('req-name', hasNoName);
    
    // 9. Must be different from current password
    const isDifferent = currentPassword.length === 0 || newPassword !== currentPassword;
    updateRequirement('req-different', isDifferent);
    
    // Check all requirements and update submit button
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
    
    // Check ascending sequences
    for (let i = 0; i < numbers.length - 2; i++) {
        if (parseInt(numbers[i + 1]) === parseInt(numbers[i]) + 1 &&
            parseInt(numbers[i + 2]) === parseInt(numbers[i]) + 2) {
            return true;
        }
    }
    
    // Check descending sequences
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

function containsNamePart(password, fullName) {
    if (!fullName || fullName.length < 3) return false;
    
    const passwordLower = password.toLowerCase();
    const passwordNormalized = convertLeetSpeak(passwordLower);
    const nameParts = fullName.toLowerCase().split(/\s+/);
    
    for (const namePart of nameParts) {
        const trimmedNamePart = namePart.trim();
        
        if (trimmedNamePart.length < 3) continue;
        
        for (let i = 0; i <= trimmedNamePart.length - 3; i++) {
            const substringLength = Math.max(3, trimmedNamePart.length - i);
            const substring = trimmedNamePart.substring(i, i + substringLength);
            
            if (passwordLower.includes(substring)) return true;
            if (passwordNormalized.includes(substring)) return true;
            if (isSimilar(passwordNormalized, substring)) return true;
        }
    }
    
    return false;
}

function levenshteinDistance(str1, str2) {
    const len1 = str1.length;
    const len2 = str2.length;
    const matrix = [];
    
    for (let i = 0; i <= len1; i++) {
        matrix[i] = [i];
    }
    for (let j = 0; j <= len2; j++) {
        matrix[0][j] = j;
    }
    
    for (let i = 1; i <= len1; i++) {
        for (let j = 1; j <= len2; j++) {
            if (str1[i - 1] === str2[j - 1]) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j] + 1
                );
            }
        }
    }
    
    return matrix[len1][len2];
}

function isSimilar(password, substring) {
    const similarityThreshold = 0.25;
    const subLen = substring.length;
    
    for (let i = 0; i <= password.length - subLen; i++) {
        const passwordPart = password.substring(i, i + subLen);
        const levenshtein = levenshteinDistance(passwordPart, substring);
        const maxLen = Math.max(passwordPart.length, substring.length);
        const similarity = 1 - (levenshtein / maxLen);
        
        if (similarity >= (1 - similarityThreshold)) {
            return true;
        }
    }
    
    return false;
}

function checkPasswordMatch() {
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const errorElement = document.getElementById('passwordMatchError');
    
    if (confirmPassword.length > 0) {
        if (newPassword !== confirmPassword) {
            errorElement.style.display = 'block';
            confirmPasswordInput.classList.add('is-invalid');
            return false;
        } else {
            errorElement.style.display = 'none';
            confirmPasswordInput.classList.remove('is-invalid');
            return true;
        }
    } else {
        errorElement.style.display = 'none';
        confirmPasswordInput.classList.remove('is-invalid');
        return confirmPassword.length === 0;
    }
}

function checkAllRequirements() {
    const currentPassword = currentPasswordInput.value;
    const newPassword = newPasswordInput.value;
    
    const hasLength = newPassword.length >= 12;
    const hasUppercase = /[A-Z]/.test(newPassword);
    const hasLowercase = /[a-z]/.test(newPassword);
    const symbols = '!@#$%^&*(),.?":{}|<>_+=[]\/`-';
    let hasSymbol = false;
    for (let i = 0; i < newPassword.length; i++) {
        if (symbols.includes(newPassword[i])) {
            hasSymbol = true;
            break;
        }
    }
    const hasNumber = /[0-9]/.test(newPassword);
    const hasNoSequential = !hasSequentialNumbers(newPassword);
    const hasNoSequentialAlpha = !hasSequentialAlphabet(newPassword);
    const hasNoName = !containsNamePart(newPassword, fullName);
    const isDifferent = currentPassword.length === 0 || newPassword !== currentPassword;
    
    const passwordsMatch = checkPasswordMatch();
    
    const allValid = hasLength && hasUppercase && hasLowercase && hasSymbol && 
                    hasNumber && hasNoSequential && hasNoSequentialAlpha && hasNoName && 
                    isDifferent && passwordsMatch;
    
    const submitButton = document.getElementById('submitButton');
    if (newPassword.length > 0) {
        if (allValid) {
            submitButton.disabled = false;
            submitButton.classList.remove('btn-secondary');
            submitButton.classList.add('btn-primary');
            submitButton.title = '';
        } else {
            submitButton.disabled = true;
            submitButton.classList.remove('btn-primary');
            submitButton.classList.add('btn-secondary');
            submitButton.title = 'Please meet all password requirements and ensure passwords match';
        }
    } else {
        submitButton.disabled = false;
        submitButton.classList.remove('btn-secondary');
        submitButton.classList.add('btn-primary');
        submitButton.title = '';
    }
    
    return allValid;
}

function convertLeetSpeak(text) {
    const leetMap = {
        '0': 'o', '1': 'i', '3': 'e', '4': 'a', '5': 's',
        '7': 't', '8': 'b', '@': 'a', '$': 's', '!': 'i', '+': 't'
    };
    
    return text.split('').map(char => leetMap[char] || char).join('');
}

// Add event listeners
// Current password - only listen to check if new password is different
currentPasswordInput.addEventListener('input', function() {
    // Only update the "different from current" requirement
    const currentPassword = currentPasswordInput.value;
    const newPassword = newPasswordInput.value;
    const isDifferent = currentPassword.length === 0 || newPassword !== currentPassword;
    updateRequirement('req-different', isDifferent);
    checkAllRequirements();
});

// New password - full validation
newPasswordInput.addEventListener('input', checkPasswordStrength);
newPasswordInput.addEventListener('keyup', checkPasswordStrength);

// Confirm password - check match
confirmPasswordInput.addEventListener('input', function() {
    checkPasswordMatch();
    checkAllRequirements();
});
confirmPasswordInput.addEventListener('keyup', function() {
    checkPasswordMatch();
    checkAllRequirements();
});
</script>
@endpush

@endsection

<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    protected $fullName;
    protected $similarityThreshold = 0.25; // 25% threshold - need 75% similarity to match

    public function __construct(?string $fullName = null)
    {
        $this->fullName = $fullName;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 1. Minimal 12 karakter
        if (strlen($value) < 12) {
            $fail('Password must be at least 12 characters.');
            return;
        }

        // 2. Minimal ada 1 huruf kapital
        if (!preg_match('/[A-Z]/', $value)) {
            $fail('Password must contain at least 1 uppercase letter.');
            return;
        }

        // 3. Minimal ada 1 huruf kecil
        if (!preg_match('/[a-z]/', $value)) {
            $fail('Password must contain at least 1 lowercase letter.');
            return;
        }

        // 4. Minimal ada 1 simbol
        $symbols = '!@#$%^&*(),.?":{}|<>_+=[]\/`-';
        $hasSymbol = false;
        for ($i = 0; $i < strlen($value); $i++) {
            if (strpos($symbols, $value[$i]) !== false) {
                $hasSymbol = true;
                break;
            }
        }
        if (!$hasSymbol) {
            $fail('Password must contain at least 1 symbol (!@#$%^&*(),.?":{}|<>_-+=[]\/`).');
            return;
        }

        // 5. Minimal ada 1 angka
        if (!preg_match('/[0-9]/', $value)) {
            $fail('Password must contain at least 1 number.');
            return;
        }

        // 6. Tidak boleh angka berurutan (123, 234, 345, dst atau 321, 432, dst)
        if ($this->hasSequentialNumbers($value)) {
            $fail('Password must not contain sequential numbers (e.g., 123, 234, 321, etc).');
            return;
        }

        // 7. Tidak boleh huruf berurutan (abc, bcd, xyz, cba, dst)
        if ($this->hasSequentialAlphabet($value)) {
            $fail('Password must not contain sequential alphabet (e.g., abc, xyz, cba, etc).');
            return;
        }

        // 8. Tidak boleh mengandung sebagian nama (dengan leet speak detection)
        if ($this->fullName && $this->containsNamePart($value, $this->fullName)) {
            $fail('Password must not contain part of your name.');
            return;
        }
    }

    /**
     * Check if password contains sequential numbers
     */
    protected function hasSequentialNumbers(string $password): bool
    {
        // Extract all numbers from password
        preg_match_all('/\d/', $password, $matches);
        $numbers = $matches[0];

        if (count($numbers) < 3) {
            return false;
        }

        // Check for ascending sequences (123, 234, 345, etc)
        for ($i = 0; $i < count($numbers) - 2; $i++) {
            if (
                $numbers[$i + 1] == $numbers[$i] + 1 &&
                $numbers[$i + 2] == $numbers[$i] + 2
            ) {
                return true;
            }
        }

        // Check for descending sequences (321, 432, 543, etc)
        for ($i = 0; $i < count($numbers) - 2; $i++) {
            if (
                $numbers[$i + 1] == $numbers[$i] - 1 &&
                $numbers[$i + 2] == $numbers[$i] - 2
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if password contains sequential alphabet
     */
    protected function hasSequentialAlphabet(string $password): bool
    {
        $passwordLower = strtolower($password);
        $length = strlen($passwordLower);

        // Check for sequences of 3 consecutive letters
        for ($i = 0; $i < $length - 2; $i++) {
            $char1 = ord($passwordLower[$i]);
            $char2 = ord($passwordLower[$i + 1]);
            $char3 = ord($passwordLower[$i + 2]);

            // Check if all are letters (a-z)
            if ($char1 >= 97 && $char1 <= 122 && 
                $char2 >= 97 && $char2 <= 122 && 
                $char3 >= 97 && $char3 <= 122) {
                
                // Check ascending sequence (abc, bcd, cde, etc)
                if ($char2 === $char1 + 1 && $char3 === $char2 + 1) {
                    return true;
                }
                
                // Check descending sequence (cba, dcb, zyx, etc)
                if ($char2 === $char1 - 1 && $char3 === $char2 - 1) {
                    return true;
                }
            }
        }

        return false;
    }
    /**
     * Check if password contains part of the name (with leet speak detection)
     */
    protected function containsNamePart(string $password, string $fullName): bool
    {
        // Normalize password for comparison (lowercase)
        $passwordLower = strtolower($password);
        
        // Convert leet speak to normal characters
        $passwordNormalized = $this->convertLeetSpeak($passwordLower);
        
        // Split full name into parts
        $nameParts = preg_split('/\s+/', $fullName);
        
        foreach ($nameParts as $namePart) {
            $namePart = strtolower(trim($namePart));
            
            // Skip very short name parts (less than 3 characters)
            if (strlen($namePart) < 3) {
                continue;
            }
            
            // Check for substrings of the name (minimum 3 characters)
            for ($i = 0; $i <= strlen($namePart) - 3; $i++) {
                $substring = substr($namePart, $i, max(3, strlen($namePart) - $i));
                
                // Check direct match
                if (strpos($passwordLower, $substring) !== false) {
                    return true;
                }
                
                // Check leet speak match
                if (strpos($passwordNormalized, $substring) !== false) {
                    return true;
                }
                
                // Check similarity using Levenshtein distance
                if ($this->isSimilar($passwordNormalized, $substring)) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Convert leet speak to normal characters
     */
    protected function convertLeetSpeak(string $text): string
    {
        $leetMap = [
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '7' => 't',
            '8' => 'b',
            '@' => 'a',
            '$' => 's',
            '!' => 'i',
            '+' => 't',
        ];
        
        return strtr($text, $leetMap);
    }

    /**
     * Check if a substring is similar to any part of the password
     */
    protected function isSimilar(string $password, string $substring): bool
    {
        $subLen = strlen($substring);
        
        // Check all substrings of password with same length
        for ($i = 0; $i <= strlen($password) - $subLen; $i++) {
            $passwordPart = substr($password, $i, $subLen);
            
            // Calculate similarity
            $levenshtein = levenshtein($passwordPart, $substring);
            $similarity = 1 - ($levenshtein / max(strlen($passwordPart), strlen($substring)));
            
            // If similarity is above threshold, consider it too similar
            if ($similarity >= (1 - $this->similarityThreshold)) {
                return true;
            }
        }
        
        return false;
    }
}

<?php

namespace App\Helpers;

/**
 * Password Validator
 * Enforces strong password policies
 */
class PasswordValidator
{
    private static int $minLength = 8;
    private static int $maxLength = 128;

    /**
     * Validate password against all rules
     */
    public static function validate(string $password): array
    {
        $errors = [];

        // Minimum length
        if (strlen($password) < self::$minLength) {
            $errors[] = "Şifre en az " . self::$minLength . " karakter olmalıdır.";
        }

        // Maximum length
        if (strlen($password) > self::$maxLength) {
            $errors[] = "Şifre en fazla " . self::$maxLength . " karakter olabilir.";
        }

        // Uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Şifre en az bir büyük harf içermelidir.";
        }

        // Lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Şifre en az bir küçük harf içermelidir.";
        }

        // Number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Şifre en az bir rakam içermelidir.";
        }

        // Special character
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>_+=\[\]\\\\\/-]/', $password)) {
            $errors[] = "Şifre en az bir özel karakter içermelidir (!@#$%^&* vb.)";
        }

        // Common password check
        if (self::isCommonPassword($password)) {
            $errors[] = "Bu şifre çok yaygın kullanılmaktadır. Lütfen daha güvenli bir şifre seçin.";
        }

        // Sequential characters
        if (self::hasSequentialCharacters($password)) {
            $errors[] = "Şifre ardışık karakterler içermemelidir (örn: 123, abc).";
        }

        // Repeated characters
        if (self::hasRepeatedCharacters($password)) {
            $errors[] = "Şifre 3'ten fazla tekrar eden karakter içermemelidir (örn: aaa, 111).";
        }

        return $errors;
    }

    /**
     * Check if password is strong enough (no errors)
     */
    public static function isStrong(string $password): bool
    {
        return empty(self::validate($password));
    }

    /**
     * Get password strength (weak, medium, strong)
     */
    public static function strength(string $password): string
    {
        $score = 0;

        // Length points
        if (strlen($password) >= 8) $score++;
        if (strlen($password) >= 12) $score++;
        if (strlen($password) >= 16) $score++;

        // Character type points
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[!@#$%^&*(),.?":{}|<>_+=\[\]\\\\\/-]/', $password)) $score++;

        // Diversity points
        $uniqueChars = count(array_unique(str_split($password)));
        if ($uniqueChars >= 8) $score++;

        // No common password
        if (!self::isCommonPassword($password)) $score++;

        // No sequential/repeated
        if (!self::hasSequentialCharacters($password)) $score++;
        if (!self::hasRepeatedCharacters($password)) $score++;

        // Calculate strength
        if ($score <= 4) return 'weak';
        if ($score <= 7) return 'medium';
        return 'strong';
    }

    /**
     * Get password strength score (0-100)
     */
    public static function strengthScore(string $password): int
    {
        $strength = self::strength($password);

        return match($strength) {
            'weak' => 33,
            'medium' => 66,
            'strong' => 100,
            default => 0
        };
    }

    /**
     * Check if password is in common passwords list
     */
    private static function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            // Top 100 most common passwords
            'password', 'Password', 'Password1', 'Password123', 'Password12', 'Password1234',
            '12345678', '123456789', '1234567890', '12345678910',
            'qwerty', 'qwerty123', 'Qwerty123', 'qwertyuiop',
            'admin', 'admin123', 'Admin123', 'administrator',
            'welcome', 'Welcome1', 'Welcome123',
            'letmein', 'monkey', 'dragon', 'master',
            'sunshine', 'princess', 'football', 'baseball',
            'iloveyou', 'trustno1', 'superman', 'batman',
            'michael', 'jennifer', 'ashley', 'jessica',
            'password1', 'password12', 'password123', 'password1234',
            'Passw0rd', 'Passw0rd!', 'P@ssw0rd', 'P@ssword',
            '1q2w3e4r', '1qaz2wsx', 'zaq12wsx', 'qazwsx',
            'abc123', 'Abc123', 'Abc123!', 'abc123456',
            '111111', '123123', '000000', '1234',
            'asdasd', 'qweqwe', '123qwe', 'qwe123',
            'aaaaaa', '123321', '654321', '987654321',
            'login', 'Login123', 'changeme', 'Change123',
            'Test1234', 'Test123', 'test123', 'testing123',
            'user', 'user123', 'User123', 'username',
            'root', 'root123', 'Root123', 'toor',
            'guest', 'guest123', 'Guest123',
            'demo', 'demo123', 'Demo123',
            'temp', 'temp123', 'Temp123', 'temporary',
            'ankara', 'istanbul', 'izmir', 'bursa',
            'galatasaray', 'fenerbahce', 'besiktas', 'trabzon',
        ];

        $passwordLower = strtolower($password);

        foreach ($commonPasswords as $common) {
            if (strtolower($common) === $passwordLower) {
                return true;
            }

            // Check if common password is contained in password
            if (strlen($common) >= 6 && str_contains($passwordLower, strtolower($common))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for sequential characters (123, abc)
     */
    private static function hasSequentialCharacters(string $password, int $threshold = 3): bool
    {
        $password = strtolower($password);
        $sequences = [
            '0123456789',
            'abcdefghijklmnopqrstuvwxyz',
            'qwertyuiop',
            'asdfghjkl',
            'zxcvbnm',
        ];

        foreach ($sequences as $sequence) {
            for ($i = 0; $i <= strlen($sequence) - $threshold; $i++) {
                $seq = substr($sequence, $i, $threshold);
                $seqReverse = strrev($seq);

                if (str_contains($password, $seq) || str_contains($password, $seqReverse)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check for repeated characters (aaa, 111)
     */
    private static function hasRepeatedCharacters(string $password, int $threshold = 3): bool
    {
        $prev = '';
        $count = 1;

        for ($i = 0; $i < strlen($password); $i++) {
            $char = $password[$i];

            if ($char === $prev) {
                $count++;
                if ($count >= $threshold) {
                    return true;
                }
            } else {
                $count = 1;
                $prev = $char;
            }
        }

        return false;
    }

    /**
     * Generate a random secure password
     */
    public static function generate(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()_-+={}[]';

        $all = $uppercase . $lowercase . $numbers . $special;

        // Ensure at least one of each type
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill the rest randomly
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle
        $password = str_shuffle($password);

        return $password;
    }

    /**
     * Get validation rules as array (for frontend)
     */
    public static function getRules(): array
    {
        return [
            'minLength' => self::$minLength,
            'maxLength' => self::$maxLength,
            'requireUppercase' => true,
            'requireLowercase' => true,
            'requireNumber' => true,
            'requireSpecial' => true,
            'checkCommon' => true,
            'checkSequential' => true,
            'checkRepeated' => true,
        ];
    }

    /**
     * Get password requirements as human-readable string
     */
    public static function getRequirementsText(): string
    {
        return "Şifre en az " . self::$minLength . " karakter olmalı ve şunları içermelidir: " .
               "büyük harf, küçük harf, rakam ve özel karakter (!@#$%^&* vb.)";
    }

    /**
     * Validate password confirmation match
     */
    public static function confirmationMatches(string $password, string $confirmation): bool
    {
        return $password === $confirmation;
    }
}

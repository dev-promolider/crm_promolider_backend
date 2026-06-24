<?php

namespace App\Helpers;

class SecurityHelper
{
    /**
     * Sanitiza texto para prevenir XSS
     */
    public static function sanitizeText($text)
    {
        if (empty($text)) {
            return $text;
        }
        
        // Remover tags HTML
        $text = strip_tags($text);
        
        // Escapar caracteres especiales
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        // Remover caracteres de control
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        return trim($text);
    }

    /**
     * Valida que no haya contenido malicioso
     */
    public static function isSecureInput($input)
    {
        $dangerousPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/javascript:/i',
            '/on\w+\s*=/i', // eventos como onclick, onerror, etc.
            '/<[^>]*>/i', // cualquier tag HTML
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Registra intentos de XSS
     */
    public static function logXSSAttempt($input, $userId = null)
    {
        \Log::warning('Intento de XSS detectado', [
            'input' => $input,
            'user_id' => $userId,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now(),
        ]);
    }
}
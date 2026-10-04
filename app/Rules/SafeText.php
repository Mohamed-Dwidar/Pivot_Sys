<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Rejects free-text input that looks like an HTML/script injection or
 * SQL-injection attempt rather than genuine visitor-submitted text
 * (e.g. a public "add answer" form). Eloquent already parameterizes
 * queries and Blade escapes output, so this isn't the only line of
 * defense — it's a moderation gate that refuses to even save content
 * shaped like an attack, rather than silently accepting it.
 */
class SafeText implements ValidationRule
{
    private const SUSPICIOUS_PATTERNS = [
        '/[<>]/',                  // any angle bracket at all — HTML/XML tags,
                                    // PHP tags (<?php, <?=), comparisons written
                                    // as code, etc. Legitimate free-text answers
                                    // don't need literal < or >.
        '/javascript\s*:/i',
        '/on\w+\s*=\s*["\']/i',    // inline event handlers: onerror="...", onload='...'
        '/\bunion\b.{0,20}\bselect\b/i',
        '/\bselect\b.{0,40}\bfrom\b/i',
        '/\bdrop\s+table\b/i',
        '/\binsert\s+into\b/i',
        '/\bdelete\s+from\b/i',
        '/\bxp_cmdshell\b/i',
        '/\/\*.*?\*\//s',          // SQL/CSS block comment
        '/--\s/i',                 // SQL line comment
        '/[\'"]\s*(or|and)\s+.{0,15}=/i', // tautology injection: ' OR '1'='1, " AND 1=1
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;

        foreach (self::SUSPICIOUS_PATTERNS as $pattern) {
            if (preg_match($pattern, $value) === 1) {
                $fail('حقل :attribute يحتوي على محتوى غير مسموح به.');
                return;
            }
        }
    }
}

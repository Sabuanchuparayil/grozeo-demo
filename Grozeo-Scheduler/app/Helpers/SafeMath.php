<?php
/**
 * EVAL() REMOVAL FIX
 * ==================
 * Replaces dangerous eval() calls in:
 *   - Grozeo-Scheduler/app/Finance/India/FinanceIndia.php (line 77)
 *   - Grozeo-Scheduler/app/Finance/UK/FinanceUK.php (line 77)
 *   - Grozeo-Bizapi/app/Finance/India/FinanceIndia.php
 *   - Grozeo-Bizapi/app/Finance/UK/FinanceUK.php
 *
 * BEFORE (DANGEROUS - Remote Code Execution risk):
 *   eval("\$headValue = $mathExpression;");
 *
 * AFTER (SAFE - Only allows arithmetic):
 *   $headValue = SafeMath::evaluate($mathExpression);
 */

class SafeMath
{
    /**
     * Safely evaluate a mathematical expression containing only:
     * - Numbers (integers and decimals)
     * - Operators: + - * / %
     * - Parentheses: ( )
     * - Variable references that have been pre-resolved to numbers
     *
     * @param string $expression  Mathematical expression like "100 + 50 * 2"
     * @return float|int          Result of the expression
     * @throws \InvalidArgumentException  If expression contains unsafe content
     */
    public static function evaluate($expression)
    {
        // Remove whitespace
        $expression = trim($expression);

        if (empty($expression)) {
            return 0;
        }

        // SECURITY: Only allow numbers, operators, parentheses, decimal points, and spaces
        if (!preg_match('/^[\d\s\+\-\*\/\%\.\(\)]+$/', $expression)) {
            throw new \InvalidArgumentException(
                "Unsafe math expression detected: " . substr($expression, 0, 50)
            );
        }

        // SECURITY: Prevent excessively long expressions (DoS protection)
        if (strlen($expression) > 500) {
            throw new \InvalidArgumentException("Math expression too long");
        }

        // SECURITY: Prevent deeply nested parentheses (stack overflow protection)
        $maxDepth = 0;
        $depth = 0;
        for ($i = 0; $i < strlen($expression); $i++) {
            if ($expression[$i] === '(') $depth++;
            if ($expression[$i] === ')') $depth--;
            $maxDepth = max($maxDepth, $depth);
        }
        if ($maxDepth > 10) {
            throw new \InvalidArgumentException("Math expression too deeply nested");
        }

        // Parse and evaluate using a simple recursive descent parser
        $pos = 0;
        $result = self::parseExpression($expression, $pos);

        return $result;
    }

    private static function parseExpression($expr, &$pos)
    {
        $result = self::parseTerm($expr, $pos);

        while ($pos < strlen($expr)) {
            self::skipSpaces($expr, $pos);
            if ($pos >= strlen($expr)) break;

            $op = $expr[$pos];
            if ($op === '+' || $op === '-') {
                $pos++;
                $term = self::parseTerm($expr, $pos);
                $result = ($op === '+') ? $result + $term : $result - $term;
            } else {
                break;
            }
        }

        return $result;
    }

    private static function parseTerm($expr, &$pos)
    {
        $result = self::parseFactor($expr, $pos);

        while ($pos < strlen($expr)) {
            self::skipSpaces($expr, $pos);
            if ($pos >= strlen($expr)) break;

            $op = $expr[$pos];
            if ($op === '*' || $op === '/' || $op === '%') {
                $pos++;
                $factor = self::parseFactor($expr, $pos);
                if ($op === '*') {
                    $result *= $factor;
                } elseif ($op === '/') {
                    if ($factor == 0) {
                        throw new \InvalidArgumentException("Division by zero");
                    }
                    $result /= $factor;
                } else {
                    if ($factor == 0) {
                        throw new \InvalidArgumentException("Modulo by zero");
                    }
                    $result %= $factor;
                }
            } else {
                break;
            }
        }

        return $result;
    }

    private static function parseFactor($expr, &$pos)
    {
        self::skipSpaces($expr, $pos);

        // Handle negative numbers
        $negative = false;
        if ($pos < strlen($expr) && $expr[$pos] === '-') {
            $negative = true;
            $pos++;
            self::skipSpaces($expr, $pos);
        }

        $result = 0;

        if ($pos < strlen($expr) && $expr[$pos] === '(') {
            // Parenthesized expression
            $pos++; // skip '('
            $result = self::parseExpression($expr, $pos);
            self::skipSpaces($expr, $pos);
            if ($pos < strlen($expr) && $expr[$pos] === ')') {
                $pos++; // skip ')'
            }
        } else {
            // Number
            $numStr = '';
            while ($pos < strlen($expr) && (ctype_digit($expr[$pos]) || $expr[$pos] === '.')) {
                $numStr .= $expr[$pos];
                $pos++;
            }
            if ($numStr === '') {
                return 0;
            }
            $result = floatval($numStr);
        }

        return $negative ? -$result : $result;
    }

    private static function skipSpaces($expr, &$pos)
    {
        while ($pos < strlen($expr) && $expr[$pos] === ' ') {
            $pos++;
        }
    }
}

/*
 * HOW TO APPLY:
 * 
 * 1. Copy this file to: app/Helpers/SafeMath.php
 * 
 * 2. In FinanceIndia.php and FinanceUK.php, replace:
 *    
 *    BEFORE:
 *      eval("\$headValue = $mathExpression;");
 *    
 *    AFTER:
 *      require_once app_path('Helpers/SafeMath.php');
 *      try {
 *          $headValue = SafeMath::evaluate($mathExpression);
 *      } catch (\InvalidArgumentException $e) {
 *          Log::error('Invalid math expression in finance calc: ' . $e->getMessage());
 *          $headValue = 0;
 *      }
 */

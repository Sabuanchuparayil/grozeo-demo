<?php
/**
 * ERROR HANDLING & LOGGING FIX
 * =============================
 * Apply to: Manage-Products, Grozeo-Bizadmin
 *
 * Problem: Only 73-88 try/catch blocks across 700+ PHP files
 * Most database operations, API calls, and file operations have zero error handling
 *
 * Solution: Add a global error handler + logging utility
 */

// ============================================================
// FILE: includes/error_handler.php
// Add this file and include it in your init/bootstrap
// ============================================================

class GrozeoErrorHandler
{
    private static $logDir = null;
    private static $initialized = false;

    /**
     * Initialize the error handler
     * Call once at application startup (e.g., in index.php or init_modules.php)
     */
    public static function init($logDirectory = null)
    {
        if (self::$initialized) return;

        self::$logDir = $logDirectory ?: dirname(__DIR__) . '/logs';
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        // Convert PHP errors to exceptions
        set_error_handler([self::class, 'handleError']);

        // Catch uncaught exceptions
        set_exception_handler([self::class, 'handleException']);

        // Catch fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);

        // Don't display errors in production
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        ini_set('error_log', self::$logDir . '/php_errors.log');

        self::$initialized = true;
    }

    /**
     * Convert PHP errors to ErrorException
     */
    public static function handleError($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) return false;

        self::log('ERROR', $message, [
            'severity' => $severity,
            'file' => $file,
            'line' => $line
        ]);

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException($exception)
    {
        self::log('CRITICAL', $exception->getMessage(), [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Return JSON error for API calls
        if (self::isApiRequest()) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'An internal error occurred'
                // Never expose exception details in production
            ]);
        } else {
            http_response_code(500);
            echo '<h1>Something went wrong</h1><p>Please try again later.</p>';
        }

        exit(1);
    }

    /**
     * Handle fatal errors on shutdown
     */
    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::log('FATAL', $error['message'], [
                'file' => $error['file'],
                'line' => $error['line']
            ]);
        }
    }

    /**
     * Log a message with context
     */
    public static function log($level, $message, $context = [])
    {
        $logFile = self::$logDir . '/app-' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');

        $logEntry = "[{$timestamp}] [{$level}] {$message}";
        if (!empty($context)) {
            $logEntry .= ' | ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }
        $logEntry .= PHP_EOL;

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    /**
     * Check if current request is an API/AJAX call
     */
    private static function isApiRequest()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
               (isset($_SERVER['CONTENT_TYPE']) &&
                strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
    }
}


// ============================================================
// USAGE: In your index.php or init_modules.php, add at the top:
// ============================================================
/*
require_once __DIR__ . '/includes/error_handler.php';
GrozeoErrorHandler::init(__DIR__ . '/logs');
*/


// ============================================================
// WRAPPING EXISTING CODE with try/catch:
// ============================================================

/*
// BEFORE (no error handling):
$status = $db->query("UPDATE orders SET status = 2 WHERE id = {$orderId}");
$paymentResult = processPayment($amount, $method);
$smsResult = sendSmsNotification($mobile, $message);

// AFTER (with proper error handling):
try {
    $db->query('BEGIN');

    $status = $db->executeSafe(
        "UPDATE orders SET status = 2 WHERE id = ?",
        "i", [$orderId]
    );

    if (!$status) {
        throw new \RuntimeException("Failed to update order status");
    }

    $paymentResult = processPayment($amount, $method);
    if (!$paymentResult['success']) {
        throw new \RuntimeException("Payment failed: " . $paymentResult['message']);
    }

    $db->query('COMMIT');

    // Non-critical operations outside transaction
    try {
        sendSmsNotification($mobile, $message);
    } catch (\Exception $e) {
        GrozeoErrorHandler::log('WARNING', 'SMS notification failed', [
            'order_id' => $orderId,
            'error' => $e->getMessage()
        ]);
        // Don't fail the order just because SMS failed
    }

    echo json_encode(['success' => true, 'msg' => 'Order processed']);

} catch (\Exception $e) {
    $db->query('ROLLBACK');
    GrozeoErrorHandler::log('ERROR', 'Order processing failed', [
        'order_id' => $orderId,
        'error' => $e->getMessage()
    ]);
    echo json_encode(['success' => false, 'msg' => 'Error processing order']);
}
*/

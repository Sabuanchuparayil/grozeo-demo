<?php

function grozeoSessionCookieDomain(): string
{
    $domain = $_SERVER['SERVER_NAME'] ?? '';
    if ($domain === '' || $domain === '0.0.0.0' || $domain === 'localhost' || filter_var($domain, FILTER_VALIDATE_IP)) {
        return '';
    }

    return $domain;
}

function grozeoSessionCookiePath(): string
{
    $path = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    if ($path === '\\' || $path === '.' || $path === '') {
        return '/';
    }

    return $path;
}

function grozeoStartSession(bool $regenerate = false): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $params = [
        'lifetime' => 0,
        'path' => grozeoSessionCookiePath(),
        'domain' => grozeoSessionCookieDomain(),
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ];

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($params);
    } elseif (function_exists('session_set_cookie_params')) {
        session_set_cookie_params(
            $params['lifetime'],
            $params['path'] . '; samesite=Lax',
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_start();

    // #region agent log
    error_log('[DEBUG-31830d][H5] session_init: after session_start, sid=' . session_id() 
        . ' admin_set=' . (isset($_SESSION['admin']) ? 'YES' : 'NO')
        . ' regenerate=' . ($regenerate ? 'true' : 'false')
    );
    // #endregion

    if ($regenerate) {
        session_regenerate_id(true);
        // #region agent log
        error_log('[DEBUG-31830d][H5] session_init: after regenerate, new_sid=' . session_id() 
            . ' admin_set=' . (isset($_SESSION['admin']) ? 'YES' : 'NO')
        );
        // #endregion
    }
}

<?php
/**
 * Migration Directory Index Router
 * Required URL parameter: ?code=2123508
 */

$REQUIRED_CODE = '2123508';

if (!isset($_GET['code']) || $_GET['code'] !== $REQUIRED_CODE) {
    http_response_code(403);
    die('<div style="color:red; font-family:sans-serif; padding:20px; border:1px solid red; margin:20px;">
        <h2>Access Denied</h2>
        <p>Invalid or missing migration security code. Please specify <code>?code=2123508</code> in the URL.</p>
    </div>');
}

// Redirect or execute default booster module migration
require_once __DIR__ . '/001_booster_module.php';

<?php
declare(strict_types=1);

require_once __DIR__ . '/../backend/helpers.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    echo json_encode([
        'success' => true,
        'data' => school_read_heads(),
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'data' => [],
        'message' => 'ডাটা লোড করা যায়নি।',
    ], JSON_UNESCAPED_UNICODE);
}

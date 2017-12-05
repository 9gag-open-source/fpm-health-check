<?php
/**
 * HTTP endpoint for FPM health-check.
 * This is a standalone script that run by itself.
 */
$path = $_ENV['FPM_STATUS_PATH'] ?? '_/_/status';
$host = $_ENV['FPM_HTTP_HOST'] ?? '127.0.0.1';
$port = $_ENV['FPM_HTTP_PORT'] ?? '80';

$status = file_get_contents("http://$host:$port/$path?json");
if (!$status) {
    http_response_code(404);
    $err = error_get_last()['message'] ?? 'no message';
    echo "Failed to fetch FPM status ($err)" . PHP_EOL;
    exit(1);
}

$status = json_decode($status, true);
if ($status['listen queue'] > 0  // TODO: duplicate logic from Helper.php
    && $status['active processes'] >= $status['total processes']
) {
    http_response_code(503);
    echo sprintf('FPM is at capacity [queue=%d],[processes=%d/%d]',
        $status['active processes'] >= $status['total processes']);
    exit(1);
}

http_response_code(200);
echo 'Health-check OK' . PHP_EOL;


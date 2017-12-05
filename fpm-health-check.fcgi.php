<?php
foreach ([
    __DIR__ . '/../../autoload.php', 
    __DIR__ . '/../vendor/autoload.php', 
    __DIR__ . '/vendor/autoload.php'] as $file) {
    if (file_exists($file)) {
        require($file);
        break;
    }
}

/**
 * HTTP endpoint for FPM health-check
 */
try {
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
    \FpmCheck\Helper::checkFpmStatus($status);
    http_response_code(200);
    echo 'Health-check OK' . PHP_EOL;

} catch (Exception $ex) {
    http_response_code(500);
    echo $ex->getMessage() . PHP_EOL;
    exit(1);
}

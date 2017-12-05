# FPM Health-check CLI

CLI tool for doing Health-Check for PHP-FPM

## Add to project
```shell
composer require 9gag/fpm-health-check
```
## Usage

Include this in your PHP project, then call the `bin/fpm-health-check` from the health checker. It calls FPM via FastCGI without needing a web server. It checks for normal HTTP status code from the server response, and exits with non-zero code on failure.

To evaluate service health with generic health-check endpoint, pass the full path to main PHP script as first argument, and the URL path as second argument.
```
./bin/fpm-health-check run --fail-on-empty -- /app/public/index.php /v1/health-check.json
```

It also takes an option to perform additional check for FPM status. To check FPM status, use the status path (`pm.status_path`) as the first argument
```
./bin/fpm-health-check run --check-fpm-status -- /_/_/status
```

## Reference

Original idea from [wizaplace/php-fpm-status-cli](https://github.com/wizaplace/php-fpm-status-cli/)
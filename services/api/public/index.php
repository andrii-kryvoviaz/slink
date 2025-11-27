<?php

use Slink\Shared\Infrastructure\Kernel;
use Slink\Shared\Infrastructure\Runtime\Swoole\SwooleRuntime;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';


if (filter_var($_ENV['SWOOLE_ENABLED'],FILTER_VALIDATE_BOOLEAN)) {
  $_SERVER['APP_RUNTIME'] = SwooleRuntime::class;
  
  $minPackageSize = 1024 * 1024 * 20;
  
  $packageMaxLength = max(
    (int) ($_ENV['UPLOAD_MAX_FILESIZE_IN_BYTES'] ?? 
      (isset($_ENV['IMAGE_MAX_SIZE']) ? convertSizeToBytes($_ENV['IMAGE_MAX_SIZE']) : 0)),
    $minPackageSize
  );

  $_SERVER['APP_RUNTIME_OPTIONS'] = [
    'host' => '0.0.0.0',
    'port' => 8080,
    'settings' => [
      \Swoole\Constant::OPTION_WORKER_NUM => $_ENV['SWOOLE_WORKER_NUM'] ?? 1,
      \Swoole\Constant::OPTION_DOCUMENT_ROOT => dirname(__DIR__).'/public',
      \Swoole\Constant::OPTION_PACKAGE_MAX_LENGTH => $packageMaxLength,
      \Swoole\Constant::OPTION_HTTP_COMPRESSION => false,
    ],
  ];
}

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};

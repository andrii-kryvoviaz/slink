<?php


use Runtime\Swoole\Runtime;
use Slik\Shared\Infrastructure\Kernel;

if (filter_var($_ENV['SWOOLE_ENABLED'],FILTER_VALIDATE_BOOLEAN)) {
  $_SERVER['APP_RUNTIME'] = Runtime::class;

  $_SERVER['APP_RUNTIME_OPTIONS'] = [
    'host' => '0.0.0.0',
    'port' => 80,
    'settings' => [
      \Swoole\Constant::OPTION_WORKER_NUM => $_ENV['SWOOLE_WORKER_NUM'] ?? 1,
      \Swoole\Constant::OPTION_DOCUMENT_ROOT => dirname(__DIR__).'/public'
    ],
  ];
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};

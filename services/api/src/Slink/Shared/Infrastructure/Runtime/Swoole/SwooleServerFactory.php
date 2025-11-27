<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Runtime\Swoole;

use Swoole\Http\Server;

final class SwooleServerFactory {
    private const DEFAULT_HOST = '127.0.0.1';
    private const DEFAULT_PORT = 8000;
    private const DEFAULT_MODE = 2;
    private const DEFAULT_SOCK_TYPE = 1;

    private string $host;
    private int $port;
    private int $mode;
    private int $sockType;
    /** @var array<string, mixed> */
    private array $settings;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = []) {
        $this->host = $options['host'] ?? $_SERVER['SWOOLE_HOST'] ?? $_ENV['SWOOLE_HOST'] ?? self::DEFAULT_HOST;
        $this->port = (int) ($options['port'] ?? $_SERVER['SWOOLE_PORT'] ?? $_ENV['SWOOLE_PORT'] ?? self::DEFAULT_PORT);
        $this->mode = (int) ($options['mode'] ?? $_SERVER['SWOOLE_MODE'] ?? $_ENV['SWOOLE_MODE'] ?? self::DEFAULT_MODE);
        $this->sockType = (int) ($options['sock_type'] ?? $_SERVER['SWOOLE_SOCK_TYPE'] ?? $_ENV['SWOOLE_SOCK_TYPE'] ?? self::DEFAULT_SOCK_TYPE);
        $this->settings = $options['settings'] ?? [];
    }

    public function create(callable $requestHandler): Server {
        $server = new Server($this->host, $this->port, $this->mode, $this->sockType);
        $server->set($this->settings);
        $server->on('request', $requestHandler);

        return $server;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getPort(): int {
        return $this->port;
    }
}

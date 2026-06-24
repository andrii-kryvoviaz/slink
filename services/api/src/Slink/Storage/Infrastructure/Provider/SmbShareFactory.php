<?php

declare(strict_types=1);

namespace Slink\Storage\Infrastructure\Provider;

use Icewind\SMB\BasicAuth;
use Icewind\SMB\IShare;
use Icewind\SMB\ServerFactory;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(SmbShareFactoryInterface::class)]
final readonly class SmbShareFactory implements SmbShareFactoryInterface {
  /**
   * @param array<string, mixed> $config
   */
  #[\Override]
  public function create(array $config): IShare {
    $basicAuth = new BasicAuth(
      username: $config['username'],
      workgroup: $config['workgroup'] ?? 'workgroup',
      password: $config['password']
    );

    $server = new ServerFactory()->createServer($config['host'], $basicAuth);

    return $server->getShare($config['share']);
  }
}

<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Storage;

use Slink\Settings\Domain\Exception\SmbHostNotConfiguredException;
use Slink\Settings\Domain\Exception\SmbShareNotConfiguredException;
use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;

final readonly class SmbStorageSettings extends AbstractCompoundValueObject {
  
  /**
   * @param string $host
   * @param string $username
   * @param string $password
   * @param string $share
   * @param string $workgroup
   */
  private function __construct(
    private string $host,
    private string $username,
    private string $password,
    private string $share,
    private string $workgroup,
  ) {}
  
  /**
   * @return string
   */
  public function getHost(): string {
    return $this->host;
  }
  
  /**
   * @return string
   */
  public function getUsername(): string {
    return $this->username;
  }
  
  /**
   * @return string
   */
  public function getPassword(): string {
    return $this->password;
  }
  
  /**
   * @return string
   */
  public function getShare(): string {
    return $this->share;
  }

  /**
   * @return string
   */
  public function getWorkgroup(): string {
    return $this->workgroup;
  }

  /**
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'host' => $this->host,
      'username' => $this->username,
      'password' => EncryptionRegistry::encrypt($this->password),
      'share' => $this->share,
      'workgroup' => $this->workgroup,
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    $host = trim((string) ($payload['host'] ?? ''));
    $share = trim((string) ($payload['share'] ?? ''));
    $workgroup = trim((string) ($payload['workgroup'] ?? ''));

    if (empty($host)) {
      throw new SmbHostNotConfiguredException();
    }

    if (empty($share)) {
      throw new SmbShareNotConfiguredException();
    }

    return new self(
      $host,
      (string) ($payload['username'] ?? ''),
      EncryptionRegistry::decrypt((string) ($payload['password'] ?? '')),
      $share,
      $workgroup,
    );
  }
}
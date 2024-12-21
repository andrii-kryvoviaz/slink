<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\ValueObject\Storage;

use Slink\Shared\Domain\ValueObject\AbstractCompoundValueObject;

final readonly class SmbStorageSettings extends AbstractCompoundValueObject {
  
  /**
   * @param string $host
   * @param string $username
   * @param string $password
   * @param string $share
   */
  private function __construct(
    private string $host,
    private string $username,
    private string $password,
    private string $share,
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
   * @return array<string, mixed>
   */
  #[\Override]
  public function toPayload(): array {
    return [
      'host' => $this->host,
      'username' => $this->username,
      'password' => $this->password,
      'share' => $this->share,
    ];
  }
  
  /**
   * @param array<string, mixed> $payload
   * @return static
   */
  #[\Override]
  public static function fromPayload(array $payload): static {
    return new self(
      $payload['host'],
      $payload['username'],
      $payload['password'],
      $payload['share'],
    );
  }
}
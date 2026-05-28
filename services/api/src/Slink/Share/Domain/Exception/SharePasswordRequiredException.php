<?php

declare(strict_types=1);

namespace Slink\Share\Domain\Exception;

final class SharePasswordRequiredException extends \DomainException implements \JsonSerializable {
  public function __construct(
    private readonly string $shareId,
  ) {
    parent::__construct('Share requires password');
  }

  public function getShareId(): string {
    return $this->shareId;
  }

  /**
   * @return array{requiresPassword: bool, shareId: string}
   */
  public function jsonSerialize(): array {
    return [
      'requiresPassword' => true,
      'shareId' => $this->shareId,
    ];
  }
}

<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Enum;

use Slink\Shared\Domain\Enum\ValidatorAwareEnumTrait;

enum License: string {
  use ValidatorAwareEnumTrait;

  case AllRightsReserved = 'all-rights-reserved';
  case PublicDomain = 'public-domain';
  case CC0 = 'cc0';
  case CC_BY = 'cc-by';
  case CC_BY_SA = 'cc-by-sa';
  case CC_BY_NC = 'cc-by-nc';
  case CC_BY_NC_SA = 'cc-by-nc-sa';
  case CC_BY_ND = 'cc-by-nd';
  case CC_BY_NC_ND = 'cc-by-nc-nd';

  public function getTitle(): string {
    return match($this) {
      self::AllRightsReserved => 'All Rights Reserved',
      self::PublicDomain => 'Public Domain',
      self::CC0 => 'CC0',
      self::CC_BY => 'CC BY',
      self::CC_BY_SA => 'CC BY-SA',
      self::CC_BY_NC => 'CC BY-NC',
      self::CC_BY_NC_SA => 'CC BY-NC-SA',
      self::CC_BY_ND => 'CC BY-ND',
      self::CC_BY_NC_ND => 'CC BY-NC-ND',
    };
  }

  public function getName(): string {
    return match($this) {
      self::AllRightsReserved => 'All Rights Reserved',
      self::PublicDomain => 'Public Domain Work',
      self::CC0 => 'Public Domain Dedication (CC0)',
      self::CC_BY => 'Attribution',
      self::CC_BY_SA => 'Attribution-ShareAlike',
      self::CC_BY_NC => 'Attribution-NonCommercial',
      self::CC_BY_NC_SA => 'Attribution-NonCommercial-ShareAlike',
      self::CC_BY_ND => 'Attribution-NoDerivs',
      self::CC_BY_NC_ND => 'Attribution-NonCommercial-NoDerivs',
    };
  }

  public function getDescription(): string {
    return match($this) {
      self::AllRightsReserved => 'All rights are reserved by the copyright holder. You may not use, copy, or distribute this work without explicit permission.',
      self::PublicDomain => 'This work is free of known copyright restrictions and can be used without permission.',
      self::CC0 => 'The creator has waived all rights and dedicated this work to the public domain. You can use it for any purpose without restriction.',
      self::CC_BY => 'You may distribute, remix, adapt, and build upon this work, even commercially, as long as you credit the creator.',
      self::CC_BY_SA => 'You may remix, adapt, and build upon this work even commercially, as long as you credit the creator and license new creations under identical terms.',
      self::CC_BY_NC => 'You may remix, adapt, and build upon this work non-commercially, as long as you credit the creator.',
      self::CC_BY_NC_SA => 'You may remix, adapt, and build upon this work non-commercially, as long as you credit the creator and license new creations under identical terms.',
      self::CC_BY_ND => 'You may copy and distribute this work, but not adapt it, as long as you credit the creator.',
      self::CC_BY_NC_ND => 'You may copy and distribute this work non-commercially, but not adapt it, as long as you credit the creator.',
    };
  }

  public function getUrl(): ?string {
    return match($this) {
      self::AllRightsReserved => null,
      self::PublicDomain => null,
      self::CC0 => 'https://creativecommons.org/publicdomain/zero/1.0/',
      self::CC_BY => 'https://creativecommons.org/licenses/by/4.0/',
      self::CC_BY_SA => 'https://creativecommons.org/licenses/by-sa/4.0/',
      self::CC_BY_NC => 'https://creativecommons.org/licenses/by-nc/4.0/',
      self::CC_BY_NC_SA => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
      self::CC_BY_ND => 'https://creativecommons.org/licenses/by-nd/4.0/',
      self::CC_BY_NC_ND => 'https://creativecommons.org/licenses/by-nc-nd/4.0/',
    };
  }

  public function isCreativeCommons(): bool {
    return match($this) {
      self::AllRightsReserved, self::PublicDomain => false,
      default => true,
    };
  }

  /**
   * @return array<string, mixed>
   */
  public function toArray(): array {
    return [
      'id' => $this->value,
      'title' => $this->getTitle(),
      'name' => $this->getName(),
      'description' => $this->getDescription(),
      'url' => $this->getUrl(),
      'isCreativeCommons' => $this->isCreativeCommons(),
    ];
  }

  /**
   * @return array<array<string, mixed>>
   */
  public static function allToArray(): array {
    return array_map(fn(self $license) => $license->toArray(), self::cases());
  }
}

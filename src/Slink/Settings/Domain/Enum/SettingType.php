<?php

declare(strict_types=1);

namespace Slink\Settings\Domain\Enum;

enum SettingType: string {
  case String = 'string';
  case Integer = 'integer';
  case Float = 'float';
  case Boolean = 'boolean';
  case Serialized = 'serialized';
  case Null = 'null';
}

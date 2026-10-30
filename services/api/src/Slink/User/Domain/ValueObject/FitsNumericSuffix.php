<?php

declare(strict_types=1);

namespace Slink\User\Domain\ValueObject;

trait FitsNumericSuffix {
  /**
   * @param string $name
   * @param int $suffix
   * @param int $maxLength
   * @param string $separator
   * @return string
   */
  private function fitSuffix(string $name, int $suffix, int $maxLength, string $separator = ''): string {
    $tail = $separator . $suffix;
    $head = mb_strcut($name, 0, $maxLength - strlen($tail), 'UTF-8');

    return rtrim($head) . $tail;
  }
}

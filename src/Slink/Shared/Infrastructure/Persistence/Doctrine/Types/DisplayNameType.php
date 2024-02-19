<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Slink\User\Domain\ValueObject\DisplayName;

final class DisplayNameType extends StringType {
  private const TYPE = 'display_name';
  
  /**
   * @param mixed $value
   * @param AbstractPlatform $platform
   * @return string|null
   *
   * @throws ConversionException
   */
  public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string {
    if (null === $value) {
      return null;
    }
    
    if (!$value instanceof DisplayName) {
      throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', DisplayName::class]);
    }
    
    return $value->toString();
  }
  
  /**
   * @param DisplayName|string|null $value
   * @param AbstractPlatform $platform
   * @return DisplayName|null
   *
   * @throws ConversionException
   */
  public function convertToPHPValue($value, AbstractPlatform $platform): ?DisplayName {
    if ($value instanceof DisplayName) {
      return $value;
    }
    
    try {
      $displayName = DisplayName::fromNullableString($value);
    } catch (\Throwable) {
      throw ConversionException::conversionFailedFormat($value, $this->getName(), 'string');
    }
    
    return $displayName;
  }
  
  /**
   * {@inheritdoc}
   */
  public function requiresSQLCommentHint(AbstractPlatform $platform): bool {
    return true;
  }

  public function getName(): string {
      return self::TYPE;
  }
}
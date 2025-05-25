<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Slink\User\Domain\ValueObject\Username;

final class UsernameType extends StringType {
  private const string TYPE = 'username';
  
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
    
    if (!$value instanceof Username) {
      throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', Username::class]);
    }
    
    return $value->toString();
  }
  
  /**
   * @param Username|string|null $value
   * @param AbstractPlatform $platform
   * @return Username|null
   *
   * @throws ConversionException
   */
  public function convertToPHPValue($value, AbstractPlatform $platform): ?Username {
    if ($value instanceof Username) {
      return $value;
    }
    
    if (is_null($value)) {
      return null;
    }
    
    try {
      $username = Username::fromString($value);
    } catch (\Throwable) {
      throw ConversionException::conversionFailedFormat($value, $this->getName(), 'string');
    }
    
    return $username;
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
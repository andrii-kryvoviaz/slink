<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Slik\User\Domain\ValueObject\Auth\HashedPassword;
use Throwable;

final class HashedPasswordType extends StringType
{
    private const TYPE = 'hashed_password';
  
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

        if (!$value instanceof HashedPassword) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', HashedPassword::class]);
        }

        return $value->toString();
    }
  
  /**
   * @param HashedPassword|string|null $value
   * @param AbstractPlatform $platform
   * @return HashedPassword|null
   *
   * @throws ConversionException
   */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?HashedPassword {
        if (null === $value || $value instanceof HashedPassword) {
            return $value;
        }

        try {
            $hashedPassword = HashedPassword::fromHash($value);
        } catch (Throwable) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $hashedPassword;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool {
        return true;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return self::TYPE;
    }
}

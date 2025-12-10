<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Slink\User\Domain\ValueObject\Auth\HashedPassword;
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
            throw new \InvalidArgumentException(sprintf('Could not convert PHP value of type %s to type hashed_password. Expected one of the following types: null, %s', get_debug_type($value), HashedPassword::class));
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
            throw new \InvalidArgumentException(sprintf('Could not convert database value "%s" to Doctrine Type hashed_password', $value));
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

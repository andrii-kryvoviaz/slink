<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Slink\User\Domain\ValueObject\Email;
use Throwable;

final class EmailType extends StringType {
    private const TYPE = 'email';
  
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

        if (!$value instanceof Email) {
            throw new \InvalidArgumentException(sprintf('Could not convert PHP value of type %s to type email. Expected one of the following types: null, %s', get_debug_type($value), Email::class));
        }

        return $value->toString();
    }
  
  /**
   * @param Email|string|null $value
   * @param AbstractPlatform $platform
   * @return Email|null
   *
   * @throws ConversionException
   */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Email {
        if (null === $value || $value instanceof Email) {
            return $value;
        }

        try {
            $email = Email::fromString($value);
        } catch (Throwable) {
            throw new \InvalidArgumentException(sprintf('Could not convert database value "%s" to Doctrine Type email. Expected format: string', $value));
        }

        return $email;
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

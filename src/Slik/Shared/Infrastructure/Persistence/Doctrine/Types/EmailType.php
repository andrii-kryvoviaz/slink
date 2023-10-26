<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Slik\User\Domain\ValueObject\Email;
use Throwable;

final class EmailType extends StringType {
    private const TYPE = 'email';

    /**
     * @param mixed $value
     *
     * @return mixed|string|null
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform) {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof Email) {
            throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', Email::class]);
        }

        return $value->toString();
    }

    /**
     * @param Email|string|null $value
     *
     * @return Email|null
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform) {
        if (null === $value || $value instanceof Email) {
            return $value;
        }

        try {
            $email = Email::fromString($value);
        } catch (Throwable) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $email;
    }

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform) {
        return true;
    }

    /**
     * @return string
     */
    public function getName() {
        return self::TYPE;
    }
}

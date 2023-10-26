<?php

declare(strict_types=1);

namespace Slik\Shared\Infrastructure\Persistence\Doctrine\Types;

use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Slik\Shared\Domain\Exception\DateTimeException;
use Slik\Shared\Domain\ValueObject\DateTime;

class DateTimeType extends DateTimeImmutableType {
    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string {
        return $platform->getDateTimeTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ConversionException
     *
     * @param T $value
     *
     * @return (T is null ? null : string)
     *
     * @template T
     **/
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string {
        if (null === $value) {
            return null;
        }

        if ($value instanceof DateTime) {
            return $value->format($platform->getDateTimeFormatString());
        }

        if ($value instanceof DateTimeImmutable) {
            return $value->format($platform->getDateTimeFormatString());
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', DateTime::class]);
    }

    /**
     * {@inheritdoc}
     *
     * @param T $value
     *
     * @return DateTimeImmutable
     * @template T
     * @throws ConversionException
     *
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTimeImmutable {
        if (null === $value || $value instanceof DateTime) {
            return $value;
        }

        try {
            $dateTime = DateTime::fromString($value);
        } catch (DateTimeException) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $dateTime;
    }
}

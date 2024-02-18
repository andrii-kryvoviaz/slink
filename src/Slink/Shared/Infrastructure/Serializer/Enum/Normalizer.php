<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Serializer\Enum;

use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\FormErrorNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizableInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ProblemNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Contracts\Translation\TranslatableInterface;

enum Normalizer: string {
  case ObjectNormalizer = ObjectNormalizer::class;
  case PropertyNormalizer = PropertyNormalizer::class;
  case JsonSerializableNormalizer = JsonSerializableNormalizer::class;
  case DateTimeNormalizer = DateTimeNormalizer::class;
  case GetSetMethodNormalizer = GetSetMethodNormalizer::class;
  case DateTimeZoneNormalizer = DateTimeZoneNormalizer::class;
  case DataUriNormalizer = DataUriNormalizer::class;
  case DateIntervalNormalizer = DateIntervalNormalizer::class;
  case BackedEnumNormalizer = BackedEnumNormalizer::class;
  case FormErrorNormalizer = FormErrorNormalizer::class;
  case ConstraintViolationListNormalizer = ConstraintViolationListNormalizer::class;
  case ProblemNormalizer = ProblemNormalizer::class;
  case UidNormalizer = UidNormalizer::class;
  case TranslatableNormalizer = TranslatableInterface::class;
  case CustomNormalizer = NormalizableInterface::class;
}

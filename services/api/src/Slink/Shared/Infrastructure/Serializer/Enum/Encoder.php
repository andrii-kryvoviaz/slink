<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Serializer\Enum;

use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;

enum Encoder: string {
  case JsonEncoder = JsonEncoder::class;
  case XmlEncoder = XmlEncoder::class;
  case YamlEncoder = YamlEncoder::class;
  case CsvEncoder = CsvEncoder::class;
  case None = '';
}

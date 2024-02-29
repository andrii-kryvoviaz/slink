<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
class SerializedName extends \Symfony\Component\Serializer\Attribute\SerializedName {
  
}
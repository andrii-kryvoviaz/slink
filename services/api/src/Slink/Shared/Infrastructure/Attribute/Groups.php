<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS)]
class Groups extends \Symfony\Component\Serializer\Attribute\Groups {
  
}
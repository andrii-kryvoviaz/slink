<?php

declare(strict_types=1);

namespace Slink\Shared\Infrastructure\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final readonly class RequireGuestAccess {
}

<?php

declare(strict_types=1);

namespace Slink\Image\Domain\Service;

interface SvgContentSanitizerInterface {
    /**
     * Sanitizes SVG content string
     */
    public function sanitize(string $content): string;
}

<?php

declare(strict_types=1);

namespace Tests\Traits;

trait FixturePathTrait {
    protected function getFixturePath(string $filename): string {
        return dirname(__DIR__) . '/fixtures/' . $filename;
    }
    
    protected function getFixtureContent(string $filename): string {
        $path = $this->getFixturePath($filename);
        $content = file_get_contents($path);
        
        if ($content === false) {
            throw new \RuntimeException("Could not read fixture file: {$filename}");
        }
        
        return $content;
    }
}

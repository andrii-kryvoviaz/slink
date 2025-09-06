<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\SvgSanitizer;
use Symfony\Component\HttpFoundation\File\File;

class SvgSanitizerIntegrationTest extends TestCase {
    
    #[Test]
    public function itUsesDecoratorPatternToExtendThirdPartyLibrary(): void {
        $sanitizer = new SvgSanitizer();
        
        $complexAttack = '<?xml version="1.0"?>
        <!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]>
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <script>alert("script_attack")</script>
            <rect onclick="alert(\"event_handler\")" href="javascript:alert()"/>
            <image xlink:href="http://evil.com/tracker.php"/>
            <style>@import url("http://evil.com/malicious.css"); .evil { expression(alert("css")); }</style>
            <foreignObject><iframe src="data:text/html,<script>alert()</script>"></iframe></foreignObject>
            <use href="data:image/svg+xml,<svg><script>alert()</script></svg>"/>
        </svg>';
        
        $sanitized = $sanitizer->sanitize($complexAttack);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringNotContainsString('http://evil.com', $sanitized);
        $this->assertStringNotContainsString('@import', $sanitized);
        $this->assertStringNotContainsString('expression(', $sanitized);
        $this->assertStringNotContainsString('<foreignObject>', $sanitized);
        $this->assertStringNotContainsString('data:text/html', $sanitized);
        $this->assertStringNotContainsString('<!DOCTYPE', $sanitized);
        $this->assertStringNotContainsString('<!ENTITY', $sanitized);
        
        $this->assertStringContainsString('<svg', $sanitized);
        $this->assertStringContainsString('<rect', $sanitized);
        $this->assertStringContainsString('<image', $sanitized);
        $this->assertStringContainsString('<style>', $sanitized);
        $this->assertStringContainsString('<use', $sanitized);
    }
    
    #[Test]
    public function itImplementsDecoratorPatternCorrectly(): void {
        $sanitizer = new SvgSanitizer();
        
        $simpleScript = '<svg><script>alert("test")</script><rect x="10" y="10"/></svg>';
        $sanitized = $sanitizer->sanitize($simpleScript);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        
        $complexCase = '<svg><rect onclick="evil()"/></svg>';
        $sanitized = $sanitizer->sanitize($complexCase);
        $this->assertStringNotContainsString('onclick=', $sanitized);
    }
}

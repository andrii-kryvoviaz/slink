<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\SvgSanitizer;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Comprehensive test suite for SVG sanitization to prevent XSS attacks.
 * 
 * Tests cover protection against:
 * - Inline <script> execution
 * - Event handler attributes (onload, onmouseover, etc.)
 * - javascript: URLs inside href or xlink:href
 * - Malicious <use> references
 * - <image> with javascript: or external references
 * - CSS-based injection inside <style>
 * - @import in CSS with javascript: or malicious URLs
 * - <foreignObject> with embedded HTML/JS
 * - <animate> and <set> with scriptable attributes
 * - SMIL animations abuse
 * - XML entities / DTD injection (XXE leading to XSS)
 * - Data URIs with embedded scripts
 * - Namespaced elements carrying dangerous attributes
 * - Filter elements referencing external or scriptable resources
 */
class SvgSanitizerTest extends TestCase {
    
    #[Test]
    public function itSanitizesMaliciousSvgContent(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">
  <script>alert("XSS")</script>
  <rect x="10" y="10" width="80" height="80" fill="red" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('<script>', $sanitizedSvg);
        $this->assertStringNotContainsString('alert("XSS")', $sanitizedSvg);
        $this->assertStringContainsString('<rect', $sanitizedSvg);
    }
    
    #[Test]
    public function itRequiresSanitizationForSvgMimeTypes(): void {
        $sanitizer = new SvgSanitizer();
        
        $this->assertTrue($sanitizer->requiresSanitization('image/svg+xml'));
        $this->assertTrue($sanitizer->requiresSanitization('image/svg'));
        $this->assertFalse($sanitizer->requiresSanitization('image/jpeg'));
        $this->assertFalse($sanitizer->requiresSanitization('image/png'));
        $this->assertFalse($sanitizer->requiresSanitization(null));
    }
    
    #[Test]
    public function itRemovesJavaScriptFromSvg(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">
  <script>alert("XSS attack!")</script>
  <rect x="10" y="10" width="80" height="80" fill="red" onclick="alert(\'click\')" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('<script>', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringNotContainsString('onclick=', $sanitizedSvg);
        $this->assertStringContainsString('<rect', $sanitizedSvg);
        $this->assertStringContainsString('fill="red"', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesInlineScriptExecution(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg">
  <script type="text/javascript">alert("XSS");</script>
  <script><![CDATA[alert("CDATA XSS");]]></script>
  <circle cx="50" cy="50" r="20" fill="blue" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('<script', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringNotContainsString('CDATA', $sanitizedSvg);
        $this->assertStringContainsString('<circle', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesEventHandlerAttributes(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg">
  <rect onload="alert(\'onload\')" onclick="alert(\'click\')" onmouseover="alert(\'hover\')" 
        onfocus="alert(\'focus\')" onblur="alert(\'blur\')" onkeydown="alert(\'key\')"
        x="10" y="10" width="80" height="80" fill="red" />
  <circle onanimationstart="alert(\'animation\')" cx="50" cy="50" r="20" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('onload=', $sanitizedSvg);
        $this->assertStringNotContainsString('onclick=', $sanitizedSvg);
        $this->assertStringNotContainsString('onmouseover=', $sanitizedSvg);
        $this->assertStringNotContainsString('onfocus=', $sanitizedSvg);
        $this->assertStringNotContainsString('onblur=', $sanitizedSvg);
        $this->assertStringNotContainsString('onkeydown=', $sanitizedSvg);
        $this->assertStringNotContainsString('onanimationstart=', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringContainsString('<rect', $sanitizedSvg);
        $this->assertStringContainsString('<circle', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesJavaScriptUrls(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <a href="javascript:alert(\'href_js\')">
    <text>Click me</text>
  </a>
  <use xlink:href="javascript:alert(\'xlink_js\')" />
  <image href="javascript:alert(\'image_js\')" />
  <image xlink:href="javascript:void(0);alert(\'image_xlink\')" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringContainsString('<text>', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesMaliciousUseReferences(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <use xlink:href="data:image/svg+xml;base64,PHNjcmlwdD5hbGVydCgiWFNTIik8L3NjcmlwdD4=" />
  <use href="http://evil.com/malicious.svg#fragment" />
  <use xlink:href="#local-safe-element" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('data:image/svg+xml;base64,', $sanitizedSvg);
        $this->assertStringNotContainsString('http://evil.com', $sanitizedSvg);
        $this->assertStringNotContainsString('evil.com', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesMaliciousImageElements(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <rect x="10" y="10" width="50" height="50" />
  <image href="javascript:alert(\'image_js\')" />
  <image href="http://evil.com/tracker.php" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('http://evil.com', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringContainsString('<rect', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesCssBasedInjection(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg">
  <style>
    rect { 
      background: url("javascript:alert(\'CSS_XSS\')"); 
      content: url("data:text/html,<script>alert(\'CSS_DATA\')</script>");
    }
    .malicious { expression(alert("CSS_EXPRESSION")); }
  </style>
  <rect class="malicious" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('expression(', $sanitizedSvg);
        $this->assertStringNotContainsString('data:text/html', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesCssImportsWithMaliciousUrls(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg">
  <style>
    @import url("javascript:alert(\'IMPORT_JS\')");
    @import url("data:text/css,body{background:url(javascript:alert(\'NESTED\'))}");
    @import "http://evil.com/malicious.css";
  </style>
  <circle cx="50" cy="50" r="20" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('@import', $sanitizedSvg);
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('evil.com', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringContainsString('<circle', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesForeignObjectWithEmbeddedHtml(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg">
  <foreignObject width="200" height="200">
    <div xmlns="http://www.w3.org/1999/xhtml">
      <script>alert("FOREIGN_OBJECT_XSS");</script>
      <img src="x" onerror="alert(\'IMG_ERROR\')" />
      <iframe src="javascript:alert(\'IFRAME\')"></iframe>
    </div>
  </foreignObject>
  <rect x="10" y="10" width="50" height="50" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('<foreignObject', $sanitizedSvg);
        $this->assertStringNotContainsString('<script', $sanitizedSvg);
        $this->assertStringNotContainsString('<iframe', $sanitizedSvg);
        $this->assertStringNotContainsString('onerror=', $sanitizedSvg);
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringContainsString('<rect', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesAnimationWithScriptableAttributes(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg">
  <rect x="10" y="10" width="50" height="50">
    <animate attributeName="onclick" to="alert(\'ANIMATE_XSS\')" />
    <set attributeName="onload" to="javascript:alert(\'SET_XSS\')" />
    <animateTransform attributeName="transform" onbegin="alert(\'ANIM_BEGIN\')" />
  </rect>
  <circle cx="50" cy="50" r="20" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('onclick', $sanitizedSvg);
        $this->assertStringNotContainsString('onload', $sanitizedSvg);
        $this->assertStringNotContainsString('onbegin=', $sanitizedSvg);
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringContainsString('<rect', $sanitizedSvg);
        $this->assertStringContainsString('<circle', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesSmilAnimationAbuse(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg">
  <rect x="10" y="10" width="50" height="50">
    <animate attributeName="x" values="10;100;10" dur="2s" 
             onbegin="alert(\'SMIL_BEGIN\')" onend="alert(\'SMIL_END\')" />
    <animateMotion path="M10,10 L100,100" dur="3s" 
                   onrepeat="javascript:alert(\'SMIL_REPEAT\')" />
  </rect>
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('onbegin=', $sanitizedSvg);
        $this->assertStringNotContainsString('onend=', $sanitizedSvg);
        $this->assertStringNotContainsString('onrepeat=', $sanitizedSvg);
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesXmlEntitiesAndDtdInjection(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE svg [
  <!ENTITY xxe SYSTEM "file:///etc/passwd">
  <!ENTITY js "javascript:alert(\'DTD_XSS\')">
]>
<svg xmlns="http://www.w3.org/2000/svg">
  <text>&xxe;</text>
  <a href="&js;">Click me</a>
  <rect x="10" y="10" width="50" height="50" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('<!DOCTYPE', $sanitizedSvg);
        $this->assertStringNotContainsString('<!ENTITY', $sanitizedSvg);
        $this->assertStringNotContainsString('SYSTEM', $sanitizedSvg);
        $this->assertStringNotContainsString('file:///', $sanitizedSvg);
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('&xxe;', $sanitizedSvg);
        $this->assertStringNotContainsString('&js;', $sanitizedSvg);
        $this->assertStringContainsString('<rect', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesDataUrisWithEmbeddedScripts(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <image href="data:text/html,<script>alert(\'DATA_HTML\')</script>" />
  <image xlink:href="data:image/svg+xml,<svg><script>alert(\'DATA_SVG\')</script></svg>" />
  <use href="data:text/html,<iframe src=javascript:alert(\'DATA_IFRAME\')></iframe>" />
  <rect x="10" y="10" width="50" height="50" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('data:text/html', $sanitizedSvg);
        $this->assertStringNotContainsString('data:image/svg+xml', $sanitizedSvg);
        $this->assertStringNotContainsString('<script', $sanitizedSvg);
        $this->assertStringNotContainsString('<iframe', $sanitizedSvg);
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringContainsString('<rect', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesNamespacedElementsWithDangerousAttributes(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:evil="http://evil.com/ns">
  <evil:script>alert("NAMESPACED_SCRIPT");</evil:script>
  <evil:rect onclick="alert(\'NAMESPACED_CLICK\')" x="10" y="10" width="50" height="50" />
  <g evil:onload="alert(\'NAMESPACED_ONLOAD\')">
    <circle cx="50" cy="50" r="20" />
  </g>
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('evil:', $sanitizedSvg);
        $this->assertStringNotContainsString('onclick=', $sanitizedSvg);
        $this->assertStringNotContainsString('onload=', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringContainsString('<circle', $sanitizedSvg);
    }
    
    #[Test]
    public function itRemovesFilterElementsWithExternalReferences(): void {
        $sanitizer = new SvgSanitizer();
        
        $maliciousSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
  <defs>
    <filter id="maliciousFilter">
      <feImage href="javascript:alert(\'FILTER_JS\')" />
      <feImage xlink:href="http://evil.com/tracker.php" />
      <feImage href="data:text/html,<script>alert(\'FILTER_DATA\')</script>" />
      <feGaussianBlur onload="alert(\'FILTER_BLUR\')" stdDeviation="5" />
    </filter>
  </defs>
  <rect x="10" y="10" width="50" height="50" filter="url(#maliciousFilter)" />
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('http://evil.com', $sanitizedSvg);
        $this->assertStringNotContainsString('data:text/html', $sanitizedSvg);
        $this->assertStringNotContainsString('onload=', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringContainsString('<rect', $sanitizedSvg);
    }
    
    #[Test]
    public function itSanitizesValidSvgFile(): void {
        $sanitizer = new SvgSanitizer();
        
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        
        $svgContent = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">
  <circle cx="50" cy="50" r="40" fill="blue" />
</svg>';
        
        file_put_contents($tempPath, $svgContent);
        
        $file = new File($tempPath);
        $result = $sanitizer->sanitizeFile($file);
        
        $this->assertInstanceOf(File::class, $result);
        $this->assertSame($tempPath, $result->getPathname());
        
        $sanitizedContent = file_get_contents($tempPath);
        $this->assertIsString($sanitizedContent);
        $this->assertStringContainsString('<circle', $sanitizedContent);
        
        fclose($tempFile);
    }
    
    #[Test]
    public function itPreservesLegitimateContent(): void {
        $sanitizer = new SvgSanitizer();
        
        $legitimateSvg = '<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200">
  <defs>
    <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" style="stop-color:rgb(255,255,0);stop-opacity:1" />
      <stop offset="100%" style="stop-color:rgb(255,0,0);stop-opacity:1" />
    </linearGradient>
    <filter id="blur">
      <feGaussianBlur stdDeviation="3" />
    </filter>
  </defs>
  <rect x="10" y="10" width="180" height="80" fill="url(#grad1)" />
  <circle cx="100" cy="130" r="30" fill="blue" filter="url(#blur)" />
  <text x="20" y="180" font-family="Arial" font-size="16" fill="black">Sample Text</text>
  <path d="M20 190 Q 100 160 180 190" stroke="green" stroke-width="2" fill="none" />
  <g transform="translate(50,50)">
    <ellipse cx="0" cy="0" rx="15" ry="10" fill="purple" />
  </g>
</svg>';
        
        $sanitizedSvg = $sanitizer->sanitize($legitimateSvg);
        
        $this->assertStringContainsString('<rect', $sanitizedSvg);
        $this->assertStringContainsString('<circle', $sanitizedSvg);
        $this->assertStringContainsString('<text', $sanitizedSvg);
        $this->assertStringContainsString('<path', $sanitizedSvg);
        $this->assertStringContainsString('<ellipse', $sanitizedSvg);
        $this->assertStringContainsString('<linearGradient', $sanitizedSvg);
        $this->assertStringContainsString('<feGaussianBlur', $sanitizedSvg);
        $this->assertStringContainsString('fill="blue"', $sanitizedSvg);
        $this->assertStringContainsString('stroke="green"', $sanitizedSvg);
        $this->assertStringContainsString('font-family="Arial"', $sanitizedSvg);
        $this->assertStringContainsString('transform="translate(50,50)"', $sanitizedSvg);
        $this->assertStringContainsString('Sample Text', $sanitizedSvg);
        
        $this->assertStringNotContainsString('javascript:', $sanitizedSvg);
        $this->assertStringNotContainsString('<script', $sanitizedSvg);
        $this->assertStringNotContainsString('alert(', $sanitizedSvg);
        $this->assertStringNotContainsString('onclick=', $sanitizedSvg);
        $this->assertStringNotContainsString('onload=', $sanitizedSvg);
    }
}

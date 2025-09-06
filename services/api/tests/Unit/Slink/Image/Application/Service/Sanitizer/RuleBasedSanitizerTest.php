<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Image\Application\Service\Sanitizer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Image\Application\Service\Sanitizer\RuleBasedSanitizer;
use Slink\Image\Application\Service\Sanitizer\Rule\ScriptRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\EventHandlerRemovalRule;
use Slink\Image\Application\Service\Sanitizer\Rule\JavaScriptUrlRemovalRule;

class RuleBasedSanitizerTest extends TestCase {
    
    #[Test]
    public function itAppliesRulesInPriorityOrder(): void {
        $sanitizer = new RuleBasedSanitizer(
            new EventHandlerRemovalRule(),
            new ScriptRemovalRule(), 
            new JavaScriptUrlRemovalRule()
        );
        
        $ruleNames = $sanitizer->getAppliedRuleNames();
        
        $this->assertEquals(['script_removal', 'event_handler_removal', 'javascript_url_removal'], $ruleNames);
    }
    
    #[Test]
    public function itCanAddAndRemoveRules(): void {
        $sanitizer = new RuleBasedSanitizer(new ScriptRemovalRule());
        
        $this->assertEquals(['script_removal'], $sanitizer->getAppliedRuleNames());
        
        $sanitizer->addRule(new EventHandlerRemovalRule());
        $this->assertContains('event_handler_removal', $sanitizer->getAppliedRuleNames());
        
        $sanitizer->removeRule('script_removal');
        $this->assertEquals(['event_handler_removal'], $sanitizer->getAppliedRuleNames());
    }
    
    #[Test]
    public function itSanitizesContentWithAllRules(): void {
        $sanitizer = new RuleBasedSanitizer(
            new ScriptRemovalRule(),
            new EventHandlerRemovalRule(),
            new JavaScriptUrlRemovalRule()
        );
        
        $maliciousSvg = '<svg><script>alert("xss")</script><rect onclick="evil()" href="javascript:alert()"/></svg>';
        $sanitized = $sanitizer->sanitize($maliciousSvg);
        
        $this->assertStringNotContainsString('<script>', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringContainsString('<rect', $sanitized);
    }
}

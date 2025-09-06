<?php

declare(strict_types=1);

namespace Slink\Image\Application\Service\Sanitizer;

use Slink\Image\Application\Service\Sanitizer\Rule\SanitizationRuleInterface;

final class RuleBasedSanitizer {
    /** @var SanitizationRuleInterface[] */
    private array $rules = [];
    
    public function __construct(SanitizationRuleInterface ...$rules) {
        $this->rules = $rules;
        $this->sortRulesByPriority();
    }
    
    public function sanitize(string $content): string {
        foreach ($this->rules as $rule) {
            $content = $rule->sanitize($content);
        }
        
        return $content;
    }
    
    public function addRule(SanitizationRuleInterface $rule): void {
        $this->rules[] = $rule;
        $this->sortRulesByPriority();
    }
    
    public function removeRule(string $ruleName): void {
        $this->rules = array_values(array_filter(
            $this->rules,
            fn(SanitizationRuleInterface $rule) => $rule->getName() !== $ruleName
        ));
    }
    
    /**
     * @return string[]
     */
    public function getAppliedRuleNames(): array {
        return array_map(
            fn(SanitizationRuleInterface $rule) => $rule->getName(),
            $this->rules
        );
    }
    
    private function sortRulesByPriority(): void {
        usort($this->rules, fn($a, $b) => $a->getPriority() <=> $b->getPriority());
    }
}

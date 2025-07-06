<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Application\Command\SaveSettings;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Application\Command\SaveSettings\SaveSettingsCommand;
use Slink\Settings\Domain\Enum\SettingCategory;

final class SaveSettingsCommandTest extends TestCase {
    #[Test]
    public function itShouldCreateCommandWithValidData(): void {
        $category = 'user';
        $settings = ['approvalRequired' => true, 'allowRegistration' => false];
        
        $command = new SaveSettingsCommand($category, $settings);
        
        $this->assertSame($category, $command->getCategory());
        $this->assertSame($settings, $command->getSettings());
    }

    #[Test]
    #[DataProvider('validCategoryProvider')]
    public function itShouldAcceptValidCategories(string $category): void {
        $settings = ['some' => 'setting'];
        
        $command = new SaveSettingsCommand($category, $settings);
        
        $this->assertSame($category, $command->getCategory());
    }

    #[Test]
    public function itShouldStoreSettingsArray(): void {
        $settings = [
            'approvalRequired' => true,
            'allowRegistration' => false,
            'password' => [
                'minLength' => 8,
                'requireSpecialChars' => false
            ]
        ];
        
        $command = new SaveSettingsCommand('user', $settings);
        
        $this->assertSame($settings, $command->getSettings());
    }

    #[Test]
    public function itShouldHandleEmptySettingsArray(): void {
        $command = new SaveSettingsCommand('user', []);
        
        $this->assertSame([], $command->getSettings());
    }

    #[Test]
    public function itShouldPreserveNestedArrays(): void {
        $settings = [
            'level1' => [
                'level2' => [
                    'level3' => 'value'
                ]
            ]
        ];
        
        $command = new SaveSettingsCommand('user', $settings);
        
        $this->assertSame($settings, $command->getSettings());
    }

    #[Test]
    public function itShouldHandleMixedDataTypes(): void {
        $settings = [
            'boolean' => true,
            'integer' => 42,
            'string' => 'test',
            'array' => [1, 2, 3],
            'null' => null,
            'float' => 3.14
        ];
        
        $command = new SaveSettingsCommand('user', $settings);
        
        $this->assertSame($settings, $command->getSettings());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function validCategoryProvider(): array {
        return [
            ['user'],
            ['image'],
            ['storage'],
        ];
    }
}

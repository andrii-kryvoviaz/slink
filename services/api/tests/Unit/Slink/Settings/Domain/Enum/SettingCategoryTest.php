<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Domain\Enum;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\Enum\SettingCategory;
use Slink\Settings\Domain\ValueObject\User\UserSettings;
use Slink\Settings\Domain\ValueObject\Image\ImageSettings;
use Slink\Settings\Domain\ValueObject\Storage\StorageSettings;

final class SettingCategoryTest extends TestCase {
    #[Test]
    #[DataProvider('categoryKeyProvider')]
    public function itShouldReturnCorrectCategoryKey(SettingCategory $category, string $expectedKey): void {
        $this->assertSame($expectedKey, $category->getCategoryKey());
    }

    #[Test]
    #[DataProvider('settingsClassProvider')]
    public function itShouldReturnCorrectSettingsClass(SettingCategory $category, string $expectedClass): void {
        $this->assertSame($expectedClass, $category->getSettingsCategoryRootClass());
    }

    #[Test]
    #[DataProvider('createSettingsProvider')]
    public function itShouldCreateSettingsCategoryRoot(
        SettingCategory $category,
        mixed $payload,
        string $expectedClass
    ): void {
        $settings = $category->createSettingsCategoryRoot($payload);
        
        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf($expectedClass, $settings);
        $this->assertSame($category, $settings->getSettingsCategory());
    }

    #[Test]
    public function itShouldHaveAllExpectedCases(): void {
        $expectedCases = ['User', 'Image', 'Storage'];
        $actualCases = array_map(fn($case) => $case->name, SettingCategory::cases());
        
        $this->assertSame($expectedCases, $actualCases);
    }

    #[Test]
    public function itShouldHaveCorrectValues(): void {
        $this->assertSame('user', SettingCategory::User->value);
        $this->assertSame('image', SettingCategory::Image->value);
        $this->assertSame('storage', SettingCategory::Storage->value);
    }

    #[Test]
    public function itShouldCreateFromStringValue(): void {
        $this->assertSame(SettingCategory::User, SettingCategory::from('user'));
        $this->assertSame(SettingCategory::Image, SettingCategory::from('image'));
        $this->assertSame(SettingCategory::Storage, SettingCategory::from('storage'));
    }

    #[Test]
    public function itShouldThrowExceptionForInvalidValue(): void {
        $this->expectException(\ValueError::class);
        
        SettingCategory::from('invalid');
    }

    #[Test]
    public function itShouldTryFromStringValue(): void {
        $this->assertSame(SettingCategory::User, SettingCategory::tryFrom('user'));
        $this->assertSame(SettingCategory::Image, SettingCategory::tryFrom('image'));
        $this->assertSame(SettingCategory::Storage, SettingCategory::tryFrom('storage'));
        $result = SettingCategory::tryFrom('invalid');
        /** @phpstan-ignore-next-line */
        $this->assertNull($result);
    }

    /**
     * @return array<int, array<int, SettingCategory|string>>
     */
    public static function categoryKeyProvider(): array
    {
        return [
            [SettingCategory::User, 'user'],
            [SettingCategory::Image, 'image'],
            [SettingCategory::Storage, 'storage'],
        ];
    }

    /**
     * @return array<int, array<int, SettingCategory|class-string>>
     */
    public static function settingsClassProvider(): array
    {
        return [
            [SettingCategory::User, UserSettings::class],
            [SettingCategory::Image, ImageSettings::class],
            [SettingCategory::Storage, StorageSettings::class],
        ];
    }

    /**
     * @return array<int, array{SettingCategory, array<string, mixed>, class-string}>
     */
    public static function createSettingsProvider(): array
    {
        return [
            [
                SettingCategory::User,
                [
                    'approvalRequired' => true,
                    'allowRegistration' => false,
                    'allowUnauthenticatedAccess' => false,
                    'password' => [
                        'minLength' => 8,
                        'requirements' => 0
                    ]
                ],
                UserSettings::class
            ],
            [
                SettingCategory::Image,
                [
                    'maxSize' => '5M',
                    'stripExifMetadata' => true,
                    'compressionQuality' => 85
                ],
                ImageSettings::class
            ],
            [
                SettingCategory::Storage,
                [
                    'provider' => 'local',
                    'adapter' => [
                        'local' => [
                            'dir' => '/storage'
                        ],
                        'smb_share' => null,
                        'amazon_s3' => null
                    ]
                ],
                StorageSettings::class
            ],
        ];
    }
}

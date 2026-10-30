<?php

declare(strict_types=1);

namespace Unit\Slink\Settings\Infrastructure\Specification;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Settings\Domain\ValueObject\Image\ImageSettings;
use Slink\Settings\Domain\ValueObject\User\UserSettings;
use Slink\Settings\Infrastructure\Specification\SignInMethodSpecification;
use Slink\User\Domain\Specification\EnabledSsoProviderSpecificationInterface;

final class SignInMethodSpecificationTest extends TestCase {
  private const array USER_PAYLOAD = [
    'approvalRequired' => false,
    'allowRegistration' => true,
    'password' => ['minLength' => 8, 'requirements' => 0],
  ];

  #[Test]
  public function itIsSatisfiedByAnotherCategoryWithoutQueryingProviders(): void {
    $providers = $this->createMock(EnabledSsoProviderSpecificationInterface::class);
    $providers->expects($this->never())->method('isSatisfied');

    $settings = ImageSettings::fromPayload(['maxSize' => '5M']);

    $this->assertTrue((new SignInMethodSpecification($providers))->isSatisfiedBy($settings));
  }

  #[Test]
  public function itIsSatisfiedWhenLocalAuthIsOnWithoutQueryingProviders(): void {
    $providers = $this->createMock(EnabledSsoProviderSpecificationInterface::class);
    $providers->expects($this->never())->method('isSatisfied');

    $settings = UserSettings::fromPayload([...self::USER_PAYLOAD, 'localAuthEnabled' => true]);

    $this->assertTrue((new SignInMethodSpecification($providers))->isSatisfiedBy($settings));
  }

  #[Test]
  public function itIsSatisfiedWhenLocalAuthIsOffAndAProviderIsEnabled(): void {
    $providers = $this->createStub(EnabledSsoProviderSpecificationInterface::class);
    $providers->method('isSatisfied')->willReturn(true);

    $settings = UserSettings::fromPayload([...self::USER_PAYLOAD, 'localAuthEnabled' => false]);

    $this->assertTrue((new SignInMethodSpecification($providers))->isSatisfiedBy($settings));
  }

  #[Test]
  public function itIsNotSatisfiedWhenLocalAuthIsOffAndNoProviderIsEnabled(): void {
    $providers = $this->createStub(EnabledSsoProviderSpecificationInterface::class);
    $providers->method('isSatisfied')->willReturn(false);

    $settings = UserSettings::fromPayload([...self::USER_PAYLOAD, 'localAuthEnabled' => false]);

    $this->assertFalse((new SignInMethodSpecification($providers))->isSatisfiedBy($settings));
  }
}

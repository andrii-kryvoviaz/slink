<?php

declare(strict_types=1);

namespace Tests\Integration\Console\Boot;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Boot\BootContext;
use Slink\Shared\Application\Boot\BootResult;
use Slink\Shared\Application\Boot\BootStatus;
use Slink\Shared\Application\Boot\Step\AuthInfoStep;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
use Slink\User\Application\Command\UpdateOAuthProvider\UpdateOAuthProviderCommand;
use Slink\User\Domain\Filter\OAuthProviderFilter;
use Slink\User\Domain\Repository\OAuthProviderRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Tests\Integration\Http\HttpTestCase;

#[Group('sc-1137')]
final class LocalAuthLockoutBootTest extends HttpTestCase {
  use RunsBootCommand;

  private const string ENV_DEFAULT = 'USER_LOCAL_AUTH_ENABLED';

  /** @var list<string> */
  private const array LOCKOUT_PHRASES = [
    'local authentication is disabled',
    'no SSO provider is enabled',
    'nobody can sign in',
  ];

  /** @var list<array{string, string}> */
  private const array ACCESS_ROWS = [
    ['Public User Registration', 'Enabled'],
    ['New Account Approval', 'Required'],
    ['Unauthenticated Access', 'Enabled'],
    ['Anonymous Guest Uploads', 'Disabled'],
    ['Auth Required for Media Shares', 'Enabled'],
    ['Auth Required for Collection Shares', 'Disabled'],
    ['Force HTTPS Connections', 'Enabled'],
    ['Minimum Password Length', '12 characters'],
  ];

  /**
   * @return iterable<string, array{bool}>
   */
  public static function providerlessProvider(): iterable {
    yield 'no provider configured' => [false];
    yield 'only a disabled provider' => [true];
  }

  #[Test]
  #[DataProvider('providerlessProvider')]
  public function itWarnsOfTheLockoutWhenTheEnvDefaultTurnsLocalAuthOffAndNoProviderIsEnabled(bool $withDisabledProvider): void {
    $this->useEnv([self::ENV_DEFAULT => 'false']);

    if ($withDisabledProvider) {
      $this->createProvider(enabled: false);
    }

    $this->assertLockoutConfiguration();

    $this->assertLockoutWarning($this->runAccessStep());
  }

  #[Test]
  public function itWarnsOfTheLockoutWhenAnAdminSavedValueTurnsLocalAuthOffAndTheLastProviderWasDisabled(): void {
    $this->useEnv([self::ENV_DEFAULT => 'true']);
    $providerId = $this->createProvider(enabled: true);
    $this->saveAccessConfiguration(localAuthEnabled: false);
    $this->disableProvider($providerId);

    $this->assertLockoutConfiguration();

    $this->assertLockoutWarning($this->runAccessStep());
  }

  #[Test]
  public function itKeepsTheInfoResultWhenLocalAuthIsOffButAProviderIsEnabled(): void {
    $this->useEnv([self::ENV_DEFAULT => 'true']);
    $this->createProvider(enabled: true);
    $this->saveAccessConfiguration(localAuthEnabled: false);

    $this->assertEffectiveLocalAuth(false);
    self::assertSame(1, $this->enabledProviderCount());

    $this->assertUnchangedInfoResult($this->runAccessStep());
  }

  #[Test]
  public function itKeepsTheInfoResultWhenTheEnvDefaultTurnsLocalAuthOffButAProviderIsEnabled(): void {
    $this->useEnv([self::ENV_DEFAULT => 'false']);
    $this->createProvider(enabled: true);

    $this->assertEffectiveLocalAuth(false);
    self::assertSame(1, $this->enabledProviderCount());

    self::assertSame(BootStatus::Info, $this->runAccessStep()->status);
  }

  #[Test]
  public function itKeepsTheInfoResultWhenLocalAuthIsOnAndNoProviderExists(): void {
    $this->useEnv([self::ENV_DEFAULT => 'true']);
    $this->saveAccessConfiguration(localAuthEnabled: true);

    $this->assertEffectiveLocalAuth(true);
    self::assertSame(0, $this->enabledProviderCount());

    $this->assertUnchangedInfoResult($this->runAccessStep());
  }

  #[Test]
  public function anAdminSavedTrueOverridesAFalseEnvDefaultSoNoLockoutIsReported(): void {
    $this->useEnv([self::ENV_DEFAULT => 'false']);
    $this->saveAccessConfiguration(localAuthEnabled: true);

    $this->assertEffectiveLocalAuth(true);
    self::assertSame(0, $this->enabledProviderCount());

    $this->assertUnchangedInfoResult($this->runAccessStep());
  }

  #[Test]
  public function theApiBootsToCompletionAndReportsTheLockout(): void {
    $this->useEnv([self::ENV_DEFAULT => 'false']);
    $this->assertLockoutConfiguration();

    [$status, $output] = $this->bootResult();

    self::assertSame(0, $status, $output);
    $reported = (string) \preg_replace('/\s+/', ' ', $output);

    foreach (self::LOCKOUT_PHRASES as $phrase) {
      self::assertStringContainsStringIgnoringCase($phrase, $reported);
    }
  }

  private function runAccessStep(): BootResult {
    $step = static::getContainer()->get(AuthInfoStep::class);
    self::assertInstanceOf(AuthInfoStep::class, $step);
    self::assertNotNull(self::$kernel);

    return $step->run(new BootContext(new Application(self::$kernel)));
  }

  private function assertLockoutConfiguration(): void {
    $this->assertEffectiveLocalAuth(false);
    self::assertSame(0, $this->enabledProviderCount());
  }

  private function assertLockoutWarning(BootResult $result): void {
    self::assertSame(BootStatus::Warn, $result->status);
    self::assertFalse($result->status->isFailure());

    foreach (self::LOCKOUT_PHRASES as $phrase) {
      self::assertStringContainsStringIgnoringCase($phrase, (string) $result->detail);
    }
  }

  private function assertUnchangedInfoResult(BootResult $result): void {
    self::assertEquals(new BootResult(BootStatus::Info, settings: self::ACCESS_ROWS), $result);
  }

  private function assertEffectiveLocalAuth(bool $expected): void {
    $config = static::getContainer()->get(ConfigurationProviderInterface::class);
    self::assertInstanceOf(ConfigurationProviderInterface::class, $config);
    self::assertSame($expected, (bool) $config->get('user.localAuthEnabled'));
  }

  private function enabledProviderCount(): int {
    $repository = static::getContainer()->get(OAuthProviderRepositoryInterface::class);
    self::assertInstanceOf(OAuthProviderRepositoryInterface::class, $repository);

    return \count($repository->getProviders(new OAuthProviderFilter(enabledOnly: true)));
  }

  private function createProvider(bool $enabled): string {
    /** @var string $providerId */
    $providerId = $this->commandBus()->handleSync(new CreateOAuthProviderCommand(
      name: 'Acme SSO',
      slug: 'acme',
      clientId: 'client-id-123',
      discoveryUrl: 'https://sso.local.test/.well-known/openid-configuration',
      clientSecret: 'client-secret-456',
      enabled: $enabled,
    ));

    return $providerId;
  }

  private function disableProvider(string $providerId): void {
    $command = new UpdateOAuthProviderCommand(enabled: false);

    $this->commandBus()->handleSync($command->withContext(['id' => $providerId]));
  }

  private function saveAccessConfiguration(bool $localAuthEnabled): void {
    $this->saveSettings('user', [
      'approvalRequired' => true,
      'allowRegistration' => true,
      'localAuthEnabled' => $localAuthEnabled,
      'password' => [
        'minLength' => 12,
        'requirements' => 0,
      ],
    ]);
    $this->setAccessSettings([
      'allowUnauthenticatedAccess' => true,
      'requireAuthForMediaShares' => true,
      'requireSsl' => true,
    ]);
  }
}

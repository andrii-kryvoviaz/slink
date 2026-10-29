<?php

declare(strict_types=1);

namespace Tests\Integration\Http\Registration;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Slink\User\Application\Command\CreateOAuthProvider\CreateOAuthProviderCommand;
use Slink\User\Domain\Specification\SignUpUsernameSpecification;
use Slink\User\Domain\ValueObject\DisplayName;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Domain\ValueObject\OAuth\OAuthIdentity;
use Slink\User\Domain\ValueObject\OAuth\OAuthSubject;
use Tests\Integration\Http\Double\StubOAuthAdapter;
use Tests\Integration\Http\HttpTestCase;

final class SsoDisplayNameUsernameTest extends HttpTestCase {
  private const string PROVIDER = 'acme';
  private const string INVALID_PREFERRED_USERNAME = 'a__b';

  protected function setUp(): void {
    parent::setUp();

    StubOAuthAdapter::reset();
    $this->allowRegistration();
    $this->commandBus()->handleSync(new CreateOAuthProviderCommand(
      name: 'Acme SSO',
      slug: self::PROVIDER,
      clientId: 'client-id-123',
      discoveryUrl: 'https://sso.local.test/.well-known/openid-configuration',
      clientSecret: 'client-secret-456',
      enabled: true,
    ));
  }

  protected function tearDown(): void {
    parent::tearDown();

    StubOAuthAdapter::reset();
  }

  #[Test]
  public function itStoresSignUpUsernameDerivedFromDisplayNameWithApostrophe(): void {
    self::assertSame(200, $this->signUpWithSso('oneil@local.test', "Pat O'Neil"), $this->responseContent());

    $username = $this->usernameOf('oneil@local.test');

    self::assertSame('pat.o.neil', $username);
    self::assertTrue((new SignUpUsernameSpecification())->isSatisfiedBy($username));
  }

  #[Test]
  public function itSuffixesUsernameWhenSecondSignUpDerivesTheSameOne(): void {
    self::assertSame(200, $this->signUpWithSso('oneil@local.test', "Pat O'Neil"), $this->responseContent());
    self::assertSame(200, $this->signUpWithSso('oneil2@local.test', "PAT O'NEIL"), $this->responseContent());

    self::assertSame('pat.o.neil', $this->usernameOf('oneil@local.test'));
    self::assertSame('pat.o.neil1', $this->usernameOf('oneil2@local.test'));
  }

  private function signUpWithSso(string $email, string $displayName): int {
    StubOAuthAdapter::setIdentity(new OAuthIdentity(
      OAuthSubject::fromPrimitives(self::PROVIDER, 'subject-' . \md5($email)),
      Email::fromString($email),
      DisplayName::fromString($displayName),
      true,
      self::INVALID_PREFERRED_USERNAME,
    ));

    return $this->apiRequest(
      'POST',
      '/api/auth/sso/token',
      null,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode(['code' => 'auth-code-123', 'state' => 'state-123'], JSON_THROW_ON_ERROR),
    );
  }

  private function usernameOf(string $email): string {
    /** @var EntityManagerInterface $entityManager */
    $entityManager = static::getContainer()->get(EntityManagerInterface::class);

    return (string) $entityManager->getConnection()->fetchOne(
      'SELECT username FROM "user" WHERE email = :email',
      ['email' => $email],
    );
  }

  private function responseContent(): string {
    return (string) $this->client->getResponse()->getContent();
  }
}

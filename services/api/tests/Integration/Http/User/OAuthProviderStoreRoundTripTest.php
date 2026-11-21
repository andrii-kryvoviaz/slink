<?php

declare(strict_types=1);

namespace Tests\Integration\Http\User;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Slink\Shared\Domain\ValueObject\AbstractStringValueObject;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Exception\NotFoundException;
use Slink\User\Application\Command\MoveOAuthProvider\MoveOAuthProviderCommand;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\OAuthProvider;
use Slink\User\Domain\Repository\OAuthProviderStoreRepositoryInterface;
use Tests\Integration\Http\Store\ViewBackedStoreRoundTripTestCase;

/**
 * @extends ViewBackedStoreRoundTripTestCase<OAuthProvider>
 */
#[Group('sc-1176')]
final class OAuthProviderStoreRoundTripTest extends ViewBackedStoreRoundTripTestCase {
  private const string CLIENT_ID = 'store-client-id';
  private const string CLIENT_SECRET = 'store-client-secret';
  private const string UNKNOWN_PROVIDER_ID = '11111111-2222-4333-8444-555555555555';

  private string $adminToken = '';

  #[\Override]
  protected function createAggregate(): ID {
    $this->adminToken = $this->bootAdmin();

    return ID::fromString($this->createProvider('acme'));
  }

  #[\Override]
  protected function fetch(ID $id): OAuthProvider {
    /** @var OAuthProviderStoreRepositoryInterface $store */
    $store = static::getContainer()->get(OAuthProviderStoreRepositoryInterface::class);

    return $store->get($id);
  }

  #[\Override]
  protected function aggregateState(object $aggregate): array {
    return [
      'name' => $this->stringState($aggregate, 'name'),
      'slug' => $this->stringState($aggregate, 'slug'),
      'type' => $this->stringState($aggregate, 'type'),
      'clientId' => $this->stringState($aggregate, 'clientId'),
      'clientSecret' => $this->stringState($aggregate, 'clientSecret'),
      'discoveryUrl' => $this->stringState($aggregate, 'discoveryUrl'),
      'scopes' => $this->stringState($aggregate, 'scopes'),
      'enabled' => $this->state($aggregate, 'enabled'),
      'sortOrder' => $this->state($aggregate, 'sortOrder'),
      'registrationPolicy' => $this->policyState($aggregate, 'registrationPolicy'),
      'approvalPolicy' => $this->policyState($aggregate, 'approvalPolicy'),
    ];
  }

  #[\Override]
  protected function storedState(ID $id): array {
    $row = $this->row($id);

    return [
      'name' => $row['name'],
      'slug' => $row['slug'],
      'type' => $row['type'],
      'clientId' => self::CLIENT_ID,
      'clientSecret' => self::CLIENT_SECRET,
      'discoveryUrl' => $row['discovery_url'],
      'scopes' => $row['scopes'],
      'enabled' => (bool) $row['enabled'],
      'sortOrder' => (float) $row['sort_order'],
      'registrationPolicy' => $row['registration_policy'],
      'approvalPolicy' => $row['approval_policy'],
    ];
  }

  #[\Override]
  protected function rootTable(): string {
    return 'oauth_provider';
  }

  #[\Override]
  protected function rootIdColumn(): string {
    return 'id';
  }

  #[Test]
  public function creatingUpdatingMovingAndRemovingAProviderChangesItsRow(): void {
    $this->adminToken = $this->bootAdmin();

    $id = ID::fromString($this->createProvider('acme'));
    $this->createProvider('other');

    self::assertSame(204, $this->updateProvider($id->toString(), ['name' => 'Renamed SSO']));
    self::assertSame('Renamed SSO', $this->row($id)['name']);

    $this->commandBus()->handleSync(new MoveOAuthProviderCommand($id->toString(), 1));
    $this->entityManager()->clear();
    self::assertSame((float) $this->row($id)['sort_order'], $this->state($this->reload($id), 'sortOrder'));

    self::assertSame(204, $this->removeProvider($id->toString()));
    self::assertSame(0, $this->countRowsByColumn('oauth_provider', 'id', $id->toString()));
  }

  #[Test]
  public function reloadedProviderHoldsThePlaintextCredentialsOfItsEncryptedRow(): void {
    $id = $this->createAggregate();
    $row = $this->row($id);

    $provider = $this->reload($id);

    self::assertNotSame(self::CLIENT_ID, $row['client_id']);
    self::assertNotSame(self::CLIENT_SECRET, $row['client_secret']);
    self::assertSame(self::CLIENT_ID, $this->stringState($provider, 'clientId'));
    self::assertSame(self::CLIENT_SECRET, $this->stringState($provider, 'clientSecret'));
  }

  #[Test]
  public function reloadedProviderTakesItsStateFromTheRow(): void {
    $id = $this->createAggregate();
    $this->readModel()->executeStatement(
      <<<'SQL'
        UPDATE "oauth_provider"
        SET name = ?, enabled = 0, sort_order = 7.5, scopes = ?, registration_policy = ?, approval_policy = ?
        WHERE id = ?
        SQL,
      ['Renamed in the row', 'openid email', 'blocked', 'none', $id->toString()],
    );

    $provider = $this->reload($id);

    self::assertSame('Renamed in the row', $this->stringState($provider, 'name'));
    self::assertFalse($this->state($provider, 'enabled'));
    self::assertSame(7.5, $this->state($provider, 'sortOrder'));
    self::assertSame('openid email', $this->stringState($provider, 'scopes'));
    self::assertSame(RegistrationPolicy::Blocked, $this->state($provider, 'registrationPolicy'));
    self::assertSame(ApprovalPolicy::None, $this->state($provider, 'approvalPolicy'));
  }

  #[Test]
  public function updatingAProviderKeepsItsEncryptedCredentialsInTheRow(): void {
    $id = $this->createAggregate();
    $encryptedBefore = $this->encryptedCredentials($id);

    self::assertSame(204, $this->updateProvider($id->toString(), ['name' => 'Renamed SSO']));

    self::assertSame('Renamed SSO', $this->row($id)['name']);
    self::assertSame($encryptedBefore, $this->encryptedCredentials($id));
    self::assertSame($this->storedState($id), $this->aggregateState($this->reload($id)));
  }

  #[Test]
  public function rotatingTheSecretStoresItEncryptedAndLeavesTheClientIdAlone(): void {
    $id = $this->createAggregate();
    $encryptedBefore = $this->encryptedCredentials($id);

    self::assertSame(204, $this->updateProvider($id->toString(), ['clientSecret' => 'rotated-secret']));

    $encryptedAfter = $this->encryptedCredentials($id);
    self::assertSame($encryptedBefore['clientId'], $encryptedAfter['clientId']);
    self::assertNotSame($encryptedBefore['clientSecret'], $encryptedAfter['clientSecret']);
    self::assertNotSame('rotated-secret', $encryptedAfter['clientSecret']);
    self::assertSame('rotated-secret', $this->stringState($this->reload($id), 'clientSecret'));
    self::assertSame(self::CLIENT_ID, $this->stringState($this->reload($id), 'clientId'));
  }

  #[Test]
  public function aRemovedProviderIsNotFound(): void {
    $id = $this->createAggregate();
    self::assertSame(204, $this->removeProvider($id->toString()));

    $this->expectException(NotFoundException::class);

    $this->reload($id);
  }

  #[Test]
  public function removingAProviderTwiceAnswersNotFoundTheSecondTime(): void {
    $id = $this->createAggregate();

    self::assertSame(204, $this->removeProvider($id->toString()));
    self::assertSame(404, $this->removeProvider($id->toString()));
  }

  #[Test]
  public function updatingAnUnknownProviderAnswersNotFound(): void {
    $this->adminToken = $this->bootAdmin();

    self::assertSame(404, $this->updateProvider(self::UNKNOWN_PROVIDER_ID, ['name' => 'Nobody']));
  }

  private function createProvider(string $slug): string {
    $status = $this->apiRequest(
      'POST',
      '/api/admin/oauth/providers',
      $this->adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode([
        'name' => 'Acme ' . $slug,
        'slug' => $slug,
        'clientId' => self::CLIENT_ID,
        'clientSecret' => self::CLIENT_SECRET,
        'discoveryUrl' => 'https://sso.local.test/.well-known/openid-configuration',
        'scopes' => 'openid profile',
        'registrationPolicy' => 'allowed',
        'approvalPolicy' => 'required',
      ], JSON_THROW_ON_ERROR),
    );

    self::assertSame(201, $status, 'Create provider failed: ' . (string) $this->client->getResponse()->getContent());

    return $this->extractId((string) $this->client->getResponse()->getContent());
  }

  /**
   * @param array<string, string> $changes
   */
  private function updateProvider(string $providerId, array $changes): int {
    return $this->apiRequest(
      'PUT',
      \sprintf('/api/admin/oauth/providers/%s', $providerId),
      $this->adminToken,
      ['CONTENT_TYPE' => 'application/json'],
      \json_encode($changes, JSON_THROW_ON_ERROR),
    );
  }

  private function removeProvider(string $providerId): int {
    return $this->apiRequest('DELETE', \sprintf('/api/admin/oauth/providers/%s', $providerId), $this->adminToken);
  }

  /**
   * @return array<string, mixed>
   */
  private function row(ID $id): array {
    $row = $this->readModel()->fetchAssociative('SELECT * FROM "oauth_provider" WHERE id = ?', [$id->toString()]);
    self::assertIsArray($row);

    return $row;
  }

  /**
   * @return array{clientId: mixed, clientSecret: mixed}
   */
  private function encryptedCredentials(ID $id): array {
    $row = $this->row($id);

    return ['clientId' => $row['client_id'], 'clientSecret' => $row['client_secret']];
  }

  private function stringState(OAuthProvider $provider, string $property): string {
    $value = $this->state($provider, $property);
    self::assertInstanceOf(AbstractStringValueObject::class, $value);

    return $value->toString();
  }

  private function policyState(OAuthProvider $provider, string $property): string {
    $policy = $this->state($provider, $property);
    self::assertInstanceOf(\BackedEnum::class, $policy);

    return (string) $policy->value;
  }

  private function state(OAuthProvider $provider, string $property): mixed {
    return new \ReflectionProperty(OAuthProvider::class, $property)->getValue($provider);
  }
}

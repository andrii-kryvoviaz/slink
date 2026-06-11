<?php

declare(strict_types=1);

namespace Slink\User\Domain;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasUpdated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasRemoved;
use Slink\Shared\Infrastructure\Encryption\EncryptionRegistry;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\OAuthType;
use Slink\User\Domain\ValueObject\OAuth\ProviderName;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;

final class OAuthProvider extends AbstractAggregateRoot {
  private ProviderName $name;
  private ProviderSlug $slug;
  private OAuthType $type;
  private ClientId $clientId;
  private ClientSecret $clientSecret;
  private DiscoveryUrl $discoveryUrl;
  private OAuthScopes $scopes;
  private bool $enabled;
  private float $sortOrder;
  private RegistrationPolicy $registrationPolicy;
  private ApprovalPolicy $approvalPolicy;

  protected function __construct(ID $id) {
    parent::__construct($id);
  }

  public static function create(
    ID $id,
    ProviderName $name,
    ProviderSlug $slug,
    OAuthType $type,
    ClientId $clientId,
    ClientSecret $clientSecret,
    DiscoveryUrl $discoveryUrl,
    OAuthScopes $scopes,
    RegistrationPolicy $registrationPolicy,
    ApprovalPolicy $approvalPolicy,
    bool $enabled,
    float $sortOrder = 0,
  ): self {
    $provider = new self($id);
    $provider->recordThat(new OAuthProviderWasCreated(
      $id,
      $name,
      $slug,
      $type,
      $clientId,
      $clientSecret,
      $discoveryUrl,
      $scopes,
      $registrationPolicy,
      $approvalPolicy,
      $enabled,
      $sortOrder,
    ));

    return $provider;
  }

  public function update(
    ?ProviderName $name = null,
    ?ProviderSlug $slug = null,
    ?OAuthType $type = null,
    ?ClientId $clientId = null,
    ?ClientSecret $clientSecret = null,
    ?DiscoveryUrl $discoveryUrl = null,
    ?OAuthScopes $scopes = null,
    ?RegistrationPolicy $registrationPolicy = null,
    ?ApprovalPolicy $approvalPolicy = null,
    ?bool $enabled = null,
    ?float $sortOrder = null,
  ): void {
    $this->recordThat(new OAuthProviderWasUpdated(
      $this->aggregateRootId(),
      $name,
      $slug,
      $type,
      $clientId,
      $clientSecret,
      $discoveryUrl,
      $scopes,
      $registrationPolicy,
      $approvalPolicy,
      $enabled,
      $sortOrder,
    ));
  }

  public function remove(): void {
    $this->recordThat(new OAuthProviderWasRemoved(
      $this->aggregateRootId()->toString(),
    ));
  }

  public function applyOAuthProviderWasCreated(OAuthProviderWasCreated $event): void {
    $this->name = $event->name;
    $this->slug = $event->slug;
    $this->type = $event->type;
    $this->clientId = $event->clientId;
    $this->clientSecret = $event->clientSecret;
    $this->discoveryUrl = $event->discoveryUrl;
    $this->scopes = $event->scopes;
    $this->enabled = $event->enabled;
    $this->sortOrder = $event->sortOrder;
    $this->registrationPolicy = $event->registrationPolicy;
    $this->approvalPolicy = $event->approvalPolicy;
  }

  public function applyOAuthProviderWasUpdated(OAuthProviderWasUpdated $event): void {
    $this->name = $event->name ?? $this->name;
    $this->slug = $event->slug ?? $this->slug;
    $this->type = $event->type ?? $this->type;
    $this->clientId = $event->clientId ?? $this->clientId;
    $this->clientSecret = $event->clientSecret ?? $this->clientSecret;
    $this->discoveryUrl = $event->discoveryUrl ?? $this->discoveryUrl;
    $this->scopes = $event->scopes ?? $this->scopes;
    $this->enabled = $event->enabled ?? $this->enabled;
    $this->sortOrder = $event->sortOrder ?? $this->sortOrder;
    $this->registrationPolicy = $event->registrationPolicy ?? $this->registrationPolicy;
    $this->approvalPolicy = $event->approvalPolicy ?? $this->approvalPolicy;
  }

  public function applyOAuthProviderWasRemoved(OAuthProviderWasRemoved $event): void {
  }

  /**
   * @return array<string, mixed>
   */
  protected function createSnapshotState(): array {
    return [
      'name' => $this->name->toString(),
      'slug' => $this->slug->toString(),
      'type' => $this->type->toString(),
      'clientId' => EncryptionRegistry::encrypt($this->clientId->toString()),
      'clientSecret' => EncryptionRegistry::encrypt($this->clientSecret->toString()),
      'discoveryUrl' => $this->discoveryUrl->toString(),
      'scopes' => $this->scopes->toString(),
      'enabled' => $this->enabled,
      'sortOrder' => $this->sortOrder,
      'registrationPolicy' => $this->registrationPolicy->value,
      'approvalPolicy' => $this->approvalPolicy->value,
    ];
  }

  /**
   * @param array<string, mixed> $state
   */
  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    $provider = new static(ID::fromString($id->toString()));

    $provider->name = ProviderName::fromString($state['name']);
    $provider->slug = ProviderSlug::fromString($state['slug']);
    $provider->type = OAuthType::fromString($state['type']);
    $provider->clientId = ClientId::fromString(EncryptionRegistry::decrypt($state['clientId']));
    $provider->clientSecret = ClientSecret::fromString(EncryptionRegistry::decrypt($state['clientSecret']));
    $provider->discoveryUrl = DiscoveryUrl::fromString($state['discoveryUrl']);
    $provider->scopes = OAuthScopes::fromString($state['scopes']);
    $provider->enabled = $state['enabled'];
    $provider->sortOrder = $state['sortOrder'] ?? 0.0;
    $provider->registrationPolicy = RegistrationPolicy::from($state['registrationPolicy'] ?? 'inherit');
    $provider->approvalPolicy = ApprovalPolicy::from($state['approvalPolicy'] ?? 'inherit');

    return $provider;
  }
}

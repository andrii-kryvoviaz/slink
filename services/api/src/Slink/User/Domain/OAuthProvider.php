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

final class OAuthProvider extends AbstractAggregateRoot {
  private string $name;
  private string $slug;
  private string $type;
  private string $clientId;
  private string $clientSecret;
  private string $discoveryUrl;
  private string $scopes;
  private bool $enabled;

  protected function __construct(ID $id) {
    parent::__construct($id);
  }

  public static function create(
    ID $id,
    string $name,
    string $slug,
    string $type,
    string $clientId,
    string $clientSecret,
    string $discoveryUrl,
    string $scopes,
    bool $enabled,
  ): self {
    $provider = new self($id);
    $provider->recordThat(new OAuthProviderWasCreated(
      $id->toString(),
      $name,
      $slug,
      $type,
      $clientId,
      $clientSecret,
      $discoveryUrl,
      $scopes,
      $enabled,
    ));

    return $provider;
  }

  public function update(
    ?string $name = null,
    ?string $slug = null,
    ?string $type = null,
    ?string $clientId = null,
    ?string $clientSecret = null,
    ?string $discoveryUrl = null,
    ?string $scopes = null,
    ?bool $enabled = null,
  ): void {
    $this->recordThat(new OAuthProviderWasUpdated(
      $this->aggregateRootId()->toString(),
      $name,
      $slug,
      $type,
      $clientId,
      $clientSecret,
      $discoveryUrl,
      $scopes,
      $enabled,
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
  }

  public function applyOAuthProviderWasUpdated(OAuthProviderWasUpdated $event): void {
    if ($event->name !== null) $this->name = $event->name;
    if ($event->slug !== null) $this->slug = $event->slug;
    if ($event->type !== null) $this->type = $event->type;
    if ($event->clientId !== null) $this->clientId = $event->clientId;
    if ($event->clientSecret !== null) $this->clientSecret = $event->clientSecret;
    if ($event->discoveryUrl !== null) $this->discoveryUrl = $event->discoveryUrl;
    if ($event->scopes !== null) $this->scopes = $event->scopes;
    if ($event->enabled !== null) $this->enabled = $event->enabled;
  }

  public function applyOAuthProviderWasRemoved(OAuthProviderWasRemoved $event): void {
  }

  /**
   * @return array<string, mixed>
   */
  protected function createSnapshotState(): array {
    return [
      'name' => $this->name,
      'slug' => $this->slug,
      'type' => $this->type,
      'clientId' => $this->clientId,
      'clientSecret' => $this->clientSecret,
      'discoveryUrl' => $this->discoveryUrl,
      'scopes' => $this->scopes,
      'enabled' => $this->enabled,
    ];
  }

  /**
   * @param array<string, mixed> $state
   */
  protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting {
    $provider = new static(ID::fromString($id->toString()));

    $provider->name = $state['name'];
    $provider->slug = $state['slug'];
    $provider->type = $state['type'];
    $provider->clientId = $state['clientId'];
    $provider->clientSecret = $state['clientSecret'];
    $provider->discoveryUrl = $state['discoveryUrl'];
    $provider->scopes = $state['scopes'];
    $provider->enabled = $state['enabled'];

    return $provider;
  }
}

<?php

declare(strict_types=1);

namespace Slink\User\Domain;

use Slink\Shared\Domain\AbstractAggregateRoot;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasCreated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasUpdated;
use Slink\User\Domain\Event\OAuthProvider\OAuthProviderWasRemoved;
use Slink\User\Domain\Enum\ApprovalPolicy;
use Slink\User\Domain\Enum\RegistrationPolicy;
use Slink\User\Domain\ValueObject\OAuth\ClientId;
use Slink\User\Domain\ValueObject\OAuth\ClientSecret;
use Slink\User\Domain\ValueObject\OAuth\DiscoveryUrl;
use Slink\User\Domain\ValueObject\OAuth\OAuthScopes;
use Slink\User\Domain\ValueObject\OAuth\OAuthType;
use Slink\User\Domain\ValueObject\OAuth\ProviderName;
use Slink\User\Domain\ValueObject\OAuth\ProviderSlug;
use Slink\User\Infrastructure\ReadModel\View\OAuthProviderView;

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

  public static function fromView(OAuthProviderView $view): self {
    $provider = new self(ID::fromString($view->getId()));
    $provider->name = ProviderName::fromString($view->getName());
    $provider->slug = $view->getSlug();
    $provider->type = OAuthType::fromString($view->getType());
    $provider->clientId = $view->getClientId();
    $provider->clientSecret = $view->getClientSecret();
    $provider->discoveryUrl = $view->getDiscoveryUrl();
    $provider->scopes = $view->getScopes();
    $provider->enabled = $view->isEnabled();
    $provider->sortOrder = $view->getSortOrder();
    $provider->registrationPolicy = $view->getRegistrationPolicy();
    $provider->approvalPolicy = $view->getApprovalPolicy();

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
}

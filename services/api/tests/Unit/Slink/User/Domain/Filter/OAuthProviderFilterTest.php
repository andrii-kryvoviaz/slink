<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Filter;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\User\Application\Query\GetOAuthProviders\GetOAuthProvidersQuery;
use Slink\User\Domain\Filter\OAuthProviderFilter;

final class OAuthProviderFilterTest extends TestCase {

  #[Test]
  public function itCreatesFromQueryWithEnabledOnlyTrue(): void {
    $query = new GetOAuthProvidersQuery(enabledOnly: true);
    $filter = OAuthProviderFilter::fromQuery($query);

    $this->assertInstanceOf(OAuthProviderFilter::class, $filter);
    $this->assertTrue($filter->isEnabledOnly());
  }

  #[Test]
  public function itCreatesFromQueryWithEnabledOnlyFalse(): void {
    $query = new GetOAuthProvidersQuery(enabledOnly: false);
    $filter = OAuthProviderFilter::fromQuery($query);

    $this->assertInstanceOf(OAuthProviderFilter::class, $filter);
    $this->assertFalse($filter->isEnabledOnly());
  }

  #[Test]
  public function itExposesIsEnabledOnlyGetter(): void {
    $filter = new OAuthProviderFilter(enabledOnly: false);

    $this->assertFalse($filter->isEnabledOnly());
  }
}

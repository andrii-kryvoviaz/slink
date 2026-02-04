<?php

declare(strict_types=1);

namespace Unit\Slink\User\Domain\Context;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\User\Domain\Context\SystemChangeUserRoleContext;
use Slink\User\Domain\Specification\UserRoleExistSpecificationInterface;
use Slink\User\Domain\ValueObject\Role;

final class SystemChangeUserRoleContextTest extends TestCase {
  #[Test]
  public function itBypassesCurrentUserCheck(): void {
    $roleExistSpec = $this->createStub(UserRoleExistSpecificationInterface::class);
    $context = new SystemChangeUserRoleContext($roleExistSpec);

    $this->assertFalse($context->currentUserSpecification->isSatisfiedBy(ID::generate()));
  }

  #[Test]
  public function itBypassesIsSameUserCheck(): void {
    $roleExistSpec = $this->createStub(UserRoleExistSpecificationInterface::class);
    $context = new SystemChangeUserRoleContext($roleExistSpec);

    $this->assertFalse($context->currentUserSpecification->isSameUser(ID::generate()));
  }

  #[Test]
  public function itDelegatesToRoleExistSpecification(): void {
    $roleExistSpec = $this->createMock(UserRoleExistSpecificationInterface::class);
    $roleExistSpec->expects($this->once())
      ->method('isSatisfiedBy')
      ->with($this->isInstanceOf(Role::class))
      ->willReturn(true);

    $context = new SystemChangeUserRoleContext($roleExistSpec);

    $result = $context->userRoleExistSpecification->isSatisfiedBy(Role::fromString('ROLE_ADMIN'));

    $this->assertTrue($result);
  }
}

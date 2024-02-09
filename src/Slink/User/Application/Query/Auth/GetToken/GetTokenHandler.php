<?php

declare(strict_types=1);

namespace Slink\User\Application\Query\Auth\GetToken;

use Slink\Shared\Application\Query\QueryHandlerInterface;
use Slink\User\Domain\Repository\GetUserCredentialsByEmailInterface;
use Slink\User\Domain\ValueObject\Email;
use Slink\User\Infrastructure\Auth\AuthProviderInterface;

final readonly class GetTokenHandler implements QueryHandlerInterface {
  public function __construct(
    private GetUserCredentialsByEmailInterface $userCredentialsByEmail,
    private AuthProviderInterface $authenticationProvider,
  ) {
  }
  
  public function __invoke(GetTokenQuery $query): string {
    [$uuid, $email, $hashedPassword] = $this->userCredentialsByEmail->getCredentialsByEmail(Email::fromString($query->getEmail()));
    
    return $this->authenticationProvider->generateToken($uuid, $email, $hashedPassword);
  }
}
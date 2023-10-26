<?php

declare(strict_types=1);

namespace Slik\User\Application\Query\Auth\GetToken;

use Slik\Shared\Application\Query\QueryHandlerInterface;
use Slik\User\Domain\Repository\GetUserCredentialsByEmailInterface;
use Slik\User\Domain\ValueObject\Email;
use Slik\User\Infrastructure\Auth\AuthProviderInterface;

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
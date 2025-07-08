<?php

declare(strict_types=1);

namespace Slink\User\Infrastructure\Auth;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class ApiKeyAuthenticator extends AbstractAuthenticator {
  public function __construct(
    private ApiKeyUserProvider $userProvider
  ) {}

  public function supports(Request $request): bool {
    $authHeader = $request->headers->get('Authorization');
    
    return $authHeader !== null && str_starts_with($authHeader, 'Bearer sk_');
  }

  public function authenticate(Request $request): Passport {
    $authHeader = $request->headers->get('Authorization');
    
    if ($authHeader === null) {
      throw new CustomUserMessageAuthenticationException('No API key provided');
    }

    $apiKey = str_replace('Bearer ', '', $authHeader);
    
    if (!str_starts_with($apiKey, 'sk_')) {
      throw new CustomUserMessageAuthenticationException('Invalid API key format');
    }

    return new SelfValidatingPassport(
      new UserBadge($apiKey, [$this->userProvider, 'loadUserByIdentifier'])
    );
  }

  public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
    return null;
  }

  public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response {
    return new JsonResponse([
      'error' => [
        'message' => 'Authentication failed',
        'code' => 'INVALID_API_KEY'
      ]
    ], 401);
  }
}

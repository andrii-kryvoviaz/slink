<?php

declare(strict_types=1);

namespace UI\Http\Rest\Controller\User;

use Slink\Settings\Application\Service\SettingsService;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\User\Infrastructure\Security\Voter\LocalAuthVoter;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use UI\Http\Rest\Request\SignUpRequest;
use UI\Http\Rest\Response\ApiResponse;

#[AsController]
#[IsGranted(LocalAuthVoter::LOCAL_AUTH_ENABLED, message: 'Local authentication is disabled', statusCode: 403)]
#[Route(path: '/auth/signup', name: 'signup_user', methods: ['POST'])]
final class SignUpController {
  use CommandTrait;

  /**
   * @param SignUpRequest $request
   * @param ConfigurationProviderInterface<SettingsService> $configurationProvider
   * @return ApiResponse
   */
  public function __invoke(
    #[MapRequestPayload] SignUpRequest $request,
    ConfigurationProviderInterface $configurationProvider
  ): ApiResponse {
    $command = $request->toCommand();
    $this->handle($command);

    $location = $configurationProvider->get('user.approvalRequired')
      ? '/profile/awaiting-approval'
      : '/profile/login';

    return ApiResponse::created((string) $command->getId(), $location);
  }
}

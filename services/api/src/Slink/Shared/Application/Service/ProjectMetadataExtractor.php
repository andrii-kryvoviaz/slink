<?php

declare(strict_types=1);

namespace Slink\Shared\Application\Service;

use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final readonly class ProjectMetadataExtractor {
  /**
   * @var array<string, mixed>
   */
  protected array $clientAppMetadata;
  
  public function __construct(
    protected KernelInterface $kernel,
    protected ConfigurationProviderInterface $configurationProvider
  ) {
    $packageJsonPath = $this->kernel->getEnvironment() === 'dev'
      ? '/client/package.json'
      : '/svelte-kit/package.json';
    
    $jsonContent = file_get_contents($this->kernel->getProjectDir() . $packageJsonPath);
    
    $this->clientAppMetadata = $jsonContent === false ? [] : json_decode($jsonContent, true);
  }
  
  public function version(): string {
//    return $this->clientAppMetadata['version'] ?? 'N/A';
    // ToDo: Investigate why github workflow does not pick up the latest package.json, hide the version for now
    return '';
  }
  
  /**
   * @return array<int, array<string>>
   */
  public function getSettings(): array {
    return [
      ['Timezone', date_default_timezone_get().' (<comment>'. new \DateTimeImmutable()->format(\DateTimeInterface::W3C).'</>)'],
      ['Storage', $this->configurationProvider->get('storage.provider')],
      ['Max Upload', $this->configurationProvider->get('image.maxSize')],
      ['Strip Exif', $this->filterBoolean($this->configurationProvider->get('image.stripExifMetadata'))],
      ['Compression', $this->configurationProvider->get('image.compressionQuality') . '%'],
      ['Sign-Up', $this->filterBoolean($this->configurationProvider->get('user.allowRegistration'), 'Enabled', 'Disabled')],
      ['Unauthenticated Access', $this->filterBoolean($this->configurationProvider->get('access.allowGuestUploads') || $this->configurationProvider->get('access.allowUnauthenticatedAccess'), 'Enabled', 'Disabled')],
      ['User Approval', $this->filterBoolean($this->configurationProvider->get('user.approvalRequired'), 'Required', 'Not Required')],
      ['Demo Mode', $this->filterBoolean($this->configurationProvider->get('demo.enabled'), 'Enabled', 'Disabled')],
    ];
  }
  
  /**
   * @return array<int, array<string>>
   */
  public function getClientAppDetails(): array {
    return [
      ['<info>Node</>', trim(shell_exec('node -v') ?: 'N/A', 'v')],
      ['Svelte', trim($this->clientAppMetadata['devDependencies']['svelte'] ?? 'N/A', '^')],
      ['SvelteKit', trim($this->clientAppMetadata['devDependencies']['@sveltejs/kit'] ?? 'N/A', '^')],
    ];
  }
  
  protected function filterBoolean(bool $value, string $confirm = 'Yes', string $deny = 'No'): string {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? $confirm : $deny;
  }
}
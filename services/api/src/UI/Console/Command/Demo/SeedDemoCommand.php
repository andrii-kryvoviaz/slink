<?php

declare(strict_types=1);

namespace UI\Console\Command\Demo;

use Slink\Image\Application\Command\TagImage\TagImageCommand;
use Slink\Image\Application\Command\UploadImage\UploadImageCommand;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Settings\Domain\Provider\ConfigurationProviderInterface;
use Slink\Shared\Application\Command\CommandTrait;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Tag\Application\Command\CreateTag\CreateTagCommand;
use Slink\Tag\Domain\Repository\TagRepositoryInterface;
use Slink\User\Domain\Repository\UserRepositoryInterface;
use Slink\User\Domain\ValueObject\Username;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(
  name: 'slink:demo:seed',
  description: 'Seeds demo instance with images from mounted pictures directory'
)]
final class SeedDemoCommand extends Command {
  use CommandTrait;

  private const DEMO_PICTURES_PATH = '/app/docker/demo/pictures';
  private const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'tga', 'svg'];
  
  private const PREDEFINED_TAGS = [
    'Nature' => ['Mountain', 'Forest', 'Ocean', 'Desert', 'Lake', 'River'],
    'Urban' => ['Architecture', 'Street', 'Skyline', 'Night'],
    'People' => ['Portrait', 'Group', 'Candid'],
    'Animals' => ['Wildlife', 'Pets', 'Birds'],
    'Art' => ['Abstract', 'Digital', 'Traditional'],
    'Travel' => ['Landscape', 'Culture', 'Adventure'],
  ];

  public function __construct(
    private readonly ConfigurationProviderInterface $configurationProvider,
    private readonly UserRepositoryInterface $userRepository,
    private readonly TagRepositoryInterface $tagRepository,
    private readonly ImageRepositoryInterface $imageRepository,
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $io = new SymfonyStyle($input, $output);

    if (!$this->configurationProvider->get('demo.enabled')) {
      $io->error('Demo mode is not enabled');
      return Command::FAILURE;
    }

    if (!is_dir(self::DEMO_PICTURES_PATH)) {
      $io->error('Demo pictures directory not found: ' . self::DEMO_PICTURES_PATH);
      return Command::FAILURE;
    }

    try {
      $demoUsername = $this->configurationProvider->get('demo.demoUsername');
      $demoUser = $this->userRepository->oneByUsername(Username::fromString($demoUsername));
      $userId = $demoUser->getUuid();
      
      $io->info('Creating predefined tags...');
      $tagMap = $this->createPredefinedTags($userId, $io);
      
      $hasExistingImages = $this->hasExistingImages();
      
      if ($hasExistingImages) {
        $io->warning('Demo instance already has images uploaded. Skipping image upload, but will assign tags to untagged images.');
        
        $io->info('Finding existing images to tag...');
        $existingImages = $this->imageRepository->findByUserId(ID::fromString($userId));
        
        if (!empty($existingImages)) {
          $io->info(sprintf('Found %d existing images, assigning tags...', count($existingImages)));
          $io->progressStart(count($existingImages));
          
          $taggedCount = 0;
          foreach ($existingImages as $imageView) {
            if ($this->assignRandomTags($imageView->getUuid(), $userId, $tagMap, $io)) {
              $taggedCount++;
            }
            $io->progressAdvance();
          }
          
          $io->progressFinish();
          $io->success(sprintf('Successfully assigned tags to %d images', $taggedCount));
        }
        
        return Command::SUCCESS;
      }
      
      $io->info('Scanning demo pictures directory for images...');
      $imageFiles = $this->scanImageFiles();
      
      if (empty($imageFiles)) {
        $io->warning('No image files found in demo pictures directory');
        return Command::SUCCESS;
      }

      $io->info(sprintf('Found %d image files to upload', count($imageFiles)));
      $io->progressStart(count($imageFiles));

      $uploadedCount = 0;
      foreach ($imageFiles as $imageFile) {
        try {
          $file = new File($imageFile);
          $command = new UploadImageCommand(
            image: $file,
            isPublic: true,
          );

          $this->handle($command->withContext([
            'userId' => $userId
          ]));
          
          $imageId = $command->getId()->toString();
          $this->assignRandomTags($imageId, $userId, $tagMap, $io);
          
          $uploadedCount++;
          $io->progressAdvance();
        } catch (\Exception $e) {
          $io->writeln(sprintf('<error>Failed to upload %s: %s</error>', basename($imageFile), $e->getMessage()));
        }
      }

      $io->progressFinish();
      $io->success(sprintf('Successfully uploaded %d out of %d images to demo instance', $uploadedCount, count($imageFiles)));

      return Command::SUCCESS;
    } catch (\Exception $e) {
      $io->error('Failed to seed demo images: ' . $e->getMessage());
      return Command::FAILURE;
    }
  }

  private function hasExistingImages(): bool {
    $imagesDir = $this->configurationProvider->get('storage.adapter.local.dir') . '/slink/images';
    
    if (!is_dir($imagesDir)) {
      return false;
    }

    $files = scandir($imagesDir);
    if ($files === false) {
      return false;
    }

    $imageFiles = array_filter($files, function ($file) use ($imagesDir) {
      if (in_array($file, ['.', '..', '.DS_Store'])) {
        return false;
      }
      return is_file($imagesDir . '/' . $file);
    });

    return !empty($imageFiles);
  }

  /**
   * @return array<string>
   */
  private function scanImageFiles(): array {
    $files = scandir(self::DEMO_PICTURES_PATH);
    if ($files === false) {
      return [];
    }

    $imageFiles = [];
    foreach ($files as $file) {
      if (in_array($file, ['.', '..', '.DS_Store'])) {
        continue;
      }

      $filePath = self::DEMO_PICTURES_PATH . '/' . $file;
      if (!is_file($filePath)) {
        continue;
      }

      $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
      if (in_array($extension, self::SUPPORTED_EXTENSIONS)) {
        $imageFiles[] = $filePath;
      }
    }

    return $imageFiles;
  }

  /**
   * @return array<string, string>
   */
  private function createPredefinedTags(string $userId, SymfonyStyle $io): array {
    $userIdObj = ID::fromString($userId);
    $existingTags = $this->tagRepository->findByUserId($userIdObj);
    
    $existingTagsByName = [];
    $existingTagsByPath = [];
    foreach ($existingTags as $tag) {
      $existingTagsByPath[$tag->getPath()] = $tag->getUuid();
      $existingTagsByName[$tag->getName()] = $tag;
    }
    
    $tagMap = [];
    
    foreach (self::PREDEFINED_TAGS as $parentName => $children) {
      $parentPath = $parentName;
      $parentId = null;
      
      if (isset($existingTagsByPath[$parentPath])) {
        $parentId = $existingTagsByPath[$parentPath];
        $tagMap[$parentPath] = $parentId;
        $io->writeln(sprintf('<comment>Parent tag already exists: %s (ID: %s)</comment>', $parentName, $parentId));
      } else {
        try {
          $command = new CreateTagCommand($parentName);
          $this->handle($command->withContext(['userId' => $userId]));
          
          $createdTag = $this->tagRepository->findByNameAndParent($parentName, $userIdObj, null);
          if ($createdTag) {
            $parentId = $createdTag->getUuid();
            $tagMap[$parentPath] = $parentId;
            $io->writeln(sprintf('<info>Created parent tag: %s (ID: %s)</info>', $parentName, $parentId));
          }
        } catch (\Exception $e) {
          if (isset($existingTagsByName[$parentName])) {
            $existingParent = $existingTagsByName[$parentName];
            if ($existingParent->getParentId() === null) {
              $parentId = $existingParent->getUuid();
              $tagMap[$parentPath] = $parentId;
              $io->writeln(sprintf('<comment>Using existing parent tag after error: %s (ID: %s)</comment>', $parentName, $parentId));
            }
          }
          if (!$parentId) {
            $io->writeln(sprintf('<error>Failed to create parent tag %s: %s</error>', $parentName, $e->getMessage()));
            continue;
          }
        }
      }
      
      if (!$parentId) {
        $io->writeln(sprintf('<error>No parent ID for %s, skipping children</error>', $parentName));
        continue;
      }
      
      foreach ($children as $childName) {
        $childPath = $parentPath . ' > ' . $childName;
        
        if (isset($existingTagsByPath[$childPath])) {
          $tagMap[$childPath] = $existingTagsByPath[$childPath];
          $io->writeln(sprintf('<comment>Child tag already exists: %s</comment>', $childPath));
        } else {
          try {
            $command = new CreateTagCommand($childName, $parentId);
            $this->handle($command->withContext(['userId' => $userId]));
            
            $createdChildTag = null;
            foreach ($this->tagRepository->findByUserId($userIdObj) as $tag) {
              if ($tag->getName() === $childName && $tag->getParentId() === $parentId) {
                $createdChildTag = $tag;
                break;
              }
            }
            
            if ($createdChildTag) {
              $tagMap[$childPath] = $createdChildTag->getUuid();
              $io->writeln(sprintf('<info>Created child tag: %s (ID: %s)</info>', $childPath, $createdChildTag->getUuid()));
            }
          } catch (\Exception $e) {
            $foundChild = false;
            foreach ($existingTags as $tag) {
              if ($tag->getName() === $childName && $tag->getParentId() === $parentId) {
                $tagMap[$childPath] = $tag->getUuid();
                $io->writeln(sprintf('<comment>Using existing child tag after error: %s (ID: %s)</comment>', $childPath, $tag->getUuid()));
                $foundChild = true;
                break;
              }
            }
            if (!$foundChild) {
              $io->writeln(sprintf('<error>Failed to create child tag %s: %s</error>', $childPath, $e->getMessage()));
            }
          }
        }
      }
    }
    
    $io->writeln(sprintf('<info>Total tags in map: %d</info>', count($tagMap)));
    
    return $tagMap;
  }

  /**
   * @param array<string, string> $tagMap
   */
  private function assignRandomTags(string $imageId, string $userId, array $tagMap, SymfonyStyle $io): bool {
    try {
      $imageView = $this->imageRepository->oneById($imageId);
      
      if (!$imageView->getTags()->isEmpty()) {
        return false;
      }
      
      if (empty($tagMap)) {
        $io->writeln(sprintf('<error>TagMap is empty for image %s</error>', $imageId));
        return false;
      }
      
      $numTags = rand(1, 3);
      $availableTags = array_values($tagMap);
      shuffle($availableTags);
      $selectedTags = array_slice($availableTags, 0, min($numTags, count($availableTags)));
      
      $assignedCount = 0;
      foreach ($selectedTags as $tagId) {
        try {
          $command = new TagImageCommand($imageId, $tagId);
          $this->handle($command->withContext(['userId' => $userId]));
          $assignedCount++;
        } catch (\Exception $e) {
          $io->writeln(sprintf('<error>Failed to assign tag %s to image %s: %s</error>', $tagId, $imageId, $e->getMessage()));
        }
      }
      
      return $assignedCount > 0;
    } catch (\Exception $e) {
      $io->writeln(sprintf('<error>Error in assignRandomTags for image %s: %s</error>', $imageId, $e->getMessage()));
      return false;
    }
  }
}

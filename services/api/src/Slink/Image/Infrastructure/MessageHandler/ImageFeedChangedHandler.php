<?php

declare(strict_types=1);

namespace Slink\Image\Infrastructure\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Slink\Image\Domain\Enum\ImageFeedEventType;
use Slink\Image\Domain\Filter\ImageListFilter;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\Message\ImageFeedChanged;
use Slink\Shared\Domain\Service\ServerSentEventPublisherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[AsMessageHandler(bus: 'messenger.bus.feed', fromTransport: 'feed')]
final readonly class ImageFeedChangedHandler {
  public function __construct(
    private ManagerRegistry $doctrine,
    private ImageRepositoryInterface $imageRepository,
    private NormalizerInterface $serializer,
    private ServerSentEventPublisherInterface $publisher,
  ) {
  }

  public function __invoke(ImageFeedChanged $message): void {
    $this->doctrine->getManager('read_model')->clear();
    $images = $this->imageRepository->geImageList(new ImageListFilter(
      limit: 1,
      isPublic: true,
      uuids: [$message->imageId],
    ));

    foreach ($images as $image) {
      $eventType = $message->eventType;
      if ($eventType === ImageFeedEventType::Removed) {
        $eventType = ImageFeedEventType::Updated;
      }

      $this->publisher->publish('public-feed', [
        'event' => $eventType->value,
        'image' => $this->serializer->normalize($image, context: ['groups' => ['public']]),
      ]);
      return;
    }

    $this->publisher->publish('public-feed', [
      'event' => ImageFeedEventType::Removed->value,
      'imageId' => $message->imageId,
    ]);
  }
}

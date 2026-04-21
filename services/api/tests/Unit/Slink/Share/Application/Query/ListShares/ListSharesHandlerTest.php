<?php

declare(strict_types=1);

namespace Tests\Unit\Slink\Share\Application\Query\ListShares;

use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Slink\Collection\Domain\Repository\CollectionRepositoryInterface;
use Slink\Collection\Infrastructure\ReadModel\View\CollectionView;
use Slink\Image\Domain\Repository\ImageRepositoryInterface;
use Slink\Image\Infrastructure\ReadModel\View\ImageView;
use Slink\Share\Application\Query\ListShares\ListSharesHandler;
use Slink\Share\Application\Query\ListShares\ListSharesQuery;
use Slink\Share\Domain\Filter\ShareListFilter;
use Slink\Share\Domain\Enum\ShareExpiryFilter;
use Slink\Share\Domain\Enum\ShareProtectionFilter;
use Slink\Share\Domain\Enum\ShareableType;
use Slink\Share\Domain\Repository\ShareRepositoryInterface;
use Slink\Share\Domain\Service\ShareServiceInterface;
use Slink\Share\Domain\ValueObject\AccessControl;
use Slink\Share\Domain\ValueObject\ShareableReference;
use Slink\Share\Infrastructure\ReadModel\View\ShareView;
use Slink\Share\Infrastructure\Resource\ShareableMetaResolver;
use Slink\Shared\Domain\ValueObject\Date\DateTime;
use Slink\Shared\Domain\ValueObject\ID;
use Slink\Shared\Infrastructure\Pagination\CursorPaginator;

final class ListSharesHandlerTest extends TestCase {
  private ShareRepositoryInterface $shareRepository;
  private ImageRepositoryInterface $imageRepository;
  private CollectionRepositoryInterface $collectionRepository;
  private ShareServiceInterface $shareService;
  private ListSharesHandler $handler;

  protected function setUp(): void {
    parent::setUp();

    $this->shareRepository = $this->createStub(ShareRepositoryInterface::class);
    $this->imageRepository = $this->createStub(ImageRepositoryInterface::class);
    $this->collectionRepository = $this->createStub(CollectionRepositoryInterface::class);
    $this->shareService = $this->createStub(ShareServiceInterface::class);

    $this->handler = $this->createHandler();
  }

  private function createHandler(): ListSharesHandler {
    return new ListSharesHandler(
      $this->shareRepository,
      $this->shareService,
      new ShareableMetaResolver($this->imageRepository, $this->collectionRepository),
      new CursorPaginator(),
    );
  }

  #[Test]
  public function itReturnsEmptyCollectionWhenNoSharesExist(): void {
    $userId = ID::generate()->toString();

    $this->shareRepository
      ->method('getShareList')
      ->willReturn($this->emptyPaginator());

    $this->shareRepository
      ->method('countShareList')
      ->willReturn(0);

    $result = ($this->handler)(new ListSharesQuery(), $userId);

    $this->assertSame(0, $result->total);
    $this->assertSame([], iterator_to_array($result->data));
    $this->assertNull($result->nextCursor);
  }

  #[Test]
  public function itScopesByUserId(): void {
    $userId = ID::generate()->toString();
    $capturedFilter = null;

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('getShareList')
      ->willReturnCallback(function (ShareListFilter $filter) use (&$capturedFilter): \Doctrine\ORM\Tools\Pagination\Paginator {
        $capturedFilter = $filter;
        return $this->emptyPaginator();
      });
    $shareRepository->method('countShareList')->willReturn(0);

    $this->shareRepository = $shareRepository;
    $handler = $this->createHandler();

    ($handler)(new ListSharesQuery(), $userId);

    $this->assertInstanceOf(ShareListFilter::class, $capturedFilter);
    $this->assertSame($userId, $capturedFilter->getUserId());
  }

  #[Test]
  public function itPassesExpiryProtectionAndTypeFiltersToRepository(): void {
    $userId = ID::generate()->toString();
    $capturedFilter = null;

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('getShareList')
      ->willReturnCallback(function (ShareListFilter $filter) use (&$capturedFilter): \Doctrine\ORM\Tools\Pagination\Paginator {
        $capturedFilter = $filter;
        return $this->emptyPaginator();
      });
    $shareRepository->method('countShareList')->willReturn(0);

    $this->shareRepository = $shareRepository;
    $handler = $this->createHandler();

    $query = new ListSharesQuery(
      expiry: ShareExpiryFilter::HasExpiry,
      protection: ShareProtectionFilter::PasswordProtected,
      type: ShareableType::Image,
    );

    ($handler)($query, $userId);

    $this->assertInstanceOf(ShareListFilter::class, $capturedFilter);
    $this->assertSame(ShareExpiryFilter::HasExpiry, $capturedFilter->getExpiry());
    $this->assertSame(ShareProtectionFilter::PasswordProtected, $capturedFilter->getProtection());
    $this->assertSame(ShareableType::Image, $capturedFilter->getType());
  }

  #[Test]
  public function itPassesShareableScopeToRepository(): void {
    $userId = ID::generate()->toString();
    $shareableId = ID::generate()->toString();
    $capturedFilter = null;

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('getShareList')
      ->willReturnCallback(function (ShareListFilter $filter) use (&$capturedFilter): \Doctrine\ORM\Tools\Pagination\Paginator {
        $capturedFilter = $filter;
        return $this->emptyPaginator();
      });
    $shareRepository->method('countShareList')->willReturn(0);

    $this->shareRepository = $shareRepository;
    $handler = $this->createHandler();

    $query = new ListSharesQuery(shareableId: $shareableId, shareableType: ShareableType::Collection);

    ($handler)($query, $userId);

    $this->assertInstanceOf(ShareListFilter::class, $capturedFilter);
    $this->assertSame($shareableId, $capturedFilter->getShareableId());
    $this->assertSame(ShareableType::Collection, $capturedFilter->getShareableType());
  }

  #[Test]
  public function itPassesSearchTermToRepositoryWhenSet(): void {
    $userId = ID::generate()->toString();
    $capturedFilter = null;

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('getShareList')
      ->willReturnCallback(function (ShareListFilter $filter) use (&$capturedFilter): \Doctrine\ORM\Tools\Pagination\Paginator {
        $capturedFilter = $filter;
        return $this->emptyPaginator();
      });
    $shareRepository->method('countShareList')->willReturn(0);

    $this->shareRepository = $shareRepository;
    $handler = $this->createHandler();

    $query = new ListSharesQuery(searchTerm: 'SunSet');

    ($handler)($query, $userId);

    $this->assertInstanceOf(ShareListFilter::class, $capturedFilter);
    $this->assertSame('SunSet', $capturedFilter->getSearchTerm());
    $this->assertSame($userId, $capturedFilter->getUserId());
  }

  #[Test]
  public function itPassesSearchTermAlongsideExpiryAndTypeFilters(): void {
    $userId = ID::generate()->toString();
    $capturedFilter = null;

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('getShareList')
      ->willReturnCallback(function (ShareListFilter $filter) use (&$capturedFilter): \Doctrine\ORM\Tools\Pagination\Paginator {
        $capturedFilter = $filter;
        return $this->emptyPaginator();
      });
    $shareRepository->method('countShareList')->willReturn(0);

    $this->shareRepository = $shareRepository;
    $handler = $this->createHandler();

    $query = new ListSharesQuery(
      searchTerm: 'holiday',
      expiry: ShareExpiryFilter::Expired,
      type: ShareableType::Collection,
    );

    ($handler)($query, $userId);

    $this->assertInstanceOf(ShareListFilter::class, $capturedFilter);
    $this->assertSame('holiday', $capturedFilter->getSearchTerm());
    $this->assertSame(ShareExpiryFilter::Expired, $capturedFilter->getExpiry());
    $this->assertNull($capturedFilter->getProtection());
    $this->assertSame(ShareableType::Collection, $capturedFilter->getType());
    $this->assertSame($userId, $capturedFilter->getUserId());
  }

  #[Test]
  public function itForwardsNullSearchTermWhenNotProvided(): void {
    $userId = ID::generate()->toString();
    $capturedFilter = null;

    $shareRepository = $this->createMock(ShareRepositoryInterface::class);
    $shareRepository
      ->expects($this->once())
      ->method('getShareList')
      ->willReturnCallback(function (ShareListFilter $filter) use (&$capturedFilter): \Doctrine\ORM\Tools\Pagination\Paginator {
        $capturedFilter = $filter;
        return $this->emptyPaginator();
      });
    $shareRepository->method('countShareList')->willReturn(0);

    $this->shareRepository = $shareRepository;
    $handler = $this->createHandler();

    ($handler)(new ListSharesQuery(), $userId);

    $this->assertInstanceOf(ShareListFilter::class, $capturedFilter);
    $this->assertNull($capturedFilter->getSearchTerm());
  }

  #[Test]
  public function itBuildsResponsesForImageAndCollectionShares(): void {
    $userId = ID::generate()->toString();
    $imageShareId = ID::generate();
    $imageId = ID::generate()->toString();
    $collectionShareId = ID::generate();
    $collectionId = ID::generate()->toString();

    $imageShare = new ShareView(
      $imageShareId->toString(),
      new ShareableReference($imageId, ShareableType::Image),
      '/image/file.jpg',
      DateTime::fromString('2026-04-20T10:00:00+00:00'),
      AccessControl::initial(true),
    );

    $collectionShare = new ShareView(
      $collectionShareId->toString(),
      new ShareableReference($collectionId, ShareableType::Collection),
      '/collection/' . $collectionId,
      DateTime::fromString('2026-04-21T10:00:00+00:00'),
      AccessControl::initial(true),
    );

    $image = $this->createStub(ImageView::class);
    $image->method('getUuid')->willReturn($imageId);
    $image->method('getFileName')->willReturn('file.jpg');

    $collection = $this->createStub(CollectionView::class);
    $collection->method('getId')->willReturn($collectionId);
    $collection->method('getName')->willReturn('My Collection');

    $this->imageRepository
      ->method('findByIds')
      ->willReturn([$image]);

    $this->collectionRepository
      ->method('findByIds')
      ->willReturn([$collection]);

    $this->shareService
      ->method('resolveUrl')
      ->willReturn('https://example.test/share');

    $this->shareRepository
      ->method('getShareList')
      ->willReturn($this->paginatorFromItems([$imageShare, $collectionShare]));

    $this->shareRepository
      ->method('countShareList')
      ->willReturn(2);

    $result = ($this->handler)(new ListSharesQuery(), $userId);

    $items = iterator_to_array($result->data);
    $this->assertCount(2, $items);

    /** @var array<string, mixed> $firstResource */
    $firstResource = $items[0]->resource;
    /** @var array<string, mixed> $secondResource */
    $secondResource = $items[1]->resource;
    /** @var array<string, mixed> $firstShareable */
    $firstShareable = $firstResource['shareable'];
    /** @var array<string, mixed> $secondShareable */
    $secondShareable = $secondResource['shareable'];

    $this->assertSame('file.jpg', $firstShareable['name']);
    $this->assertSame('image', $firstResource['type']);
    $this->assertSame('My Collection', $secondShareable['name']);
    $this->assertSame('collection', $secondResource['type']);
    $this->assertSame(2, $result->total);
  }

  #[Test]
  public function itReturnsNextCursorWhenMoreResultsExist(): void {
    $userId = ID::generate()->toString();

    $shares = [];
    for ($i = 0; $i < 3; $i++) {
      $shares[] = new ShareView(
        ID::generate()->toString(),
        new ShareableReference(ID::generate()->toString(), ShareableType::Image),
        '/image/file.jpg',
        DateTime::fromString(\sprintf('2026-04-%02dT10:00:00+00:00', 10 + $i)),
        AccessControl::initial(true),
      );
    }

    $image = $this->createStub(ImageView::class);
    $image->method('getFileName')->willReturn('file.jpg');

    $this->imageRepository->method('findByIds')->willReturn([$image]);
    $this->shareService->method('resolveUrl')->willReturn('https://example.test/share');

    $this->shareRepository
      ->method('getShareList')
      ->willReturn($this->paginatorFromItems($shares));

    $this->shareRepository
      ->method('countShareList')
      ->willReturn(5);

    $query = new ListSharesQuery(limit: 2);

    $result = ($this->handler)($query, $userId);

    $this->assertSame(2, $result->limit);
    $this->assertSame(5, $result->total);
    $this->assertCount(2, iterator_to_array($result->data));
    $this->assertNotNull($result->nextCursor);
  }

  /**
   * @return Paginator<ShareView>
   */
  private function emptyPaginator(): Paginator {
    return $this->paginatorFromItems([]);
  }

  /**
   * @param array<int, ShareView> $items
   * @return Paginator<ShareView>
   */
  private function paginatorFromItems(array $items): Paginator {
    $paginator = $this->createStub(Paginator::class);
    $paginator->method('getIterator')->willReturn(new \ArrayIterator($items));
    $paginator->method('count')->willReturn(count($items));

    return $paginator;
  }
}

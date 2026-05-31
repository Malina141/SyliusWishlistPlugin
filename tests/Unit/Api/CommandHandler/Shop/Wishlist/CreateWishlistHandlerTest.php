<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Api\CommandHandler\Shop\Wishlist;

use Doctrine\Persistence\ObjectManager;
use Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist\CreateWishlist;
use Malina141\SyliusWishlistPlugin\Api\CommandHandler\Shop\Wishlist\CreateWishlistHandler;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Generator\WishlistTokenGeneratorInterface;
use Malina141\SyliusWishlistPlugin\Repository\WishlistRepositoryInterface;
use Override;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ApiBundle\Context\UserContextInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class CreateWishlistHandlerTest extends TestCase
{
    private ChannelRepositoryInterface&MockObject $channelRepository;

    private FactoryInterface&MockObject $wishlistFactory;

    private ObjectManager&MockObject $wishlistManager;

    private WishlistTokenGeneratorInterface&MockObject $wishlistTokenGenerator;

    private UserContextInterface&Stub $userContext;

    private WishlistRepositoryInterface&MockObject $wishlistRepository;

    private CreateWishlistHandler $handler;

    #[Override]
    protected function setUp(): void
    {
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->wishlistFactory = $this->createMock(FactoryInterface::class);
        $this->wishlistManager = $this->createMock(ObjectManager::class);
        $this->wishlistTokenGenerator = $this->createMock(WishlistTokenGeneratorInterface::class);
        $this->userContext = $this->createStub(UserContextInterface::class);
        $this->wishlistRepository = $this->createMock(WishlistRepositoryInterface::class);

        $this->handler = new CreateWishlistHandler(
            $this->channelRepository,
            $this->wishlistFactory,
            $this->wishlistManager,
            $this->wishlistTokenGenerator,
            $this->userContext,
            $this->wishlistRepository,
        );
    }

    #[Test]
    public function it_creates_guest_wishlist_for_channel(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);

        $this->channelRepository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with('FASHION_WEB')
            ->willReturn($channel)
        ;
        $this->wishlistFactory
            ->expects($this->once())
            ->method('createNew')
            ->willReturn($wishlist)
        ;
        $wishlist->expects($this->once())->method('setChannel')->with($channel);
        $this->wishlistTokenGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('wishlist-token')
        ;
        $wishlist->expects($this->once())->method('setToken')->with('wishlist-token');
        $this->userContext->method('getUser')->willReturn(null);
        $wishlist->expects($this->never())->method('setOwner');
        $this->wishlistManager->expects($this->once())->method('persist')->with($wishlist);
        $this->wishlistManager->expects($this->once())->method('flush');

        $this->assertSame($wishlist, ($this->handler)(new CreateWishlist('FASHION_WEB')));
    }

    #[Test]
    public function it_creates_owned_wishlist_for_authenticated_user(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);
        $user = $this->createMock(ShopUserInterface::class);

        $this->channelRepository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with('FASHION_WEB')
            ->willReturn($channel)
        ;
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findOneByOwnerAndChannel')
            ->with($user, $channel)
            ->willReturn(null)
        ;
        $this->wishlistFactory
            ->expects($this->once())
            ->method('createNew')
            ->willReturn($wishlist)
        ;
        $wishlist->expects($this->once())->method('setChannel')->with($channel);
        $this->wishlistTokenGenerator
            ->expects($this->once())
            ->method('generate')
            ->willReturn('wishlist-token')
        ;
        $wishlist->expects($this->once())->method('setToken')->with('wishlist-token');
        $this->userContext->method('getUser')->willReturn($user);
        $wishlist->expects($this->once())->method('setOwner')->with($user);
        $this->wishlistManager->expects($this->once())->method('persist')->with($wishlist);
        $this->wishlistManager->expects($this->once())->method('flush');

        $this->assertSame($wishlist, ($this->handler)(new CreateWishlist('FASHION_WEB')));
    }

    #[Test]
    public function it_throws_conflict_when_authenticated_user_already_has_wishlist_in_channel(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $wishlist = $this->createMock(WishlistInterface::class);
        $existingWishlist = $this->createMock(WishlistInterface::class);
        $user = $this->createMock(ShopUserInterface::class);

        $this->channelRepository
            ->expects($this->once())
            ->method('findOneByCode')
            ->with('FASHION_WEB')
            ->willReturn($channel)
        ;
        $this->wishlistFactory
            ->expects($this->once())
            ->method('createNew')
            ->willReturn($wishlist)
        ;
        $this->wishlistRepository
            ->expects($this->once())
            ->method('findOneByOwnerAndChannel')
            ->with($user, $channel)
            ->willReturn($existingWishlist)
        ;
        $this->userContext->method('getUser')->willReturn($user);

        $this->expectException(ConflictHttpException::class);

        ($this->handler)(new CreateWishlist('FASHION_WEB'));
    }
}

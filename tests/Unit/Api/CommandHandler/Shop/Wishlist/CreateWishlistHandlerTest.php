<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\Api\CommandHandler\Shop\Wishlist;

use Doctrine\Persistence\ObjectManager;
use Malina141\SyliusWishlistPlugin\Api\Command\Shop\Wishlist\CreateWishlist;
use Malina141\SyliusWishlistPlugin\Api\CommandHandler\Shop\Wishlist\CreateWishlistHandler;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Generator\WishlistTokenGeneratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;

final class CreateWishlistHandlerTest extends TestCase
{
    private ChannelRepositoryInterface&MockObject $channelRepository;

    private FactoryInterface&MockObject $wishlistFactory;

    private ObjectManager&MockObject $wishlistManager;

    private WishlistTokenGeneratorInterface&MockObject $wishlistTokenGenerator;

    private CreateWishlistHandler $handler;

    protected function setUp(): void
    {
        $this->channelRepository = $this->createMock(ChannelRepositoryInterface::class);
        $this->wishlistFactory = $this->createMock(FactoryInterface::class);
        $this->wishlistManager = $this->createMock(ObjectManager::class);
        $this->wishlistTokenGenerator = $this->createMock(WishlistTokenGeneratorInterface::class);

        $this->handler = new CreateWishlistHandler(
            $this->channelRepository,
            $this->wishlistFactory,
            $this->wishlistManager,
            $this->wishlistTokenGenerator,
        );
    }

    public function test_it_creates_guest_wishlist_for_channel(): void
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
        $this->wishlistManager->expects($this->once())->method('persist')->with($wishlist);
        $this->wishlistManager->expects($this->once())->method('flush');

        $this->assertSame($wishlist, ($this->handler)(new CreateWishlist('FASHION_WEB')));
    }
}

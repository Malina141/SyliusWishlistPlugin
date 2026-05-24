<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\EventSubscriber;

use Malina141\SyliusWishlistPlugin\EventSubscriber\MergeWishlistOnLoginSubscriber;
use Malina141\SyliusWishlistPlugin\Merger\GuestWishlistLoginMergerInterface;
use Malina141\SyliusWishlistPlugin\Options\WishlistCookieOptions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class MergeWishlistOnLoginSubscriberTest extends TestCase
{
    private const string COOKIE_NAME = 'wishlist_token';

    private GuestWishlistLoginMergerInterface&MockObject $guestWishlistLoginMerger;

    private MergeWishlistOnLoginSubscriber $sut;

    protected function setUp(): void
    {
        $this->guestWishlistLoginMerger = $this->createMock(GuestWishlistLoginMergerInterface::class);

        $this->sut = new MergeWishlistOnLoginSubscriber(
            new WishlistCookieOptions(self::COOKIE_NAME, 3600, '/', true, true, 'lax'),
            $this->guestWishlistLoginMerger,
        );
    }

    #[Test]
    public function it_subscribes_to_login_success_event(): void
    {
        $subscribedEvents = MergeWishlistOnLoginSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(LoginSuccessEvent::class, $subscribedEvents);
        $this->assertSame('mergeWishlists', $subscribedEvents[LoginSuccessEvent::class]);
    }

    #[Test]
    public function it_does_nothing_when_request_has_no_wishlist_token_cookie(): void
    {
        $event = $this->createLoginSuccessEvent(
            $this->createStub(ShopUserInterface::class),
            new Request(),
            new Response(),
        );

        $this->guestWishlistLoginMerger->expects($this->never())->method('merge');

        $this->sut->mergeWishlists($event);

        $this->assertSame([], $event->getResponse()?->headers->getCookies());
    }

    #[Test]
    public function it_does_not_merge_wishlist_for_non_shop_user(): void
    {
        $request = new Request(cookies: [self::COOKIE_NAME => 'guest-token']);
        $event = $this->createLoginSuccessEvent(
            new InMemoryUser('admin@example.com', null),
            $request,
            new Response(),
        );

        $this->guestWishlistLoginMerger->expects($this->never())->method('merge');

        $this->sut->mergeWishlists($event);

        $this->assertSame([], $event->getResponse()?->headers->getCookies());
    }

    #[Test]
    public function it_clears_cookie_after_attempting_to_merge_guest_wishlist(): void
    {
        $token = 'guest-token';
        $request = new Request(cookies: [self::COOKIE_NAME => $token]);
        $response = new Response();
        $shopUser = $this->createStub(ShopUserInterface::class);
        $event = $this->createLoginSuccessEvent($shopUser, $request, $response);

        $this->guestWishlistLoginMerger
            ->expects($this->once())
            ->method('merge')
            ->with($token, $shopUser);

        $this->sut->mergeWishlists($event);

        $cookies = $response->headers->getCookies();
        $this->assertCount(1, $cookies);
        $this->assertSame(self::COOKIE_NAME, $cookies[0]->getName());
        $this->assertSame('/', $cookies[0]->getPath());
        $this->assertTrue($cookies[0]->isSecure());
        $this->assertTrue($cookies[0]->isHttpOnly());
        $this->assertSame('lax', $cookies[0]->getSameSite());
        $this->assertLessThan(time(), $cookies[0]->getExpiresTime());
    }

    private function createLoginSuccessEvent(
        SymfonyUserInterface $user,
        Request $request,
        ?Response $response,
    ): LoginSuccessEvent {
        return new LoginSuccessEvent(
            $this->createStub(AuthenticatorInterface::class),
            new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), static fn (): SymfonyUserInterface => $user)),
            $this->createStub(TokenInterface::class),
            $request,
            $response,
            'shop',
        );
    }
}

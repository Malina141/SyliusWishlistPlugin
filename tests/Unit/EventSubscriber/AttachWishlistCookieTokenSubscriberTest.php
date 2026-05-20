<?php

declare(strict_types=1);

namespace Tests\Malina141\SyliusWishlistPlugin\Unit\EventSubscriber;

use Malina141\SyliusWishlistPlugin\EventSubscriber\AttachWishlistCookieTokenSubscriber;
use Malina141\SyliusWishlistPlugin\Factory\WishlistCookieFactoryInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class AttachWishlistCookieTokenSubscriberTest extends TestCase
{
    private const string COOKIE_NAME = 'wishlist_token';

    private WishlistCookieFactoryInterface&MockObject $wishlistCookieFactory;

    private AttachWishlistCookieTokenSubscriber $sut;

    protected function setUp(): void
    {
        $this->wishlistCookieFactory = $this->createMock(WishlistCookieFactoryInterface::class);
        $this->sut = new AttachWishlistCookieTokenSubscriber(
            $this->wishlistCookieFactory,
            self::COOKIE_NAME,
        );
    }

    #[Test]
    public function it_subscribes_to_kernel_response_event(): void
    {
        $subscribedEvents = AttachWishlistCookieTokenSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::RESPONSE, $subscribedEvents);
        $this->assertSame('attachWishlistCookieToken', $subscribedEvents[KernelEvents::RESPONSE]);
    }

    #[Test]
    public function it_does_not_attach_cookie_for_sub_requests(): void
    {
        $request = new Request();
        $request->attributes->set(self::COOKIE_NAME, 'some-token');
        $event = $this->createResponseEvent($request, HttpKernelInterface::SUB_REQUEST);

        $this->wishlistCookieFactory
            ->expects($this->never())
            ->method('create');

        $this->sut->attachWishlistCookieToken($event);
    }

    #[Test]
    public function it_does_not_attach_cookie_when_attribute_is_missing(): void
    {
        $request = new Request();
        $event = $this->createResponseEvent($request, HttpKernelInterface::MAIN_REQUEST);

        $this->wishlistCookieFactory
            ->expects($this->never())
            ->method('create');

        $this->sut->attachWishlistCookieToken($event);
    }

    #[Test]
    public function it_attaches_cookie_to_response_when_attribute_is_present(): void
    {
        $token = 'abc-123-token';
        $request = new Request();
        $request->attributes->set(self::COOKIE_NAME, $token);
        $event = $this->createResponseEvent($request, HttpKernelInterface::MAIN_REQUEST);

        $cookie = Cookie::create(self::COOKIE_NAME, $token);

        $this->wishlistCookieFactory
            ->expects($this->once())
            ->method('create')
            ->with($token)
            ->willReturn($cookie);

        $this->sut->attachWishlistCookieToken($event);

        $responseCookies = $event->getResponse()->headers->getCookies();
        $this->assertCount(1, $responseCookies);
        $this->assertSame($cookie, $responseCookies[0]);
    }

    private function createResponseEvent(Request $request, int $requestType): ResponseEvent
    {
        return new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            $requestType,
            new Response(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\EventListener\Workflow;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Provider\WishlistShareTokenProviderInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class ProvideWishlistShareTokenListener
{
    public function __construct(
        private WishlistShareTokenProviderInterface $shareTokenProvider,
    ) {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $subject = $event->getSubject();
        Assert::isInstanceOf($subject, WishlistInterface::class);

        $this->shareTokenProvider->provideShareToken($subject);
    }
}

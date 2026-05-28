<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Api\Applicator;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\SM\WishlistShareTransitions;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\ApiBundle\Exception\StateMachineTransitionFailedException;

final readonly class WishlistShareStateMachineTransitionApplicator
{
    public function __construct(
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function share(WishlistInterface $data): WishlistInterface
    {
        if (!$this->stateMachine->can($data, WishlistShareTransitions::GRAPH, WishlistShareTransitions::TRANSITION_SHARE)) {
            throw new StateMachineTransitionFailedException('Cannot share the wishlist.');
        }

        $this->stateMachine->apply($data, WishlistShareTransitions::GRAPH, WishlistShareTransitions::TRANSITION_SHARE);

        return $data;
    }

    public function unshare(WishlistInterface $data): WishlistInterface
    {
        if (!$this->stateMachine->can($data, WishlistShareTransitions::GRAPH, WishlistShareTransitions::TRANSITION_UNSHARE)) {
            throw new StateMachineTransitionFailedException('Cannot unshare the wishlist.');
        }

        $this->stateMachine->apply($data, WishlistShareTransitions::GRAPH, WishlistShareTransitions::TRANSITION_UNSHARE);

        return $data;
    }
}

<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Twig\Component\Wishlist;

use Doctrine\ORM\EntityManagerInterface;
use Malina141\SyliusWishlistPlugin\Context\WishlistContextInterface;
use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Malina141\SyliusWishlistPlugin\Form\Type\WishlistNameType;
use Sylius\Bundle\UiBundle\Twig\Component\TemplatePropTrait;
use Sylius\TwigHooks\LiveComponent\HookableLiveComponentTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class WishlistNameEditor
{
    use DefaultActionTrait;
    use HookableLiveComponentTrait;
    use TemplatePropTrait;
    use ComponentWithFormTrait;

    public WishlistInterface $wishlist;

    #[LiveProp]
    public bool $isEditing = false;

    public ?string $flashMessage = null;

    public function __construct(
        private readonly WishlistContextInterface $wishlistContext,
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
    ) {
        $this->wishlist = $this->wishlistContext->getWishlist();
    }

    #[LiveAction]
    public function activateEditing(): void
    {
        $this->isEditing = true;
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();

        $this->isEditing = false;
        $this->flashMessage = 'malina141_sylius_wishlist.ui.saved';

        $this->entityManager->flush();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->formFactory->create(WishlistNameType::class, $this->wishlist);
    }
}

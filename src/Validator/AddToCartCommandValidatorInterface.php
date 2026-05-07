<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Validator;

use Sylius\Bundle\OrderBundle\Controller\AddToCartCommandInterface;

interface AddToCartCommandValidatorInterface
{
    public function isValid(AddToCartCommandInterface $command): bool;
}

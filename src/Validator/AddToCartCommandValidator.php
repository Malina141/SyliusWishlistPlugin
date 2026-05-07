<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Validator;

use Sylius\Bundle\OrderBundle\Controller\AddToCartCommandInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class AddToCartCommandValidator implements AddToCartCommandValidatorInterface
{
    /**
     * @param string[] $validationGroups
     */
    public function __construct(
        private ValidatorInterface $validator,
        private array $validationGroups,
    ) {
    }

    public function isValid(AddToCartCommandInterface $command): bool
    {
        $violations = $this->validator->validate($command, null, $this->validationGroups);

        return 0 === \count($violations);
    }
}

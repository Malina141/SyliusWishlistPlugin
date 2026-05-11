<?php

declare(strict_types=1);

namespace Malina141\SyliusWishlistPlugin\Form\Type;

use Malina141\SyliusWishlistPlugin\Entity\WishlistInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WishlistNameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'sylius_wishlist.ui.name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WishlistInterface::class,
            'validation_groups' => ['sylius'],
        ]);
    }
}

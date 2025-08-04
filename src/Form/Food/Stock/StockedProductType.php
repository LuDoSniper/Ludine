<?php

namespace App\Form\Food\Stock;

use App\Entity\Food\Stock\Container;
use App\Entity\Food\Stock\Product;
use App\Entity\Food\Stock\StockedProduct;
use DateTimeInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockedProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('arrivalDate', TextType::class, [
                'label' => 'Date d\'arrivée',
                'attr' => [
                    'data-widget' => 'date',
                ]
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'Date de péremption',
                'attr' => [
                    'data-widget' => 'date',
                ]
            ])
            ->add('stackable', CheckboxType::class, [
                'label' => 'Stackable',
            ])
            ->add('cool', CheckboxType::class, [
                'label' => 'Frais',
            ])
            ->add('floor', TextType::class, [
                'label' => 'Étage',
            ])
            ->add('location', TextType::class, [
                'label' => 'Emplacement',
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'label' => 'Produit',
                'attr' => [
                    'class' => 'field',
                    'data-widget' => 'relational',
                    'data-placeholder' => ' '
                ],
                'choice_attr' => function(Product $product) {
                    return [
                        'data-id' => $product->getId(),
                        'data-url' => '/food/stock/products/get/' . $product->getId(),
                        'data-external_id' => 'food_stock_product',
                    ];
                }
            ])
            ->add('container', EntityType::class, [
                'class' => Container::class,
                'choice_label' => 'name',
                'label' => 'Conteneur',
                'attr' => [
                    'class' => 'field',
                    'data-widget' => 'relational',
                    'data-placeholder' => ' '
                ],
                'choice_attr' => function(Container $container) {
                    return [
                        'data-id' => $container->getId(),
                        'data-url' => '/food/stock/containers/get/' . $container->getId(),
                        'data-external_id' => 'food_stock_container',
                    ];
                }
            ])
        ;

        $builder->get('arrivalDate')->addModelTransformer(new CallbackTransformer(
            function (?DateTimeInterface $date): ?string {
                return $date?->format('d/m/Y');
            },
            function (?string $string): ?\DateTimeInterface {
                return $string ? \DateTime::createFromFormat('d/m/Y', $string) : null;
            }
        ));
        $builder->get('expirationDate')->addModelTransformer(new CallbackTransformer(
            function (?DateTimeInterface $date): ?string {
                return $date?->format('d/m/Y');
            },
            function (?string $string): ?\DateTimeInterface {
                return $string ? \DateTime::createFromFormat('d/m/Y', $string) : null;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StockedProduct::class,
        ]);
    }
}

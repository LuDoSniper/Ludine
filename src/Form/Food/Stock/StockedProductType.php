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
        $user = $options['user'];
        $products = $options['products'];
        $containers = $options['containers'];

        $builder
            ->add('arrivalDate', TextType::class, [
                'label' => 'Date d\'arrivée',
                'attr' => [
                    'data-widget' => 'date',
                ],
                'required' => true,
            ])
            ->add('expirationDate', TextType::class, [
                'label' => 'Date de péremption',
                'attr' => [
                    'data-widget' => 'date',
                ],
                'required' => true,
            ])
            ->add('stackable', CheckboxType::class, [
                'label' => 'Stackable',
                'required' => false,
            ])
            ->add('cool', CheckboxType::class, [
                'label' => 'Frais',
                'required' => false,
            ])
            ->add('floor', TextType::class, [
                'label' => 'Étage',
                'required' => true,
            ])
            ->add('location', TextType::class, [
                'label' => 'Emplacement',
                'required' => true,
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
                        'data-url' => '/food/stock/product/get/' . $product->getId(),
                        'data-external_id' => 'food_stock_product',
                    ];
                },
                'choices' => $products,
                'required' => true,
//                'query_builder' => function (ProductRepository $pr) use ($user) {
//                    $qb = $pr->createQueryBuilder('p')
//                        ->orderBy('p.name', 'ASC');
//
//                    if ($user) {
//                        $qb->where('p.owner = :user')
//                            ->setParameter('user', $user);
//                    } else {
//                        $qb->where('1 = 0');
//                    }
//
//                    return $qb;
//                }
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
                        'data-url' => '/food/stock/container/get/' . $container->getId(),
                        'data-external_id' => 'food_stock_container',
                    ];
                },
                'choices' => $containers,
                'required' => true,
//                'query_builder' => function (ContainerRepository $cr) use ($user) {
//                    $qb = $cr->createQueryBuilder('c')
//                        ->orderBy('c.name', 'ASC');
//
//                    if ($user) {
//                        $qb->where('c.owner = :user')
//                            ->setParameter('user', $user);
//                    } else {
//                        $qb->where('1 = 0');
//                    }
//
//                    return $qb;
//                }
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
            'user' => null,
            'products' => [],
            'containers' => [],
        ]);
    }
}

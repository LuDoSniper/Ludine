<?php

namespace App\Form\Food\Meal;

use App\Entity\Food\Meal\Dish;
use App\Entity\Food\Meal\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DishType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];
        $tags = $options['tags'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'label_attr' => [
                    'class' => 'h1',
                ],
                'required' => true,
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('instructions', TextAreaType::class, [
                'label' => 'Instructions',
                'required' => false,
            ])
            ->add('preparationTime', IntegerType::class, [
                'label' => "Temps de préparation",
                'required' => false,
            ])
            ->add('cookingTime', IntegerType::class, [
                'label' => "Temps de cuisson",
                'required' => false,
            ])
            ->add('difficulty', IntegerType::class, [
                'label' => "Difficulté",
                'attr' => [
                    'data-widget' => 'star',
                    'max' => $options['maxDifficulty']
                ],
                'required' => true,
            ])
            ->add('tags', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'label' => 'Tags',
                'attr' => [
                    'class' => 'field',
                    'data-widget' => 'relational',
                    'data-placeholder' => ' '
                ],
                'choice_attr' => function (Tag $tag) {
                    return [
                        'data-id' => $tag->getId(),
                        'data-url' => '/food/meal/tag/get/' . $tag->getId(),
                        'data-external_id' => 'food_meal_tags',
                        'data-color' => $tag->getColor(),
                    ];
                },
                'choices' => $tags,
                'required' => false,
//                'query_builder' => function (TagRepository $tr) use ($user) {
//                    $qb = $tr->createQueryBuilder('t')
//                        ->orderBy('t.name', 'ASC');
//
//                    if ($user) {
//                        $qb->where('t.owner = :user')
//                            ->setParameter('user', $user);
//                    } else {
//                        $qb->where('1 = 0');
//                    }
//
//                    return $qb;
//                }
            ])
            ->add('dropRate', IntegerType::class, [
                'label' => 'Drop rate',
                'attr' => [
                    'step' => '0.01',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dish::class,
            'maxDifficulty' => 3,
            'user' => null,
            'tags' => [],
        ]);
    }
}

<?php

namespace App\Form\Food\Meal;

use App\Entity\Food\Meal\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('selectionMode', ChoiceType::class, [
                'label' => 'Mode de séléction',
                'attr' => [
                    'class' => 'field',
                    'data-widget' => 'relational',
                    'data-placeholder' => ' '
                ],
                'choices' => [
                    'Choix aléatoire' => 1,
                    'Choix aléatoire (uniquement les ingrédients disponibles)' => 2,
                    'Les deux' => 3
                ]
            ])
            ->add('selectLunch', CheckboxType::class, [
                'label' => 'Midi',
            ])
            ->add('selectDiner', CheckboxType::class, [
                'label' => 'Soir',
            ])
            ->add('lunchTime', null, [
                'label' => 'à',
                'widget' => 'single_text',
                'attr' => [
                    'data-widget' => 'time',
                ]
            ])
            ->add('dinerTime', null, [
                'label' => 'à',
                'widget' => 'single_text',
                'attr' => [
                    'data-widget' => 'time',
                ]
            ])
            ->add('maxDifficulty', IntegerType::class, [
                'label' => 'Difficulté maximum',
                'attr' => [
                    'max' => 10,
                    'min' => 1,
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}

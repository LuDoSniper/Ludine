<?php

namespace App\Form\Food\Stock;

use App\Entity\Food\Stock\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContainerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
            ->add('cool', CheckboxType::class, [
                'label' => 'Frais',
                'required' => false,
            ])
            ->add('nbFloor', IntegerType::class, [
                'label' => 'Nombre d\'étage',
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                    'value' => 0
                ],
                'required' => true,
            ])
            ->add('ref', TextType::class, [
                'label' => 'REF',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Container::class,
        ]);
    }
}

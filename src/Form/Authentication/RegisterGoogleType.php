<?php

namespace App\Form\Authentication;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterGoogleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Nom d\'utilisateur'
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => [
                    'attr' => [
                        'placeholder' => 'Mot de passe'
                    ]
                ],
                'second_options' => [
                    'attr' => [
                        'placeholder' => 'Répéter le mot de passe'
                    ]
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}

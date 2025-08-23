<?php

namespace App\Form\Messenger;

use App\Entity\Authentication\User;
use App\Entity\Messenger\Chat;
use App\Repository\Authentication\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'label_attr' => [
                    'class' => 'h1',
                ],
                'required' => false,
            ])
            ->add('members', EntityType::class, [
                'label' => 'Membres',
                'attr' => [
                    'data-widget' => 'relational'
                ],
                'class' => User::class,
                'choice_label' => fn (User $u) => $u->getDisplayName() ?: $u->getUsername(),
                'query_builder' => function (UserRepository $er) use ($user) {
                    $qb = $er->createQueryBuilder('u')
                        ->orderBy('u.id', 'ASC');

                    if ($user) {
                        $qb->where('u.id != :id')
                            ->setParameter('id', $user->getId());
                    } else {
                        $qb->where('1 = 0');
                    }

                    return $qb;
                },
                'multiple' => true,
                'expanded' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chat::class,
            'user' => null,
        ]);
    }
}

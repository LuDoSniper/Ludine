<?php

namespace App\Form\Settings\General;

use App\Entity\Authentication\User;
use App\Entity\Settings\General\Share;
use App\Service\EntityService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShareType extends AbstractType
{
    public function __construct(
        private readonly EntityService $entityService
    ){}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $entities = $options['entities'];
        $selectedEntities = $options['selectedEntities'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'label_attr' => [
                    'class' => 'h1',
                ],
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
            ->add('active', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
            ])
            ->add('members', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    if (!$user) return '';
                    $dn = trim((string) $user->getDisplayName());
                    return !in_array($dn, ['', null]) ? $dn : $user->getUsername();
                },
                'multiple' => true,
                'attr' => [
                    'data-widget' => 'relational'
                ]
            ])
            ->add('entities', ChoiceType::class, [
                'label' => 'Entités',
                'choices' => array_column(
                    array_map(fn($e) => [
                        'k' => ucfirst(strtolower($e['module'])) . ' - ' . $this->entityService->getEntityName($e),
                        'v' => $e['internal_id']
                    ],
                    array_filter($entities, fn($e) => strtolower($e['module'] ?? '') !== 'settings')),
                    'v',
                    'k'
                ),
                'data' => $selectedEntities,
                'attr' => [
                    'data-widget' => 'relational'
                ],
                'multiple' => true,
                'expanded' => false,
                'mapped' => false,
                'required' => true,
            ])
        ;
    }

    public function formatEntities($entities): array
    {
        $formatedEntities = [];
        foreach (array_values($entities) as $entity) {
            $formatedEntities[ucfirst(strtolower($entity['module'])) . ' - ' . $this->entityService->getEntityName($entity)] = $entity['internal_id'];
        }

        return $formatedEntities;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Share::class,
            'entities' => [],
            'selectedEntities' => []
        ]);
    }
}

<?php

namespace App\Service;

use App\Entity\Authentication\User;
use App\Entity\Food\Meal\Config;
use App\Entity\Food\Meal\Dashboard;
use App\Entity\Food\Meal\Dish;
use App\Entity\Food\Meal\Ingredient;
use App\Entity\Food\Meal\Tag;
use App\Entity\Food\Stock\Container;
use App\Entity\Food\Stock\Product;
use App\Entity\Food\Stock\StockedProduct;
use App\Entity\Settings\General\Share;
use Doctrine\ORM\EntityManagerInterface;

class EntityService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ){}

    public function getEntities(): array
    {
        return [
            'product' => [
                'class' => Product::class,
                'save_path' => '/food/stock/products/save',
                'get_path' => '/food/stock/products/get',
                'get_meta_path' => '/food/stock/products/get_meta',
                'module' => 'food',
                'internal_id' => 0
            ],
            'container' => [
                'class' => Container::class,
                'save_path' => '/food/stock/containers/save',
                'get_path' => '/food/stock/containers/get',
                'get_meta_path' => '/food/stock/containers/get_meta',
                'module' => 'food',
                'internal_id' => 1
            ],
            'stocked_product' => [
                'class' => StockedProduct::class,
                'save_path' => '/food/stock/stocked-products/save',
                'get_path' => '/food/stock/stocked-products/get',
                'get_meta_path' => '/food/stock/stocked-products/get_meta',
                'module' => 'food',
                'internal_id' => 2
            ],
            'config' => [
                'class' => Config::class,
                'save_path' => '/food/meal/config/save',
                'get_path' => '/food/meal/config/get',
                'get_meta_path' => '/food/meal/config/get_meta',
                'module' => 'food',
                'internal_id' => 3
            ],
            'dashboard' => [
                'class' => Dashboard::class,
                'save_path' => '/food/meal/dashboard/save',
                'get_path' => '/food/meal/dashboard/get',
                'get_meta_path' => '/food/meal/dashboard/get_meta',
                'module' => 'food',
                'internal_id' => 4
            ],
            'dish' => [
                'class' => Dish::class,
                'save_path' => '/food/meal/dishes/save',
                'get_path' => '/food/meal/dishes/get',
                'get_meta_path' => '/food/meal/dishes/get_meta',
                'module' => 'food',
                'internal_id' => 5
            ],
            'ingredient' => [
                'class' => Ingredient::class,
                'save_path' => '/food/meal/ingredients/save',
                'get_path' => '/food/meal/ingredients/get',
                'get_meta_path' => '/food/meal/ingredients/get_meta',
                'module' => 'food',
                'internal_id' => 6
            ],
            'tag' => [
                'class' => Tag::class,
                'save_path' => '/food/meal/tags/save',
                'get_path' => '/food/meal/tags/get',
                'get_meta_path' => '/food/meal/tags/get_meta',
                'module' => 'food',
                'internal_id' => 7
            ],
            'general' => [
                'class' => User::class,
                'save_path' => '/settings/profile/general/save',
                'get_path' => '/settings/profile/general/get',
                'get_meta_path' => '/settings/profile/general/get_meta',
                'module' => 'settings',
                'internal_id' => 8
            ],
            'security' => [
                'class' => User::class,
                'save_path' => '/settings/profile/security/save',
                'get_path' => '/settings/profile/security/get',
                'get_meta_path' => '/settings/profile/security/get_meta',
                'module' => 'settings',
                'internal_id' => 9
            ],
            'share' => [
                'class' => Share::class,
                'save_path' => '/settings/general/shares/save',
                'get_path' => '/settings/general/shares/get',
                'get_meta_path' => '/settings/general/shares/get_meta',
                'module' => 'settings',
                'internal_id' => 10
            ]
        ];
    }

    public function getEntityById(int $id): ?array
    {
         return current(array_filter($this->getEntities(), fn($e) => ($e['internal_id'] ?? null) === $id)) ?: null;
    }

    public function getEntityName(array $entity): string
    {
        return (new \ReflectionClass($entity['class']))->getShortName();
    }

    /**
     * @param class-string $class
     */
    public function getClassName(string $class): string
    {
        return (new \ReflectionClass($class))->getShortName();
    }

    public function getEntityByClassName(string $className): ?array
    {
        return current(array_filter($this->getEntities(), fn($e) => ($this->getEntityName($e) ?? null) === $className)) ?: null;
    }

    public function array_unique(array $array): array
    {
        $byId = [];
        foreach ($array as $p) {
            $byId[$p->getId()] = $p;   // la dernière occurrence gagne
        }

        return array_values($byId);
    }

    /**
     * @param class-string $class
     */
    public function getEntityRecords(User $owner, string $class): array
    {
        $entities = $this->entityManager->getRepository($class)->findBy(['owner' => $owner]);
        $shares = $this->entityManager->getRepository(Share::class)
            ->createQueryBuilder('s')
            ->where(':user MEMBER OF s.members')
            ->andWhere('s.active = :active')
            ->setParameter('user', $owner)
            ->setParameter('active', true)
            ->getQuery()
            ->getResult()
        ;

        $sharedEntities = [];
        foreach ($shares as $share) {
            foreach ($share->getEntities() as $entity) {
                if ($this->getEntityName($this->getEntityById($entity)) === $this->getClassName($class)) {
                    $sharedEntities = array_merge($sharedEntities, $this->entityManager->getRepository($class)->findBy(['owner' => array_map(fn($m) => $m->getId(), $share->getMembers()->toArray())]));
                }
            }
        }

        return $this->array_unique(array_merge($entities, $sharedEntities));
    }
}
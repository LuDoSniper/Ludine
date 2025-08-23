<?php

namespace App\Entity\Authentication;

use App\Entity\Food\Meal\Config;
use App\Entity\Food\Meal\Dashboard;
use App\Entity\Food\Meal\Dish;
use App\Entity\Food\Meal\Ingredient;
use App\Entity\Food\Meal\Tag;
use App\Entity\Food\Stock\Container;
use App\Entity\Food\Stock\Product;
use App\Entity\Food\Stock\StockedProduct;
use App\Entity\Messenger\Chat;
use App\Entity\Messenger\Message;
use App\Entity\Settings\General\Share;
use App\Repository\Authentication\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username', 'email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $email = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(nullable: true)]
    private ?string $passwordToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $passwordTokenExpiration = null;

    /**
     * @var Collection<int, Share>
     */
    #[ORM\OneToMany(targetEntity: Share::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $ownedShares;

    /**
     * @var Collection<int, Share>
     */
    #[ORM\ManyToMany(targetEntity: Share::class, mappedBy: 'members')]
    private Collection $memberShares;

    /**
     * @var Collection<int, Container>
     */
    #[ORM\OneToMany(targetEntity: Container::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $containers;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $products;

    /**
     * @var Collection<int, StockedProduct>
     */
    #[ORM\OneToMany(targetEntity: StockedProduct::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $stockedProducts;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\OneToMany(targetEntity: Tag::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $tags;

    #[ORM\OneToOne(mappedBy: 'owner', cascade: ['persist', 'remove'])]
    private ?Config $config = null;

    /**
     * @var Collection<int, Dish>
     */
    #[ORM\OneToMany(targetEntity: Dish::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $dishes;

    /**
     * @var Collection<int, Ingredient>
     */
    #[ORM\OneToMany(targetEntity: Ingredient::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $ingredients;

    #[ORM\OneToOne(mappedBy: 'owner', cascade: ['persist', 'remove'])]
    private ?Dashboard $dashboard = null;

    /**
     * @var Collection<int, Chat>
     */
    #[ORM\OneToMany(targetEntity: Chat::class, mappedBy: 'owner', orphanRemoval: true)]
    private Collection $chats;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'author', orphanRemoval: true)]
    private Collection $messages;

    public function __construct()
    {
        $this->ownedShares = new ArrayCollection();
        $this->memberShares = new ArrayCollection();
        $this->containers = new ArrayCollection();
        $this->products = new ArrayCollection();
        $this->stockedProducts = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->dishes = new ArrayCollection();
        $this->ingredients = new ArrayCollection();
        $this->chats = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    // Getter - Setter

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }
    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }
    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
    public function addRole(string $role): static
    {
        if (!in_array($role, $this->getRoles())) {
            $this->roles[] = $role;
        }
        return $this;
    }
    public function removeRole(string $role): static
    {
        if (in_array($role, $this->getRoles())) {
            $this->roles[] = $role;
        }
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }
    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }
    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;
        return $this;
    }

    public function getPasswordToken(): ?string
    {
        return $this->passwordToken;
    }
    public function setPasswordToken(?string $passwordToken): static
    {
        $this->passwordToken = $passwordToken;
        return $this;
    }

    public function getPasswordTokenExpiration(): ?\DateTimeImmutable
    {
        return $this->passwordTokenExpiration;
    }
    public function setPasswordTokenExpiration(?\DateTimeImmutable $passwordTokenExpiration): static
    {
        $this->passwordTokenExpiration = $passwordTokenExpiration;
        return $this;
    }

    // Methods

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Share>
     */
    public function getOwnedShares(): Collection
    {
        return $this->ownedShares;
    }

    public function addOwnedShare(Share $ownedShare): static
    {
        if (!$this->ownedShares->contains($ownedShare)) {
            $this->ownedShares->add($ownedShare);
            $ownedShare->setOwner($this);
        }

        return $this;
    }

    public function removeOwnedShare(Share $ownedShare): static
    {
        if ($this->ownedShares->removeElement($ownedShare)) {
            // set the owning side to null (unless already changed)
            if ($ownedShare->getOwner() === $this) {
                $ownedShare->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Share>
     */
    public function getMemberShares(): Collection
    {
        return $this->memberShares;
    }

    public function addMemberShare(Share $memberShare): static
    {
        if (!$this->memberShares->contains($memberShare)) {
            $this->memberShares->add($memberShare);
            $memberShare->addMember($this);
        }

        return $this;
    }

    public function removeMemberShare(Share $memberShare): static
    {
        if ($this->memberShares->removeElement($memberShare)) {
            $memberShare->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Container>
     */
    public function getContainers(): Collection
    {
        return $this->containers;
    }

    public function addContainer(Container $container): static
    {
        if (!$this->containers->contains($container)) {
            $this->containers->add($container);
            $container->setOwner($this);
        }

        return $this;
    }

    public function removeContainer(Container $container): static
    {
        if ($this->containers->removeElement($container)) {
            // set the owning side to null (unless already changed)
            if ($container->getOwner() === $this) {
                $container->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setOwner($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getOwner() === $this) {
                $product->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, StockedProduct>
     */
    public function getStockedProducts(): Collection
    {
        return $this->stockedProducts;
    }

    public function addStockedProduct(StockedProduct $stockedProduct): static
    {
        if (!$this->stockedProducts->contains($stockedProduct)) {
            $this->stockedProducts->add($stockedProduct);
            $stockedProduct->setOwner($this);
        }

        return $this;
    }

    public function removeStockedProduct(StockedProduct $stockedProduct): static
    {
        if ($this->stockedProducts->removeElement($stockedProduct)) {
            // set the owning side to null (unless already changed)
            if ($stockedProduct->getOwner() === $this) {
                $stockedProduct->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->setOwner($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            // set the owning side to null (unless already changed)
            if ($tag->getOwner() === $this) {
                $tag->setOwner(null);
            }
        }

        return $this;
    }

    public function getConfig(): ?Config
    {
        return $this->config;
    }

    public function setConfig(Config $config): static
    {
        // set the owning side of the relation if necessary
        if ($config->getOwner() !== $this) {
            $config->setOwner($this);
        }

        $this->config = $config;

        return $this;
    }

    /**
     * @return Collection<int, Dish>
     */
    public function getDishes(): Collection
    {
        return $this->dishes;
    }

    public function addDish(Dish $dish): static
    {
        if (!$this->dishes->contains($dish)) {
            $this->dishes->add($dish);
            $dish->setOwner($this);
        }

        return $this;
    }

    public function removeDish(Dish $dish): static
    {
        if ($this->dishes->removeElement($dish)) {
            // set the owning side to null (unless already changed)
            if ($dish->getOwner() === $this) {
                $dish->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ingredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(Ingredient $ingredient): static
    {
        if (!$this->ingredients->contains($ingredient)) {
            $this->ingredients->add($ingredient);
            $ingredient->setOwner($this);
        }

        return $this;
    }

    public function removeIngredient(Ingredient $ingredient): static
    {
        if ($this->ingredients->removeElement($ingredient)) {
            // set the owning side to null (unless already changed)
            if ($ingredient->getOwner() === $this) {
                $ingredient->setOwner(null);
            }
        }

        return $this;
    }

    public function getDashboard(): ?Dashboard
    {
        return $this->dashboard;
    }

    public function setDashboard(Dashboard $dashboard): static
    {
        // set the owning side of the relation if necessary
        if ($dashboard->getOwner() !== $this) {
            $dashboard->setOwner($this);
        }

        $this->dashboard = $dashboard;

        return $this;
    }

    /**
     * @return Collection<int, Chat>
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chat $chat): static
    {
        if (!$this->chats->contains($chat)) {
            $this->chats->add($chat);
            $chat->setOwner($this);
        }

        return $this;
    }

    public function removeChat(Chat $chat): static
    {
        if ($this->chats->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getOwner() === $this) {
                $chat->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }
}

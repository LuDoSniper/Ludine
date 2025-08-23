<?php

namespace App\Entity\Messenger;

use App\Entity\Authentication\User;
use App\Repository\Messenger\ChatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChatRepository::class)]
class Chat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'chats')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'chats')]
    private Collection $members;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'chat', orphanRemoval: true)]
    private Collection $messages;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(User $member): static
    {
        $this->members->removeElement($member);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

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
            $message->setChat($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChat() === $this) {
                $message->setChat(null);
            }
        }

        return $this;
    }

    public function isMP(): bool
    {
        return count($this->getMembers()->toArray()) === 1;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getOwnerName(): ?string
    {
        return $this->getOwner() ? $this->getOwner()->getDisplayName() ?: $this->getOwner()->getUsername() : '';
    }

    public function getFirstMemberName(): ?string
    {
        return $this->getMembers()->first() ? $this->getMembers()->first()->getDisplayName() ?: $this->getMembers()->first()->getUsername() : '';
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLastMessageContent(): string
    {
        $message = 'Nouvelle conversation';

        foreach (array_reverse($this->getMessages()->toArray()) as $msg) {
            if ($msg->isActive()) {
                return $msg->getContent();
            }
        }

        return $message;
    }

    public function getLastMessageTime(): string
    {
        $message = '';

        foreach (array_reverse($this->getMessages()->toArray()) as $msg) {
            if ($msg->isActive()) {
                return $msg->getCreatedAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('H:i');
            }
        }

        return $message;
    }
}

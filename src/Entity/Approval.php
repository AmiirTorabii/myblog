<?php

namespace App\Entity;

use App\Repository\ApprovalRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ApprovalRepository::class)]
class Approval
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false)]
    private ?Post $post = null; // Reference to Post

    #[ORM\Column(enumType: PostStatus::class)]
    private ?PostStatus $changedTo = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $approvedAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $by = null; // User who approved the post

    public function __construct(Post $post, User $by, PostStatus $changedTo)
    {
        $this->post = $post;
        $this->by = $by;
        $this->changedTo = $changedTo;
        $this->approvedAt = new \DateTimeImmutable();
    }

 
    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getBy(): ?User
    {
        return $this->by;
    }

    public function setBy(User $by): static
    {
        $this->by = $by;

        return $this;
    }

    public function getChangedTo(): ?PostStatus
    {
        return $this->changedTo;
    }

    public function setChangedTo(PostStatus $changedTo): static
    {
        $this->changedTo = $changedTo;

        return $this;
    }

    public function getApprovedAt(): ?\DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function setApprovedAt(\DateTimeImmutable $approvedAt): static
    {
        $this->approvedAt = $approvedAt;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): static
    {
        // unset the owning side of the relation if necessary
        if ($post === null && $this->post !== null) {
            $this->post->setStatus(null);
        }

        // set the owning side of the relation if necessary
        if ($post !== null && $post->getStatus() !== $this) {
            $post->setStatus($this);
        }

        $this->post = $post;

        return $this;
    }
}

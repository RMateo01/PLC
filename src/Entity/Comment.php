<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotNull()
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     *
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="comments")
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Publication", inversedBy="commentaires")
     */
    private $publication;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Flow", mappedBy="comment", cascade={"remove"})
     */
    private $flows;

    public function __construct()
    {
        $this->flows = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getPublication(): ?Publication
    {
        return $this->publication;
    }

    public function setPublication(?Publication $publication): self
    {
        $this->publication = $publication;

        return $this;
    }

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * @return Collection|Flow[]
     */
    public function getFlows(): Collection
    {
        return $this->flows;
    }

    public function addFlow(Flow $flow): self
    {
        if (!$this->flows->contains($flow)) {
            $this->flows[] = $flow;
            $flow->setComment($this);
        }

        return $this;
    }

    public function removeFlow(Flow $flow): self
    {
        if ($this->flows->contains($flow)) {
            $this->flows->removeElement($flow);
            // set the owning side to null (unless already changed)
            if ($flow->getComment() === $this) {
                $flow->setComment(null);
            }
        }

        return $this;
    }
}

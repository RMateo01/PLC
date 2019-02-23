<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FlowRepository")
 */
class Flow
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="flows")
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Publication", inversedBy="flows")
     */
    private $publication;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Comment", inversedBy="flows")
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="flows")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $artisteId;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $playlistId;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $albumId;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $trackId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

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

    public function getAuteur(): ?User
    {
        return $this->auteur;
    }

    public function setAuteur(?User $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    public function getArtisteId(): ?string
    {
        return $this->artisteId;
    }

    public function setArtisteId(?string $artisteId): self
    {
        $this->artisteId = $artisteId;

        return $this;
    }

    public function getPlaylistId(): ?string
    {
        return $this->playlistId;
    }

    public function setPlaylistId(?string $playlistId): self
    {
        $this->playlistId = $playlistId;

        return $this;
    }

    public function getAlbumId(): ?string
    {
        return $this->albumId;
    }

    public function setAlbumId(?string $albumId): self
    {
        $this->albumId = $albumId;

        return $this;
    }

    public function getTrackId(): ?string
    {
        return $this->trackId;
    }

    public function setTrackId(?string $trackId): self
    {
        $this->trackId = $trackId;

        return $this;
    }
}

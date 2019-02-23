<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PublicationRepository")
 */
class Publication
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="publications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="publicationsLike")
     */
    private $aimer;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="publication", cascade={"remove"})
     */
    private $commentaires;

    /**
     * @ORM\Column(type="text")
     */
    private $contenu;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreation;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Flow", mappedBy="publication", cascade={"remove"})
     */
    private $flows;

    public function __construct()
    {
        $this->aimer = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
        $this->flows = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Collection|User[]
     */
    public function getAimer(): Collection
    {
        return $this->aimer;
    }

    public function addAimer(User $aimer): self
    {
        if (!$this->aimer->contains($aimer)) {
            $this->aimer[] = $aimer;
        }

        return $this;
    }

    public function removeAimer(User $aimer): self
    {
        if ($this->aimer->contains($aimer)) {
            $this->aimer->removeElement($aimer);
        }

        return $this;
    }

    /**
     * @return Collection|comment[]
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(comment $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setPublication($this);
        }

        return $this;
    }

    public function removeCommentaire(comment $commentaire): self
    {
        if ($this->commentaires->contains($commentaire)) {
            $this->commentaires->removeElement($commentaire);
            // set the owning side to null (unless already changed)
            if ($commentaire->getPublication() === $this) {
                $commentaire->setPublication(null);
            }
        }

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

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
            $flow->setPublication($this);
        }

        return $this;
    }

    public function removeFlow(Flow $flow): self
    {
        if ($this->flows->contains($flow)) {
            $this->flows->removeElement($flow);
            // set the owning side to null (unless already changed)
            if ($flow->getPublication() === $this) {
                $flow->setPublication(null);
            }
        }

        return $this;
    }
}

<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     fields = {"email"},
 *     message = "L'email que vous avez indiqué est déjà utilisée !"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=8,minMessage="Votre mot de passe doit faire au moins 8 caractères")
     * @Assert\EqualTo(propertyPath="confirm_password", message="Votre mot de passe doit être similaire à votre mot de passe de confirmation")
     */
    private $password;

    /**
    @Assert\EqualTo(propertyPath="password",message="Votre mot de passe de confirmation doit être similaire à votre mot de passe")
    */
    public $confirm_password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Article", mappedBy="auteur")
     */
    private $article;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Publication", mappedBy="auteur")
     */
    private $contenu;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Publication", mappedBy="auteur")
     */
    private $publications;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Publication", mappedBy="aimer")
     */
    private $publicationsLike;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="auteur")
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Flow", mappedBy="auteur", orphanRemoval=true)
     */
    private $flows;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * Many Users have Many Users.
     * @ORM\ManyToMany(targetEntity="User", mappedBy="myFriends")
     */
    private $friendsWithMe;

    /**
     * Many Users have many Users.
     * @ORM\ManyToMany(targetEntity="User", inversedBy="friendsWithMe")
     * @ORM\JoinTable(name="friends",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="friend_user_id", referencedColumnName="id")}
     *      )
     */
    private $myFriends;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="amis")
     */
    private $amis;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $accessToken;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $refreshToken;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $idSpotify;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $image;




    public function __construct()
    {
        $this->article = new ArrayCollection();
        $this->contenu = new ArrayCollection();
        $this->publications = new ArrayCollection();
        $this->publicationsLike = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->flows = new ArrayCollection();
        $this->amis = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->friendsWithMe = new ArrayCollection();
        $this->myFriends = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getRoles()
    {
        // TODO: Implement getRoles() method.
        return ['ROLE_USER'];
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * @return Collection|Article[]
     */
    public function getArticle(): Collection
    {
        return $this->article;
    }

    public function addArticle(Article $article): self
    {
        if (!$this->article->contains($article)) {
            $this->article[] = $article;
            $article->setAuteur($this);
        }

        return $this;
    }

    public function removeArticle(Article $article): self
    {
        if ($this->article->contains($article)) {
            $this->article->removeElement($article);
            // set the owning side to null (unless already changed)
            if ($article->getAuteur() === $this) {
                $article->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Publication[]
     */
    public function getContenu(): Collection
    {
        return $this->contenu;
    }

    public function addContenu(Publication $contenu): self
    {
        if (!$this->contenu->contains($contenu)) {
            $this->contenu[] = $contenu;
            $contenu->setAuteur($this);
        }

        return $this;
    }

    public function removeContenu(Publication $contenu): self
    {
        if ($this->contenu->contains($contenu)) {
            $this->contenu->removeElement($contenu);
            // set the owning side to null (unless already changed)
            if ($contenu->getAuteur() === $this) {
                $contenu->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Publication[]
     */
    public function getPublications(): Collection
    {
        return $this->publications;
    }

    public function addPublication(Publication $publication): self
    {
        if (!$this->publications->contains($publication)) {
            $this->publications[] = $publication;
            $publication->setAuteur($this);
        }

        return $this;
    }

    public function removePublication(Publication $publication): self
    {
        if ($this->publications->contains($publication)) {
            $this->publications->removeElement($publication);
            // set the owning side to null (unless already changed)
            if ($publication->getAuteur() === $this) {
                $publication->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Publication[]
     */
    public function getPublicationsLike(): Collection
    {
        return $this->publicationsLike;
    }

    public function addPublicationsLike(Publication $publicationsLike): self
    {
        if (!$this->publicationsLike->contains($publicationsLike)) {
            $this->publicationsLike[] = $publicationsLike;
            $publicationsLike->addAimer($this);
        }

        return $this;
    }

    public function removePublicationsLike(Publication $publicationsLike): self
    {
        if ($this->publicationsLike->contains($publicationsLike)) {
            $this->publicationsLike->removeElement($publicationsLike);
            $publicationsLike->removeAimer($this);
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuteur($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAuteur() === $this) {
                $comment->setAuteur(null);
            }
        }

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
            $flow->setAuteur($this);
        }

        return $this;
    }

    public function removeFlow(Flow $flow): self
    {
        if ($this->flows->contains($flow)) {
            $this->flows->removeElement($flow);
            // set the owning side to null (unless already changed)
            if ($flow->getAuteur() === $this) {
                $flow->setAuteur(null);
            }
        }

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }


    public function getFriendsWithMe()
    {
        return $this->friendsWithMe;
    }

    /**
     * @return Collection|User
     */
    public function getFriends(): Collection
    {
        return $this->myFriends;
    }

    public function addFriends(User $myFriends): self
    {
        if (!$this->myFriends->contains($myFriends)) {
            $this->myFriends[] = $myFriends;
        }

        return $this;
    }

    public function removeFriends(User $myFriends): self
    {
        if ($this->myFriends->contains($myFriends)) {
            $this->myFriends->removeElement($myFriends);
            $myFriends->removeFriends($this);
        }

        return $this;
    }


    public function follow(User $user): bool
    {

        $amis = $this->getFriends();

        foreach ($amis as $ami){

            if($ami->getId() == $user->getId()){
                return true;
            }

        }

        return false;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): self
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function getIdSpotify(): ?string
    {
        return $this->idSpotify;
    }

    public function setIdSpotify(?string $idSpotify): self
    {
        $this->idSpotify = $idSpotify;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
}

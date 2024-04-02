<?php

namespace App\Entity;

use App\Repository\AvisRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AvisRepository::class)]
class Avis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ? int $idAvis = null;

    #[Assert\NotBlank(message: "Le commentaire ne peut pas être vide")]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $commentaire = null;

    
    #[Assert\NotBlank(message: "La note ne peut pas être vide")]
    #[Assert\Type(type: 'integer', message: "La note doit etre un chiffre compris entre 0 et 5")]
    #[ORM\Column(type: 'integer', name: 'note')]
    private ?int $note = null;

    #[ORM\Column(type: 'integer', name: 'likes')]
    private ?int $likes = null;

    #[ORM\Column(type: 'integer', name: 'dislikes')]
    private ?int $dislikes = null;

    #[ORM\Column(type: 'boolean', name: 'favoris')]
    private ?bool $favoris = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user")]
    private ?User $user = null;
    

    #[ORM\ManyToOne(targetEntity: Oeuvre::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(name: "id_oeuvre", referencedColumnName: "id_oeuvre" , onDelete:"CASCADE")]

    private ?Oeuvre $oeuvre = null;

    public function getIdAvis(): ?int
    {
        return $this->idAvis;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getNote(): ?int
    {
        return $this->note;
    }

    public function setNote(int $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;

        return $this;
    }

    public function getDislikes(): ?int
    {
        return $this->dislikes;
    }

    public function setDislikes(int $dislikes): static
    {
        $this->dislikes = $dislikes;

        return $this;
    }

    public function isFavoris(): ?bool
    {
        return $this->favoris;
    }

    public function setFavoris(bool $favoris): static
    {
        $this->favoris = $favoris;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getOeuvre(): ?Oeuvre
    {
        return $this->oeuvre;
    }

    public function setOeuvre(?Oeuvre $oeuvre): static
    {
        $this->oeuvre = $oeuvre;

        return $this;
    }
  


}

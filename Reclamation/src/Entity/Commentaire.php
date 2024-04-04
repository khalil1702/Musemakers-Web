<?php

namespace App\Entity;

use App\Entity\Reclamation;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentaireRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
class Commentaire
{
    #[ORM\Id]
    #[ORM\Column(name: "idCom", type: "integer")]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $idcom;

    #[ORM\Column(name: "DateCom", type: "date")]
    private \DateTime $datecom;

    #[ORM\Column(name: "ContenuCom", type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le contenu du commentaire ne peut pas être vide.")]
    #[Assert\Length(max: 200, maxMessage: "Le contenu du commentaire ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $contenucom = null;
    

    #[ORM\ManyToOne(targetEntity: Reclamation::class)]
    #[ORM\JoinColumn(name: "idrec", referencedColumnName: "idRec")]
    private ?Reclamation $idrec=null;

    // Getters
    public function getIdcom(): int
    {
        return $this->idcom;
    }

    public function getDatecom(): \DateTime
    {
        return $this->datecom;
    }

    public function getContenucom(): string
    {
        return $this->contenucom;
    }

    public function getIdrec(): ?Reclamation
    {
        return $this->idrec;
    }

    // Setters
    public function setIdcom(int $idcom): self
    {
        $this->idcom = $idcom;
        return $this;
    }

    public function setDatecom(\DateTime $datecom): self
    {
        $this->datecom = $datecom;
        return $this;
    }

    public function setContenucom(?string $contenucom): self
{
    $this->contenucom = $contenucom;
    return $this;
}

    public function setIdrec(?Reclamation $idrec): self
    {
        $this->idrec = $idrec;
        return $this;
    }
}

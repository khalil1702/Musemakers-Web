<?php

namespace App\Entity;

use App\Repository\CoursRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;
use App\Entity\Atelier;

#[ORM\Entity(repositoryClass: CoursRepository::class)]
class Cours
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id_cours", type: "integer", nullable: false)]
    private int $idCours;

    #[ORM\Column(name: "titre_cours", type: "string", length: 255, nullable: false)]
    private string $titreCours;

    #[ORM\Column(name: "descri_cours", type: "text", length: 65535, nullable: false)]
    private string $descriCours;

    #[ORM\Column(name: "dateDebut_cours", type: "date", nullable: false)]
    private \DateTime $datedebutCours;

    #[ORM\Column(name: "dateFin_cours", type: "date", nullable: false)]
    private \DateTime $datefinCours;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user")]
   
   // #[ORM\ManyToOne( inversedBy: 'cours')]
    //#[ORM\JoinColumn(name: "id_user")]
    private ?User $user = null;
   
    #[ORM\OneToMany(targetEntity: Atelier::class, mappedBy: 'cours')]
    private ?Collection $ateliers = null;

    public function __construct()
    {
        $this->ateliers = new ArrayCollection();
    }

    public function getIdCours(): int
    {
        return $this->idCours;
    }

    public function getTitreCours(): string
    {
        return $this->titreCours;
    }

    public function setTitreCours(string $titreCours): self
    {
        $this->titreCours = $titreCours;

        return $this;
    }

    public function getAteliers(): ?Collection
    {
        return $this->ateliers;
    }

    public function setAteliers(Collection $ateliers): self
    {
        $this->ateliers = $ateliers;

        return $this;
    }

  


    public function getDescriCours(): string
    {
        return $this->descriCours;
    }

    public function setDescriCours(string $descriCours): self
    {
        $this->descriCours = $descriCours;

        return $this;
    }

    public function getDatedebutCours(): \DateTime
    {
        return $this->datedebutCours;
    }

    public function setDatedebutCours(\DateTime $datedebutCours): self
    {
        $this->datedebutCours = $datedebutCours;

        return $this;
    }

    public function getDatefinCours(): \DateTime
    {
        return $this->datefinCours;
    }

    public function setDatefinCours(\DateTime $datefinCours): self
    {
        $this->datefinCours = $datefinCours;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getIdUser(): ?int
    {
        if ($this->user !== null) {
            return $this->user->getIdUser();
        }
        return null;
    }

}
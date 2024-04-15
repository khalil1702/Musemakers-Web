<?php

namespace App\Entity;

use App\Repository\AtelierRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AtelierRepository::class)]

class Atelier
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "id_atelier", type: "integer", nullable: false)]
    private int $idAtelier;

    #[ORM\Column(name: "dateDebut_atelier", type: "date", nullable: false)]
    private \DateTime $datedebutAtelier;

    #[ORM\Column(name: "dateFin_atelier", type: "date", nullable: false)]
    private \DateTime $datefinAtelier;

    #[ORM\Column(name: "lien_atelier", type: "string", length: 255, nullable: false)]
    private string $lienAtelier;
    #[ORM\OneToMany(mappedBy: 'atelier', targetEntity: Cours::class)]
    private Collection $cours;

    public function getIdAtelier(): int
    {
        return $this->idAtelier;
    }

    public function getDatedebutAtelier(): \DateTime
    {
        return $this->datedebutAtelier;
    }

    public function setDatedebutAtelier(\DateTime $datedebutAtelier): self
    {
        $this->datedebutAtelier = $datedebutAtelier;

        return $this;
    }

    public function getDatefinAtelier(): \DateTime
    {
        return $this->datefinAtelier;
    }

    public function setDatefinAtelier(\DateTime $datefinAtelier): self
    {
        $this->datefinAtelier = $datefinAtelier;

        return $this;
    }

    public function getLienAtelier(): string
    {
        return $this->lienAtelier;
    }

    public function setLienAtelier(string $lienAtelier): self
    {
        $this->lienAtelier = $lienAtelier;

        return $this;
    }

     /**
     * @return Collection<int, Vote>
     */
    public function getCours(): Collection
    {
        return $this->cours;
    }
  
}

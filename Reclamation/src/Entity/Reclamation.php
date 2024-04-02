<?php

namespace App\Entity;
use App\Entity\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ReclamationRepository;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\Column(name: "idRec", type: "integer")]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    private int $idrec;

    #[ORM\Column(name: "descriRec", type: "text", length: 200)]
#[Assert\NotBlank(message: "Il faut écrire la description")]
#[Assert\Length(max: 200, maxMessage: "La description ne doit pas dépasser 200 caractères")]
private string $descrirec;

    #[ORM\Column(name: "DateRec", type: "date")]
    private \DateTime $daterec;

    #[ORM\Column(name: "CategorieRec", type: "string", length: 255)]
    #[Assert\NotBlank(message:"Veuillez choisir une catégorie")]
    private string $categorierec;
     
    

    #[ORM\Column(name: "StatutRec", type: "string", length: 30)]
    private string $statutrec;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "idU", referencedColumnName: "id_user")]
    private ?User $idu=null;

    // Getters
    public function getIdrec(): int
    {
        return $this->idrec;
    }

    public function getDescrirec(): string
    {
        return $this->descrirec;
    }

    public function getDaterec(): \DateTime
    {
        return $this->daterec;
    }

    public function getCategorierec(): string
    {
        return $this->categorierec;
    }

    public function getStatutrec(): string
    {
        return $this->statutrec;
    }

    public function getIdu(): ?User
    {
        return $this->idu;
    }

    // Setters
    public function setDescrirec(string $descrirec): self
    {
        $this->descrirec = $descrirec;
        return $this;
    }

    public function setDaterec(\DateTime $daterec): self
    {
        $this->daterec = $daterec;
        return $this;
    }

    public function setCategorierec(string $categorierec): self
    {
        $this->categorierec = $categorierec;
        return $this;
    }

    public function setStatutrec(string $statutrec): self
    {
        $this->statutrec = $statutrec;
        return $this;
    }

    public function setIdu(?User $idu): self
    {
        $this->idu = $idu;
        return $this;
    }
    public function __toString()
{
    return 'ID: ' . $this->idrec ;
}

}
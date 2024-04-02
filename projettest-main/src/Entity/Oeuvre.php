<?php

namespace App\Entity;

use App\Repository\OeuvreRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: OeuvreRepository::class)]
class Oeuvre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idOeuvre = null;

    #[Assert\NotBlank(message: "Le nom de l'oeuvre ne peut pas être vide")]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nomOeuvre = null;

    #[Assert\NotBlank(message: "La categorie de l'oeuvre ne peut pas être vide")]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $categorieOeuvre = null;

#[Assert\NotBlank(message: "Le prix de l'oeuvre ne peut pas être vide")]
#[Assert\Type(type: 'float', message: "Le prix de l'oeuvre doit être un nombre décimal")]
    #[ORM\Column(type: 'float')]
    private ?float $prixOeuvre = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private  ?\DateTimeInterface $datecreation = null;

    #[Assert\NotBlank(message: "La description de l'oeuvre ne peut pas être vide")]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $description = null;
   
    #[Assert\NotBlank(message: "L'image de l'oeuvre ne peut pas être vide")]
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $image = null;

    public function getIdOeuvre(): ?int
    {
        return $this->idOeuvre;
    }

    public function getNomOeuvre(): ?string
    {
        return $this->nomOeuvre;
    }

    public function setNomOeuvre(string $nomOeuvre): static
    {
        $this->nomOeuvre = $nomOeuvre;

        return $this;
    }

    public function getCategorieOeuvre(): ?string
    {
        return $this->categorieOeuvre;
    }

    public function setCategorieOeuvre(string $categorieOeuvre): static
    {
        $this->categorieOeuvre = $categorieOeuvre;

        return $this;
    }

    public function getPrixOeuvre(): ?float
    {
        return $this->prixOeuvre;
    }

    public function setPrixOeuvre(float $prixOeuvre): static
    {
        $this->prixOeuvre = $prixOeuvre;

        return $this;
    }

    public function getDatecreation(): ?\DateTimeInterface
    {
        return $this->datecreation;
    }

    public function setDatecreation(\DateTimeInterface $datecreation): static
    {
        $this->datecreation = $datecreation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }
  
   

}

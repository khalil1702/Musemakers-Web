<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Exposition;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

use App\Repository\ReservationRepository;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idReservation = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id_user")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Exposition::class, inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: "id_exposition", referencedColumnName: "id_exposition")]

    private ?Exposition $exposition = null;

    #[ORM\Column(type: 'integer')]
    private ?int $ticketsNumber = null;

    #[ORM\Column(type: 'integer', name: 'accessByAdmin')]
    private ?int $accessByAdmin = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateReser = null;

    public function getIdReservation(): ?int
    {
        return $this->idReservation;
    }

    public function getDateReser(): ?\DateTimeInterface
    {
        return $this->dateReser;
    }

    public function setDateReser(\DateTimeInterface $date): static
    {
        $this->dateReser = $date;
        return $this;
    }

    public function getTicketsNumber(): ?int
    {
        return $this->ticketsNumber;
    }

    public function setTicketsNumber(?int $ticketsNumber): static
    {
        $this->ticketsNumber = $ticketsNumber;
        return $this;
    }

    public function getAccessByAdmin(): ?int
    {
        return $this->accessByAdmin;
    }

    public function setAccessByAdmin(?int $accessByAdmin): static
    {
        $this->accessByAdmin = $accessByAdmin;
        return $this;
    }

    public function getExposition(): ?Exposition
    {
        return $this->exposition;
    }

    public function setExposition(?Exposition $exposition): static
    {
        $this->exposition = $exposition;
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

    public function __toString(): string
    {
        return (string) $this->idReservation;
        
    }
}
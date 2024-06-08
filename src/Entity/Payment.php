<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ApiResource]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(name: 'telegram_id', referencedColumnName: 'telegram_id', nullable: false)]
    private ?User $user_id = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?bool $is_discount = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(name: 'tariff_id', nullable: false)]
    private ?Tariff $tariff_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function isDiscount(): ?bool
    {
        return $this->is_discount;
    }

    public function setDiscount(bool $is_discount): static
    {
        $this->is_discount = $is_discount;
        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\SocialNetworkRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: SocialNetworkRepository::class)]
class SocialNetwork
{
#[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['social_network:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['social_network:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['social_network:read'])]
    private ?string $icon = null;

    #[ORM\Column(length: 255)]
    #[Groups(['social_network:read'])]
    private ?string $url = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }
}

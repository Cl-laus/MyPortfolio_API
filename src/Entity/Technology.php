<?php
namespace App\Entity;

use App\Repository\TechnologyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TechnologyRepository::class)]
class Technology
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    #[Groups(['technology:read', 'project:read'])]
    private ?int $id = null;
    #[Groups(['technology:read', 'technology:write', 'project:read'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;
    #[Groups(['technology:read', 'technology:write', 'project:read'])]
    #[ORM\Column(length: 255)]
    private ?string $icon = null;
    #[Groups(['technology:read', 'technology:write', 'project:read'])]
    #[ORM\Column(length: 50)]
    private ?string $category = null;



    
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

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\InformationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: InformationRepository::class)]
class Information
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['information:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['information:read'])]
    private ?string $fullName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['information:read'])]
    private ?string $jobTitle = null;

    #[ORM\Column(length: 255)]
    #[Groups(['information:read'])]
    private ?string $tagLine = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['information:read'])]
    private ?string $introText = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['information:read'])]
    private ?string $photoPath = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['information:read'])]
    private ?string $aboutTitle = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['information:read'])]
    private ?string $aboutText = null;

    #[ORM\Column(length: 255)]
    #[Groups(['information:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['information:read'])]
    private ?string $cv = null;

    public function getId(): ?int { return $this->id; }

    public function getFullName(): ?string { return $this->fullName; }
    public function setFullName(string $fullName): static { $this->fullName = $fullName; return $this; }

    public function getJobTitle(): ?string { return $this->jobTitle; }
    public function setJobTitle(string $jobTitle): static { $this->jobTitle = $jobTitle; return $this; }

    public function getTagLine(): ?string { return $this->tagLine; }
    public function setTagLine(string $tagLine): static { $this->tagLine = $tagLine; return $this; }

    public function getIntroText(): ?string { return $this->introText; }
    public function setIntroText(string $introText): static { $this->introText = $introText; return $this; }

    public function getPhotoPath(): ?string { return $this->photoPath; }
    public function setPhotoPath(?string $photoPath): static { $this->photoPath = $photoPath; return $this; }

    public function getAboutTitle(): ?string { return $this->aboutTitle; }
    public function setAboutTitle(?string $aboutTitle): static { $this->aboutTitle = $aboutTitle; return $this; }

    public function getAboutText(): ?string { return $this->aboutText; }
    public function setAboutText(string $aboutText): static { $this->aboutText = $aboutText; return $this; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }

    public function getCv(): ?string { return $this->cv; }
    public function setCv(?string $cv): static { $this->cv = $cv; return $this; }
}
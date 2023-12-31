<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $idVille = null;

    #[Assert\Regex('/^[a-zA-Z-]+$/')]
    #[Assert\NotBlank(message: 'Le nom du campus doit obligatoirement être renseigné.')]
    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[Assert\Regex('/^\d{1}[1-9]{1}\d{3}$/')]
    #[ORM\Column(length: 5)]
    private ?string $codePostal = null;

    //TODO: ajout champs lieux de type array
    #[ORM\OneToMany(mappedBy: 'ville', targetEntity: Lieu::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $lieux;

    public function getId(): ?int
    {
        return $this->idVille;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getLieux(): Collection
    {
        return $this->lieux;
    }

    /**
     * @param Collection $lieux
     */
    public function setLieux(Collection $lieux): void
    {
        $this->lieux = $lieux;
    }
}

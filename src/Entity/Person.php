<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $post;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;



    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $posten;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionen;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $postru;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionru;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $position;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getPost(): ?string
    {
        return $this->post;
    }

    public function setPost(?string $post): self
    {
        $this->post = $post;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }


    public function getPostEn(): ?string
    {
        return $this->posten;
    }

    public function setPostEn(?string $posten): self
    {
        $this->posten = $posten;

        return $this;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionen;
    }

    public function setDescriptionEn(?string $descriptionen): self
    {
        $this->descriptionen = $descriptionen;

        return $this;
    }

    public function getPostRu(): ?string
    {
        return $this->postru;
    }

    public function setPostRu(?string $postru): self
    {
        $this->postru = $postru;

        return $this;
    }

    public function getDescriptionRu(): ?string
    {
        return $this->descriptionru;
    }

    public function setDescriptionRu(?string $descriptionru): self
    {
        $this->descriptionru = $descriptionru;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }
}

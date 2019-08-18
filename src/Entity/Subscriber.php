<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriberRepository")
 */
class Subscriber
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email_address;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $confirmed;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $start_time;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_member;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $has_subscribed;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $unid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->email_address;
    }

    public function setEmailAddress(string $email_address): self
    {
        $this->email_address = $email_address;

        return $this;
    }

    public function getConfirmed(): ?int
    {
        return $this->confirmed;
    }

    public function setConfirmed(?int $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->start_time;
    }

    public function setStartTime(?\DateTimeInterface $start_time): self
    {
        $this->start_time = $start_time;

        return $this;
    }

    public function getIsMember(): ?bool
    {
        return $this->is_member;
    }

    public function setIsMember(?bool $is_member): self
    {
        $this->is_member = $is_member;

        return $this;
    }

    public function getHasSubscribed(): ?bool
    {
        return $this->has_subscribed;
    }

    public function setHasSubscribed(?bool $has_subscribed): self
    {
        $this->has_subscribed = $has_subscribed;

        return $this;
    }

    public function getUnid(): ?string
    {
        return $this->unid;
    }

    public function setUnid(string $unid): self
    {
        $this->unid = $unid;

        return $this;
    }
}

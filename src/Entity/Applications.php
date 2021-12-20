<?php

namespace App\Entity;

use App\Repository\ApplicationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ApplicationsRepository::class)
 */
class Applications
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
     * @ORM\Column(type="string", length=255)
     */
    private $currentVersion;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $latestVersion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $githubRepository;

    /**
     * @ORM\OneToMany(targetEntity=Changelog::class, mappedBy="application", cascade={"remove"})
     */
    private $changelogs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $api_name;

    public function __construct()
    {
        $this->changelogs = new ArrayCollection();
    }

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

    public function getCurrentVersion(): ?string
    {
        return $this->currentVersion;
    }

    public function setCurrentVersion(string $currentVersion): self
    {
        $this->currentVersion = $currentVersion;

        return $this;
    }

    public function getLatestVersion(): ?string
    {
        return $this->latestVersion;
    }

    public function setLatestVersion(string $latestVersion): self
    {
        $this->latestVersion = $latestVersion;

        return $this;
    }

    public function getGithubRepository(): ?string
    {
        return $this->githubRepository;
    }

    public function setGithubRepository(?string $githubRepository): self
    {
        $this->githubRepository = $githubRepository;

        return $this;
    }

    /**
     * @return Collection|Changelog[]
     */
    public function getChangelogs(): Collection
    {
        return $this->changelogs;
    }

    public function addChangelog(Changelog $changelog): self
    {
        if (!$this->changelogs->contains($changelog)) {
            $this->changelogs[] = $changelog;
            $changelog->setApplication($this);
        }

        return $this;
    }

    public function removeChangelog(Changelog $changelog): self
    {
        if ($this->changelogs->contains($changelog)) {
            $this->changelogs->removeElement($changelog);
            // set the owning side to null (unless already changed)
            if ($changelog->getApplication() === $this) {
                $changelog->setApplication(null);
            }
        }

        return $this;
    }

    public function __toString() {
        return $this->name;
    }

    public function getApiName(): ?string
    {
        return $this->api_name;
    }

    public function setApiName(?string $api_name): self
    {
        $this->api_name = $api_name;

        return $this;
    }
}

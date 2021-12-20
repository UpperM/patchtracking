<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity (
 * fields={"email"},
 * message="register.email.already_used"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message = "register.email.not_valid")
     */
    private $email;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(min=8, max=255, minMessage="register.password.not_valid.min_length")
     * @Assert\EqualTo(propertyPath="confirm_password")
     */
    private $password;


    /**
     * @Assert\EqualTo(propertyPath="password", message="register.password.not_match")
     */
    public $confirm_password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="register.fullname.not_valid")
     */
    private $full_name;

    /**
     * @ORM\OneToMany(targetEntity=Changelog::class, mappedBy="update_by")
     */
    private $changelogs;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ldapAuth;

    /**
     * @ORM\Column(type="integer", length=255, nullable=true)
     */
    private $glpiId;

    public function __construct()
    {
        $this->changelogs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): self
    {
        $this->full_name = $full_name;

        return $this;
    }

    public function eraseCredentials()
    {

    }

    public function getSalt()
    {

    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getUsername()
    {
        return $this->email;
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
            $changelog->setUpdateBy($this);
        }

        return $this;
    }

    public function removeChangelog(Changelog $changelog): self
    {
        if ($this->changelogs->contains($changelog)) {
            $this->changelogs->removeElement($changelog);
            // set the owning side to null (unless already changed)
            if ($changelog->getUpdateBy() === $this) {
                $changelog->setUpdateBy(null);
            }
        }

        return $this;
    }

    public function __toString() {
        return $this->name;
    }

    public function getLdapAuth(): ?string
    {
        return $this->ldapAuth;
    }

    public function setLdapAuth(?string $ldapAuth): self
    {
        $this->ldapAuth = $ldapAuth;

        return $this;
    }

    public function getGlpiId(): ?int
    {
        return $this->glpiId;
    }

    public function setGlpiId(?int $glpiId): self
    {
        $this->glpiId = $glpiId;

        return $this;
    }

}

<?php

namespace App\Entity;

use App\Repository\ChangelogRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ChangelogRepository::class)
 */
class Changelog
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
    private $updateVersionFrom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $updateVersionTo;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updateDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateScheduleAt;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;


    /**
     * @ORM\ManyToOne(targetEntity=Applications::class, inversedBy="changelogs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $application;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="changelogs")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $update_by;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $glpi_ticket_id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $reportFilename;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdateVersionFrom(): ?string
    {
        return $this->updateVersionFrom;
    }

    public function setUpdateVersionFrom(string $updateVersionFrom): self
    {
        $this->updateVersionFrom = $updateVersionFrom;

        return $this;
    }

    public function getUpdateVersionTo(): ?string
    {
        return $this->updateVersionTo;
    }

    public function setUpdateVersionTo(string $updateVersionTo): self
    {
        $this->updateVersionTo = $updateVersionTo;

        return $this;
    }

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTimeInterface $updateDate): self
    {
        $this->updateDate = $updateDate;

        return $this;
    }

    public function getUpdateScheduleAt(): ?\DateTimeInterface
    {
        return $this->updateScheduleAt;
    }

    public function setUpdateScheduleAt(\DateTimeInterface $updateScheduleAt): self
    {
        $this->updateScheduleAt = $updateScheduleAt;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }


    public function getApplication(): ?Applications
    {
        return $this->application;
    }

    public function setApplication(?Applications $application): self
    {
        $this->application = $application;

        return $this;
    }

    public function getUpdateBy(): ?User
    {
        return $this->update_by;
    }

    public function setUpdateBy(?User $update_by): self
    {
        $this->update_by = $update_by;

        return $this;
    }

    public function getGlpiTicketId(): ?int
    {
        return $this->glpi_ticket_id;
    }

    public function setGlpiTicketId(?int $glpi_ticket_id): self
    {
        $this->glpi_ticket_id = $glpi_ticket_id;

        return $this;
    }

    public function getReportFilename(): ?string
    {
        return $this->reportFilename;
    }

    public function setReportFilename($reportFilename): self
    {
        $this->reportFilename = $reportFilename;
        return $this;
    }
}

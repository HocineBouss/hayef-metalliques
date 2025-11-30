<?php

namespace App\Model;

class Event
{
    public function __construct(
        private string $id,
        private string $title,
        private \DateTimeImmutable $startAt,
        private \DateTimeImmutable $endAt,
        private ?string $image = null,
        private ?string $location = null,
        private ?string $description = null,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getStartAt(): \DateTimeImmutable
    {
        return $this->startAt;
    }

    public function getEndAt(): \DateTimeImmutable
    {
        return $this->endAt;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isOngoing(\DateTimeImmutable $now): bool
    {
        return $this->startAt <= $now && $this->endAt >= $now;
    }

    public function isUpcoming(\DateTimeImmutable $now): bool
    {
        return $this->startAt > $now;
    }

    public function isPast(\DateTimeImmutable $now): bool
    {
        return $this->endAt < $now;
    }
}

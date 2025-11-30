<?php

namespace App\Service;

use App\Model\Event;
use Symfony\Component\Yaml\Yaml;

class EventProvider
{
    /** @var Event[] */
    private array $events = [];

    public function __construct(string $projectDir)
    {
        $this->loadEvents($projectDir);
    }

    private function loadEvents(string $projectDir): void
    {
        $path = $projectDir . '/config/events.yaml';

        if (!file_exists($path)) {
            $this->events = [];
            return;
        }

        $data = Yaml::parseFile($path);
        $rows = $data['events'] ?? [];

        $tz = new \DateTimeZone('Europe/Paris');

        $this->events = array_map(function (array $row) use ($tz) {
            return new Event(
                $row['id'],
                $row['title'],
                new \DateTimeImmutable($row['start_at'], $tz),
                new \DateTimeImmutable($row['end_at'], $tz),
                $row['image'] ?? null,
                $row['location'] ?? null,
                $row['description'] ?? null
            );
        }, $rows);
    }

    /**
     * @return Event[]
     */
    public function getAll(): array
    {
        return $this->events;
    }

    /**
     * Retourne les évènements triés :
     * - d'abord EN COURS + À VENIR (par date de début croissante)
     * - puis PASSÉS (les plus récents d'abord)
     *
     * @return Event[]
     */
    public function getSortedByStatus(): array
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        $ongoingOrUpcoming = array_filter($this->events, function (Event $e) use ($now) {
            return $e->isOngoing($now) || $e->isUpcoming($now);
        });

        $past = array_filter($this->events, function (Event $e) use ($now) {
            return $e->isPast($now);
        });

        // en cours / à venir : du plus proche au plus lointain
        usort($ongoingOrUpcoming, function (Event $a, Event $b) {
            return $a->getStartAt() <=> $b->getStartAt();
        });

        // passés : les plus récents en premier
        usort($past, function (Event $a, Event $b) {
            return $b->getStartAt() <=> $a->getStartAt();
        });

        return array_merge($ongoingOrUpcoming, $past);
    }

    /**
     * Filtre par statut : all | ongoing | upcoming | past
     *
     * @return Event[]
     */
    public function getByStatus(string $status): array
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
        $events = $this->getSortedByStatus();

        if ($status === 'all') {
            return $events;
        }

        return array_values(array_filter($events, function (Event $event) use ($status, $now) {
            return match ($status) {
                'ongoing'  => $event->isOngoing($now),
                'upcoming' => $event->isUpcoming($now),
                'past'     => $event->isPast($now),
                default    => true,
            };
        }));
    }

    /**
     * Retourne l'évènement à mettre en avant :
     * - en priorité un évènement EN COURS
     * - sinon, l'évènement À VENIR le plus proche
     *
     * @return Event|null
     */
    public function getHighlightedEvent(): ?Event
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        // 1. Événements en cours
        $ongoing = array_filter($this->events, function (Event $event) use ($now) {
            return $event->isOngoing($now);
        });

        if (!empty($ongoing)) {
            usort($ongoing, function (Event $a, Event $b) {
                return $a->getStartAt() <=> $b->getStartAt();
            });

            return $ongoing[0];
        }

        // 2. Sinon, événement à venir le plus proche
        $upcoming = array_filter($this->events, function (Event $event) use ($now) {
            return $event->isUpcoming($now);
        });

        if (!empty($upcoming)) {
            usort($upcoming, function (Event $a, Event $b) {
                return $a->getStartAt() <=> $b->getStartAt();
            });

            return $upcoming[0];
        }

        // 3. Sinon, rien à mettre en avant
        return null;
    }
}

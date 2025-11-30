<?php

namespace App\Controller;

use App\Service\EventProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/evenements', name: 'events_index')]
    public function index(Request $request, EventProvider $eventProvider): Response
    {
        // status = all | ongoing | upcoming | past
        $status = $request->query->get('status', 'all');

        // sécurise un peu les valeurs
        if (!in_array($status, ['all', 'ongoing', 'upcoming', 'past'], true)) {
            $status = 'all';
        }

        $events = $eventProvider->getByStatus($status);

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'status' => $status,
        ]);
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

        // 1. On cherche les événements en cours
        $ongoing = array_filter($this->events, function (Event $event) use ($now) {
            return $event->isOngoing($now);
        });

        if (!empty($ongoing)) {
            // on prend celui qui a la date de début la plus proche
            usort($ongoing, function (Event $a, Event $b) {
                return $a->getStartAt() <=> $b->getStartAt();
            });

            return $ongoing[0];
        }

        // 2. Sinon, on prend l'évènement à venir le plus proche
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

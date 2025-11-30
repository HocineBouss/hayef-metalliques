<?php

namespace App\Controller;

use App\Service\EventProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EventProvider $eventProvider): Response
    {
        $highlightedEvent = $eventProvider->getHighlightedEvent();

        return $this->render('home/index.html.twig', [
            'highlightedEvent' => $highlightedEvent,
        ]);
    }
}

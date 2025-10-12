<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Finder\Finder;

final class AproposController extends AbstractController
{
    #[Route('/apropos', name: 'app_apropos')]
    public function index(): Response
    {
        $finder = new Finder();
        $partners = [];

        // Chemin absolu vers ton dossier public (pas assets/)
        $dir = $this->getParameter('kernel.project_dir') . '/public/assets/img/partenaires';

        if (is_dir($dir)) {
            $finder->files()->in($dir)->name('/\.(png|jpg|jpeg)$/i')->sortByName();
            foreach ($finder as $file) {
                $partners[] = '/assets/img/partenaires/' . $file->getFilename();
            }
        }

        return $this->render('apropos/index.html.twig', [
            'partners' => $partners,
        ]);
    }
}

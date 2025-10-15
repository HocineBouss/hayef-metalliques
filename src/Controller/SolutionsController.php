<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SolutionsController extends AbstractController
{
    #[Route('/produits', name: 'app_produits')]
    public function index(): Response
    {
        return $this->render('solutions/index.html.twig', [
            'controller_name' => 'SolutionsController',
        ]);
    }

    #[Route('/v-system/{slug}', name: 'app_produit')]
    public function produit(string $slug): Response
    {
        // Génère le chemin du template correspondant au slug
        $template = sprintf('solutions/fiche_%s.html.twig', $slug);

        // Vérifie que le fichier existe réellement
        $templatePath = $this->getParameter('kernel.project_dir') . '/templates/' . $template;
        if (!file_exists($templatePath)) {
            throw $this->createNotFoundException("Le produit demandé n'existe pas.");
        }

        // Rend le bon template
        return $this->render($template, [
            'slug' => $slug,
        ]);
    }


}

<?php

namespace App\Controller;

use App\Repository\PalanqueeRepository;
use App\Repository\PlongeurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class APIPlongeurController extends AbstractController
{

    public function __construct(
        private PalanqueeRepository $palanqueeRepository,
    )
    {

    }

    #[Route("/api/encadrants/{idEncadrant}/plongeursNoComments", name: "api_plongeurs_no_comments_encadrant", methods: ["GET"])]
    #[IsGranted('ROLE_MONITEUR')]
    public function getPalanqueesWithoutComments(
        $idEncadrant,
    ): JsonResponse {
        $palanquees = $this->palanqueeRepository->findPalanqueesWithoutCommentsForEncadrant($idEncadrant);
        return $this->json($palanquees); // Convertit directement le tableau en JSON
    }

    #[Route("/api/encadrants/plongeursNoComments", name: "api_plongeurs_no_comments_encadrants", methods: ["GET"])]
    #[isGranted('ROLE_ADMIN')]
    public function getPalanqueesWithoutCommentsAllEncadrant(): JsonResponse {
        $palanquees = $this->palanqueeRepository->findPalanqueesWithoutCommentsForAllEncadrant();
        return $this->json($palanquees); // Convertit directement le tableau en JSON
    }

    #[Route('/run-script', name: 'run_script', methods: ['POST'])]
    public function runScript(): JsonResponse
    {
        $output = null;
        $returnVar = null;

        // Commande pour exécuter le script Python
        $scriptPath = "../ScriptPython/spond.py";
        exec("python3 $scriptPath", $output, $returnVar);

        if ($returnVar !== 0) {
            return new JsonResponse(['error' => 'Erreur lors de l\'exécution du script', 'output' => $output], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => 'Script exécuté avec succès', 'output' => $output]);
    }

}
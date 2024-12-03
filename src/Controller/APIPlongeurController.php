<?php

namespace App\Controller;

use App\Repository\InscriptionSortieRepository;
use App\Repository\PalanqueeRepository;
use App\Repository\PlongeurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class APIPlongeurController extends AbstractController
{

    public function __construct(
        private PalanqueeRepository         $palanqueeRepository,
        private readonly PlongeurRepository $plongeurRepository,
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

    #[Route("/api/filter/hasNotPaidInscriptionSortie", name: 'api_plongeurs_has_not_paid_sortie', methods: ["GET"])]
    #[IsGranted('ROLE_ADMIN')]
    public function getPlongeursHasNotPaidInscriptionSortie(): JsonResponse {
        $plongeurs = $this->plongeurRepository->getAllHasNotPaidASortie();
        return $this->json($plongeurs);
    }
}
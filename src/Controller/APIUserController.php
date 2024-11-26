<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class APIUserController extends AbstractController
{

    #[Route("/api/me", name: "api_me", methods: ["GET"])]
    #[isGranted('ROLE_USER')]
    public function me()
    {
        $user = $this->getUser();
        return $this->json([
            'id' => $user->getId(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'isMoniteur' => $user->isMoniteur(),
        ]);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request, Security $security): JsonResponse
    {
        $response = new JsonResponse(['message' => 'Logged out successfully'], Response::HTTP_OK);
        $response->headers->clearCookie('BEARER', '/', null, true, true, 'none');

        return $response;
    }

}
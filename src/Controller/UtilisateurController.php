<?php

namespace App\Controller;

use App\Entity\Plongeur;
use App\Form\UtilisateurType;
use App\Service\UtilisateurManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $request, UtilisateurManager $utilisateurManager, EntityManagerInterface $entityManager): Response
    {
        $newUser = new Plongeur();
        $form = $this->createForm(UtilisateurType::class, $newUser, [
            'action' => $this->generateUrl('inscription'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $utilisateurManager->processNewUtilisateur($newUser, $form["plainPassword"]->getData());
                $entityManager->persist($newUser);
                $entityManager->flush();
            } catch (\Exception $e) {
                //TODO : afficher erreur
                return $this->redirectToRoute('inscription');
            }
            return $this->redirectToRoute('test'); // TODO : rediriger vers connexion
        }

        return $this->render('utilisateur/inscription.html.twig', [
            'controller_name' => 'UtilisateurController',
            'form' => $form
        ]);
    }

    #[Route('', name: 'test', methods: ['GET'])]
    public function test(): Response
    {
        return $this->render('base.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Plongeur;
use App\Form\UtilisateurType;
use App\Service\DatabaseContextResolver;
use App\Service\UtilisateurManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Util\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UtilisateurController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $request, UtilisateurManager $utilisateurManager, DatabaseContextResolver $resolver): Response
    {
        $entityManager = $resolver->getEntityManager();
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
                throw new Exception($e);
            }
            return $this->redirectToRoute('test'); // TODO : rediriger vers connexion
        }

        return $this->render('utilisateur/inscription.html.twig', [
            'controller_name' => 'UtilisateurController',
            'form' => $form
        ]);
    }

    #[Route('/connexion', name: 'connexion', methods: ['GET', 'POST'])]
    public function connexion(AuthenticationUtils $authenticationUtils) : Response {
        $lastEmailTried = $authenticationUtils->getLastUsername();

        return $this->render('utilisateur/connexion.html.twig', [
            'controller_name' => 'UtilisateurController',
            'last_email_tried' => $lastEmailTried
        ]);
    }

    #[Route('', name: 'test', methods: ['GET'])]
    public function test(): Response
    {
        return $this->render('accueil.html.twig', [
            'controller_name' => 'UtilisateurController',
        ]);
    }
}

<?php

namespace App\Controller;


use App\Repository\InscriptionSortieRepository;
use App\Entity\DonneeClub;
use App\Entity\InscriptionSortie;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Exception\NotFoundException;
use App\Repository\DonneeClubRepository;
use App\Repository\PalanqueeRepository;
use App\Repository\PlongeurRepository;
use App\Repository\SortieRepository;
use App\Service\DonneeClubManagerInterface;
use App\Service\SortieManagerInterface;
use App\Service\UtilisateurManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class APIPlongeurController extends AbstractController
{

    public function __construct(
        private readonly PalanqueeRepository         $palanqueeRepository,
        private readonly PlongeurRepository          $plongeurRepository,
        private readonly DonneeClubManagerInterface  $donneeClubManager,
        private readonly SortieManagerInterface      $sortieManager,
        private readonly UtilisateurManagerInterface $utilisateurManager,
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


    #[Route('/sorties/{idSpondSortie}/spond', name: 'api_sortie_spond', methods: ['POST'])]
    #[IsGranted('ROLE_MONITEUR')]
    public function importSortieSpond($idSpondSortie, Request $request): JsonResponse
    {
        try {
            $data = $request->toArray();
            $this->sortieManager->processNewFromArray($data, $idSpondSortie);
            return new JsonResponse(['message' => 'Sortie importée avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        } catch (ORMException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/sorties/spond', name: 'api_sorties_spond', methods: ['GET'])]
    #[IsGranted('ROLE_MONITEUR')]
    public function getSortiesSpond(): JsonResponse
    {
        try {
            $donneeClub = $this->donneeClubManager->getDonneeClub();
            $username = $donneeClub->getEmailSpond();
            $password = $donneeClub->getPasswordSpond();
            $password = $this->donneeClubManager->decryptPassword($password);
            $groupId = $donneeClub->getGroupIdSpond();
            $sortiesJson = $this->sortieManager->getSortiesSpond($username, $password, $groupId, false);
            return new JsonResponse($sortiesJson);
        } catch (NotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }
        catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }


    #[Route('/donneeClub/findGroupId', name: 'api_sortie_spond_findGroupId', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function findGroupId(Request $request): JsonResponse {
        $data = $request->toArray();
        $username = $data['emailSpond'];
        $password = $data['plainPasswordSpond'];
        $groupName = $data['groupNameSpond'];

        try {
            $groupID = $this->donneeClubManager->findGroupIdSpond($username, $password, $groupName);
            return new JsonResponse(['groupId' => $groupID]);
        } catch (NotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Server error : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    #[Route('/donneeClubs/intervalUpdate', name: 'api_interval_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function intervalUpdate(Request $request): JsonResponse {
        try {
            $this->sortieManager->updateSpondUpdateInterval($request->get('interval'));
            return new JsonResponse(['message' => 'Interval updated']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/sorties/spondUpdate', name: 'api_spond_update', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function spondUpdate(): JsonResponse {
        try {
            if ($this->isGranted('ROLE_ADMIN')) {
                $this->sortieManager->updateSpondUpdateDate();
                return new JsonResponse(['message' => 'Spond update date updated']);
            }
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        } catch (ORMException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/sorties/spond', name: 'api_import_all', methods: ['POST'])]
    #[IsGranted('ROLE_MONITEUR')]
    public function importAllSortie(Request $request): JsonResponse {
        try {
            $this->sortieManager->importAllSortie($request->toArray());
            return new JsonResponse(['message' => 'Sorties importées avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        } catch (ORMException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

}


<?php

namespace App\Controller;

use App\Entity\InscriptionSortie;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Repository\PalanqueeRepository;
use App\Repository\PlongeurRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class APIPlongeurController extends AbstractController
{

    public function __construct(
        private PalanqueeRepository $palanqueeRepository,
        private EntityManagerInterface $entityManager,
        private SortieRepository $sortieRepository,
        private PlongeurRepository $plongeurRepository
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


    /*
     * Problemes :
     *
     * MDP en clair dans le script -> comment le stocker en securité ?
     * Probleme si une personne est inscrite sur spond et pas sur neptuneDive
     * Facile de recupérer un ensemble de sorties avec une date, mais si on veut ajouter 1 seule sortie,
     * il faudrai faire un form pour pouvoir rechercher une sortie par date ou par tirte ou par lieu car
     * pour recuperer une sortie il faut connaitre son idSpond
     *
     * Incertitudes :
     *
     * Je ne sais pais si le fonctionnement du script et la facon de faire avec les csv est la bonne cf. Ce que Coletta a dit
     *
     *
     * TODO :
     * Page pour mettre ses identifiants spond et le nom du groupe spond
     * Faire une requete pour trouver l'id du groupe spond
     * Faire un bouton pour lancer le script et importer une/des sorties (avec un form pour choisir la date ou la date du jour ?)
     * Faire la requete pour recuperer les sorties
     * Faire la requete pour recuperer les plongeurs inscrits a une sortie
     *
     *
     * A faire peut etre :
     * Un form pour demander quelle plongeur est le directeur de plongée et le securite de surface
     * Gerer les personens qui sont inscrites sur spond mais pas sur neptunedive cf. Problemes
     */
    #[Route('/run-script', name: 'run_script', methods: ['POST'])]
    public function runScript(): JsonResponse
    {
        $output = null;
        $returnVar = null;


        // Commande pour exécuter le script Python
        $scriptPath = __DIR__ . '/../ScriptPython/spondScript.py';

        exec("python3 $scriptPath", $csvFilePath, $returnVar);


        // Vérifier si le fichier existe
        if (!file_exists($csvFilePath[0])) {
            return new JsonResponse(['error' => 'Le fichier CSV est introuvable'], 400);
        }

        // Lire le contenu du fichier CSV
        try {
            $handle = fopen($csvFilePath[0], 'r');
            if (!$handle) {
                throw new \Exception('Impossible d\'ouvrir le fichier CSV.');
            }

            // Lire la première ligne (en-têtes du fichier CSV)
            $headers = fgetcsv($handle);

            // Boucler sur chaque ligne du fichier
            while (($data = fgetcsv($handle)) !== false) {
                // Associer les données aux en-têtes
                $row = array_combine($headers, $data);
                if (!$this->sortieRepository->findOneBy(['idSpond' => $row['ID']])) {
                    // Créer une nouvelle entité à partir des données
                    $sortie = new Sortie();
                    $sortie->setIdSpond($row['ID'] ?? null);
                    $sortie->setLieu($row['Lieu'] ?? null);
                    $sortie->setType("Mer");
                    $sortie->setDirecteurPlongee($this->getUser());
                    $sortie->setSecuriteSurface($this->getUser());
                    $sortie->setDateSortie(new \DateTime($row['Date'] ?? null));
                    $heure = new \DateTime($row['Heure'] ?? null);
                    if ($heure < new \DateTime('12:00')) {
                        $creneau = Creneaux::MATIN;
                    } else {
                        $creneau = Creneaux::APRES_MIDI;
                    }
                    $sortie->setCreneau($creneau);

                    // Persister l'entité


                    // Ajouter les plongeurs à la sortie
                    $plongeursNames = explode(',', $row['Participants']);

                    foreach ($plongeursNames as $plongeurName) {
                        $plongeurName = explode(' ', $plongeurName);
                        $plongeur = $this->plongeurRepository->findOneBy(['prenom' => trim($plongeurName[0]), 'nom' => trim($plongeurName[1])]);
                        if ($plongeur) {
                            $inscription = new InscriptionSortie();
                            $inscription->setSortie($sortie);
                            $inscription->setPlongeur($plongeur);
                            $sortie->addInscriptionSorty($inscription);
                        }
                    }
                    $this->entityManager->persist($sortie);
                }
            }
            // Fermer le fichier
            fclose($handle);

            // Sauvegarder les entités en base de données
            $this->entityManager->flush();

            unlink($csvFilePath[0]);

            return new JsonResponse(['message' => 'Données importées avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

}
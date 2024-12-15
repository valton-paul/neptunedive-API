<?php

namespace App\DataFixtures;

use App\Entity\Bateau;
use App\Entity\InscriptionSortie;
use App\Entity\Lieu;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Enum\NiveauMoniteur;
use App\Enum\NiveauPlongeeAutonome;
use App\Enum\NiveauPlongeeEncadre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        /**
         *  Création de bateaux
         */
        $bateauxObject = [];
        $bateaux = [
            [
                'immatriculation' => 'Red Pepper',
                'nbPlaces' => 12,
            ],
            [
                'immatriculation' => 'Bigoudi',
                'nbPlaces' => 20,
            ],
        ];

        foreach ($bateaux as $data) {
            $bateau = new Bateau();
            $bateau->setImmatriculation($data['immatriculation']);
            $bateau->setNbPlaces($data['nbPlaces']);
            $bateauxObject[] = $bateau;
            $manager->persist($bateau);
        }

        /**
         *  Création de lieux
         */

        $lieuObject = [];
        $lieux = [
            [
                'nomLieu' => 'Sète',
                'description' => 'Charmante ville portuaire du sud de la France, connue pour ses canaux et son ambiance méditerranéenne.',
                'lienMaps' => 'https://maps.app.goo.gl/vocjKqDaDV2BkAwF6'
            ],
            [
                'nomLieu' => 'Méjean',
                'description' => 'Petit village côtier entouré de calanques, offrant des paysages naturels exceptionnels.',
                'lienMaps' => 'https://maps.app.goo.gl/gKw3sV1REu2gKVr9A'
            ],
            [
                'nomLieu' => 'Marseille',
                'description' => 'Ville emblématique du sud de la France, célèbre pour son Vieux-Port et sa gastronomie provençale.',
                'lienMaps' => 'https://maps.app.goo.gl/yk66zzVYrJb3XEKU6'
            ],
            [
                'nomLieu' => 'Banyuls-sur-Mer',
                'description' => 'Village côtier proche de la frontière espagnole, réputé pour ses vignobles en terrasses et son bord de mer.',
                'lienMaps' => 'https://maps.app.goo.gl/YXiiE6Sr7SWiNEPL9'
            ],
            [
                'nomLieu' => 'Villefranche-sur-Mer',
                'description' => 'Charmante ville de la Côte d’Azur, connue pour sa baie pittoresque et ses ruelles colorées.',
                'lienMaps' => 'https://maps.app.goo.gl/pK99An55KShMYxJi9'
            ]
        ];

        foreach ($lieux as $data) {
            $lieu = new Lieu();
            $lieu->setNomLieu($data['nomLieu']);
            $lieu->setDescription($data['description']);
            $lieu->setLienMaps($data['lienMaps']);
            $lieuObject[] = $lieu;
            $manager->persist($lieu);
        }


        /**
         *  Création de plongeurs
         */
        $plongeurs = [
            [
                'email' => 'elodie@gmail.com',
                'nom' => 'Amilhau',
                'prenom' => 'Elodie',
                'formation' => true,
                'moniteur' => false,
                'plainPassword' => 'Azerty123!',
                'niveauPlongeeAutonome' => NiveauPlongeeAutonome::PA40,
                'niveauPlongeeEncadre' => NiveauPlongeeEncadre::PE40,
                'niveauMoniteur' => NiveauMoniteur::NONE,
                'roles' => ['ROLE_USER']
            ],
            [
                'email' => 'matthieu@gmail.com',
                'nom' => 'Lopez',
                'prenom' => 'Matthieu',
                'formation' => false,
                'moniteur' => false,
                'plainPassword' => 'Azerty123!',
                'niveauPlongeeAutonome' => NiveauPlongeeAutonome::PA20,
                'niveauPlongeeEncadre' => NiveauPlongeeEncadre::PE40,
                'niveauMoniteur' => NiveauMoniteur::NONE,
                'roles' => ['ROLE_USER']
            ],
            [
                'email' => 'anais@gmail.com',
                'nom' => 'Beauvieux',
                'prenom' => 'Anais',
                'formation' => false,
                'moniteur' => true,
                'plainPassword' => 'Azerty123!',
                'niveauPlongeeAutonome' => NiveauPlongeeAutonome::PA60,
                'niveauPlongeeEncadre' => NiveauPlongeeEncadre::PE60,
                'niveauMoniteur' => NiveauMoniteur::E2,
                'roles' => ['ROLE_USER', 'ROLE_MONITEUR']
            ],
            [
                'email' => 'pascal@gmail.com',
                'nom' => 'Celka',
                'prenom' => 'Pascal',
                'formation' => false,
                'moniteur' => true,
                'plainPassword' => 'Azerty123!',
                'niveauPlongeeAutonome' => NiveauPlongeeAutonome::PA60,
                'niveauPlongeeEncadre' => NiveauPlongeeEncadre::PE60,
                'niveauMoniteur' => NiveauMoniteur::E2,
                'roles' => ['ROLE_USER', 'ROLE_MONITEUR']
            ],
            [
                'email' => 'remi@gmail.com',
                'nom' => 'Coletta',
                'prenom' => 'Remi',
                'formation' => true,
                'moniteur' => true,
                'plainPassword' => 'Azerty123!',
                'niveauPlongeeAutonome' => NiveauPlongeeAutonome::PA60,
                'niveauPlongeeEncadre' => NiveauPlongeeEncadre::PE60,
                'niveauMoniteur' => NiveauMoniteur::NONE,
                'roles' => ['ROLE_USER', 'ROLE_MONITEUR', 'ROLE_ADMIN']
            ],
        ];

        $plongeursObjects = [];
        foreach ($plongeurs as $data) {
            $plongeur = new Plongeur();
            $plongeur->setEmail($data['email']);
            $plongeur->setNom($data['nom']);
            $plongeur->setPrenom($data['prenom']);
            $plongeur->setFormation($data['formation']);
            $plongeur->setMoniteur($data['moniteur']);
            $plongeur->setPlainPassword($data['plainPassword']);
            $plongeur->setNiveauPlongeeAutonome($data['niveauPlongeeAutonome']);
            $plongeur->setNiveauPlongeeEncadre($data['niveauPlongeeEncadre']);
            $plongeur->setNiveauMoniteur($data['niveauMoniteur']);

            $hashedPassword = $this->passwordHasher->hashPassword($plongeur, $plongeur->getPlainPassword());
            $plongeur->setPassword($hashedPassword);

            foreach ($data['roles'] as $role) {
                $plongeur->addRole($role);
            }

            $manager->persist($plongeur);
            $plongeursObjects[] = $plongeur;
        }

        /**
         *  Création de sorties
         */
        $sortiesData = [
            [
                'type' => 'Plongée Scuba',
                'dateSortie' => new \DateTime('2023-11-15'),
                'creneau' => Creneaux::MATIN,
                'directeurPlongee' => $plongeursObjects[1],
                'securiteSurface' => $plongeursObjects[1],
                'bateau' => $bateauxObject[1],
                'lieu' => $lieuObject[4],
                'heure_sortie' => '20:50',
                'maxParticiapant' => 10,
                'prix' => 30,
            ],
            [
                'type' => 'Plongée Scuba',
                'dateSortie' => new \DateTime('2025-01-15'),
                'directeurPlongee' => $plongeursObjects[1],
                'securiteSurface' => $plongeursObjects[1],
                'bateau' => $bateauxObject[0],
                'lieu' => $lieuObject[0],
                'heure_sortie' => '20:50',
                'maxParticiapant' => 10,
                'prix' => 30,
            ],
            [
                'type' => 'Plongée Apnée',
                'dateSortie' => new \DateTime('2025-08-22'),
                'creneau' => Creneaux::APRES_MIDI,
                'directeurPlongee' => $plongeursObjects[1],
                'securiteSurface' => $plongeursObjects[0],
                'bateau' => $bateauxObject[0],
                'lieu' => $lieuObject[1],
                'heure_sortie' => '12:00',
                'maxParticiapant' => 20,
                'prix' => 15,
            ],
            [
                'type' => 'Plongée Mer',
                'dateSortie' => new \DateTime('2025-12-25'),
                'directeurPlongee' => $plongeursObjects[1],
                'securiteSurface' => $plongeursObjects[0],
                'bateau' => $bateauxObject[1],
                'lieu' => $lieuObject[2],
                'heure_sortie' => '10:30',
                'maxParticiapant' => 16,
                'prix' => 5,
            ],
            [
                'type' => 'Plongée Mer',
                'dateSortie' => new \DateTime('2025-02-10'),
                'directeurPlongee' => $plongeursObjects[1],
                'securiteSurface' => $plongeursObjects[0],
                'bateau' => $bateauxObject[1],
                'lieu' => $lieuObject[3],
                'heure_sortie' => '10:30',
                'maxParticiapant' => 16,
                'prix' => 5,
            ],
        ];

        foreach ($sortiesData as $data) {
            $sortie = new Sortie();
            $sortie->setType($data['type'])
                ->setDateSortie($data['dateSortie'])
                ->setIdLieu($data['lieu'])
                ->setDirecteurPlongee($data['directeurPlongee'])
                ->setSecuriteSurface($data['securiteSurface'])
                ->setBateau($data['bateau'])
                ->setHeureSortie($data['heure_sortie'])
                ->setNbMaxInscription($data['maxParticiapant'])
                ->setPrice($data['prix']);
            $manager->persist($sortie);

            foreach ($plongeursObjects as $plongeur) {
                if($data['bateau'] === $bateauxObject[0]) {
                    $inscriptionSortie = new InscriptionSortie();
                    $inscriptionSortie->setSortie($sortie)
                        ->setPlongeur($plongeur);
                    $manager->persist($inscriptionSortie);
                }
            }
        }

        $manager->flush();
    }
}
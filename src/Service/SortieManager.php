<?php

namespace App\Service;

use App\Entity\InscriptionSortie;
use App\Entity\Lieu;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use App\Exception\NotFoundException;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use Cassandra\Date;
use DateException;
use DateTime;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;

class SortieManager implements SortieManagerInterface
{
    private $entityManager;

    public function __construct(
        private DatabaseContextResolver $provider,
        private Security $security,
        private SortieRepository $sortieRepository,
        private LieuRepository $lieuRepository
    )
    {
        $this->entityManager = $this->provider->getEntityManager();
    }

    /**
     * @inheritDoc
     */
    function signUpFor(Plongeur $plongeur, Sortie $sortie): void
    {
        $inscription = new InscriptionSortie();
        $inscription->setPlongeur($plongeur);
        $inscription->setSortie($sortie);
        if ($sortie->getInscriptions()->contains($inscription)) {
            throw new AlreadyExistsException();
        }
        $sortie->addInscriptionSorty($inscription);
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function quit(Plongeur $plongeur, Sortie $sortie): void
    {
        if (!$sortie->getInscriptions()->contains($plongeur)) {
            throw new MemberNotFoundException();
        }
        if ($sortie->getDirecteurPlongee() === $plongeur) {
            throw new NotAllowedException("Vous ne pouvez pas vous désinscrire d'une sortie dont vous êtes le directeur de plongée");
        }
        $sortie->removeInscription($plongeur);
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function processNew(Sortie $sortie, FormInterface $form): void
    {

        if (!$this->security->isGranted('ROLE_MONITEUR')) {
            throw new NotAllowedException("Vous n'êtes pas autorisé à créer une sortie");
        }
        $this->UpdateSortie($form, $sortie, $this->security->getUser());
        $plongeur = $this->security->getUser();
        if ($plongeur instanceof Plongeur) {
            $sortie->addInscriptionSorty($plongeur);
        }
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function processEdit(Sortie $sortie, FormInterface $form): void
    {
        if (!$this->security->isGranted('ROLE_MONITEUR')) {
            throw new NotAllowedException("Vous n'êtes pas autorisé à éditer une sortie");
        }
        $this->UpdateSortie($form, $sortie, $form->get('directeurPlongee')->getData());
        $this->entityManager->persist($sortie);
        $this->entityManager->flush();
    }

    /**
     * @inheritDoc
     */
    function cancel(Sortie $sortie): void
    {
        if ($sortie->getDateSortie() < new DateTime()) {
            throw new NotAllowedException("Vous ne pouvez pas annuler une sortie    passée");
        }
        $this->entityManager->remove($sortie);
        $this->entityManager->flush();
    }

    /**
     * @param FormInterface $form
     * @param Sortie $sortie
     * @param $directeurPlongee
     * @return void
     * @throws DateException
     */
    private function UpdateSortie(FormInterface $form, Sortie $sortie, $directeurPlongee): void
    {
        $date = $form->get('dateSortie')->getData();
        if ($date < new DateTime()) {
            throw new DateException();
        }
        $sortie->setDateSortie($date);
        $sortie->setLieu($form->get('lieu')->getData());
        $sortie->setType($form->get('type')->getData());
        $sortie->setCreneau($form->get('creneau')->getData());
        $sortie->setDirecteurPlongee($directeurPlongee);
        $sortie->setSecuriteSurface($form->get('securiteSurface')->getData());
        $sortie->setPiloteBateau($form->get('piloteBateau')->getData());
        /*$sortie->setBateau($form->get('bateau')->getData());*/
    }


    function getSortiesSpond($username, $password, $idGroupe): array
    {
        $scriptPath = __DIR__ . '/../ScriptPython/spondScript.py';

        exec("python3 $scriptPath --username $username --password $password --group_id $idGroupe", $csvFilePath);

        if ($csvFilePath === []) {
            throw new NotFoundException('Aucune sortie trouvée');
        }

        // Vérifier si le fichier existe
        if (!file_exists($csvFilePath[0])) {
            throw new NotFoundException('Le fichier CSV est introuvable');
        }

        // Lire le contenu du fichier CSV
        $handle = fopen($csvFilePath[0], 'r');
        if (!$handle) {
            throw new \Exception('Impossible d\'ouvrir le fichier CSV.');
        }

        // Lire la première ligne (en-têtes du fichier CSV)
        $headers = fgetcsv($handle);
        $sortiesJson = [];
        // Boucler sur chaque ligne du fichier
        while (($data = fgetcsv($handle)) !== false) {
            // Associe les données aux en-têtes
            $row = array_combine($headers, $data);
            if (!$this->sortieRepository->findOneBy(['idSpond' => $row['ID']])) {
                $sortieJson = [
                    'idSpond' => $row['ID'] ?? null,
                    'lieu' => $row['Lieu'] ?? null,
                    'dateSortie' => $row['Date'] ?? null,
                    'heure_sortie' => $row['Heure'] ?? null,
                    'participants' => explode(',', $row['Participants'])
                ];
                $sortiesJson[] = $sortieJson;
            }
        }
        fclose($handle);
        unlink($csvFilePath[0]);
        return $sortiesJson;
    }


    function processNewArray(array $sortie, $idSpondSortie): Sortie
    {
        $newSortie = new Sortie();
        $newSortie->setIdSpond($idSpondSortie);
        $lieu = $this->lieuRepository->findOneBy(['nomLieu' => $sortie['lieu']]);
        $lieu ? $newSortie->setIdLieu($lieu) :
            $lieu = new Lieu();
            $lieu->setNomLieu($sortie['lieu']);
            $lieu->setLienMaps("https://www.google.com/maps/place/" . $sortie['lieu']);
            $this->entityManager->persist($lieu);
            $newSortie->setIdLieu($lieu);
        $newSortie->setType("Mer");
        $newSortie->setDirecteurPlongee($this->security->getUser());
        $newSortie->setSecuriteSurface($this->security->getUser());
        $newSortie->setDateSortie(new DateTime($sortie['dateSortie']));
        $newSortie->setHeureSortie($sortie['heure_sortie']);
        $newSortie->setNbMaxInscription(count($sortie['participants']));
        $this->entityManager->persist($newSortie);
        $this->entityManager->flush();
        return $newSortie;
    }
}
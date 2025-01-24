<?php

namespace App\Service;

use App\Entity\InscriptionSortie;
use App\Entity\Lieu;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Exception\AlreadyExistsException;
use App\Exception\MemberNotFoundException;
use App\Exception\NotAllowedException;
use App\Exception\NotFoundException;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use DateException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;

class SortieManager implements SortieManagerInterface
{


    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private SortieRepository $sortieRepository,
        private LieuRepository $lieuRepository,
        private DonneeClubManagerInterface $donneeClubManager,
        private UtilisateurManagerInterface $utilisateurManager
    )
    {
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


    function getSortiesSpond($username, $password, $idGroupe, $date): array
    {
        $scriptPath = __DIR__ . '/../ScriptPython/spondScript.py';
        $escapedUsername = escapeshellarg($username);
        $escapedPassword = escapeshellarg($password);
        $escapedGroupID = escapeshellarg(trim($idGroupe));

        if ($date){
            $date = $date->format('Y-m-d');
            exec("python3 $scriptPath --username $escapedUsername --password $escapedPassword --group_id $escapedGroupID --date $date 2>&1", $csvFilePath);
        }else{
            exec("python3 $scriptPath --username $escapedUsername --password $escapedPassword --group_id $escapedGroupID 2>&1", $csvFilePath);
        }

        if ($csvFilePath[0] === "Aucun événement trouvé.") {
            throw new NotFoundException('Aucune sortie trouvée');
        }
        if (!file_exists($csvFilePath[0])) {
            throw new NotFoundException('Le fichier CSV est introuvable : ' . $csvFilePath[0]);
        }
        $handle = fopen($csvFilePath[0], 'r');
        if (!$handle) {
            throw new \Exception('Impossible d\'ouvrir le fichier CSV.');
        }
        $headers = fgetcsv($handle);
        $sortiesJson = [];
        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($headers, $data);
            if (!$this->sortieRepository->findOneBy(['idSpond' => $row['ID'] ?? null])) {
                $sortieJson = [
                    'idSpond' => $row['ID'] ?? null,
                    'lieu' => $row['Lieu'] ?? null,
                    'titre' => $row['Titre'] ?? null,
                    'dateSortie' => $row['Date'] ?? null,
                    'heure_sortie' => $row['Heure'] ?? null,
                    'participants' => isset($row['Participants']) ? explode(',', $row['Participants']) : []
                ];
                $sortiesJson[] = $sortieJson;
            }
        }
        fclose($handle);
        unlink($csvFilePath[0]);
        return $sortiesJson;
    }


    function importAllSortie(array $sorties = null, DateTime $date = null): void
    {
        if (!$sorties) {
            $donneeClub = $this->donneeClubManager->getDonneeClub();
            $username = $donneeClub->getEmailSpond();
            $password = $this->donneeClubManager->decryptPassword($donneeClub->getPasswordSpond());
            $groupId = $donneeClub->getGroupIdSpond();
            $sorties = $this->getSortiesSpond($username, $password, $groupId, $date);
        }
        foreach ($sorties as $sortie) {
            $this->processNewFromArray($sortie, $sortie['idSpond']);
        }
    }

    function processNewFromArray(array $sortie, $idSpondSortie): void
    {
        $newSortie = new Sortie();
        $newSortie->setIdSpond($idSpondSortie);
        $lieu = $this->lieuRepository->findOneBy(['nomLieu' => $sortie['lieu']]);
        if (!$lieu) {
            $lieu = new Lieu();
            $lieu->setNomLieu($sortie['lieu']);
            $lieu->setLienMaps("https://www.google.com/maps/place/" . urlencode($sortie['lieu']));
            $this->entityManager->persist($lieu);
        }
        $newSortie->setIdLieu($lieu);
        //TODO : A modifier
        if (str_contains($sortie['titre'], 'Piscine')) {
            $newSortie->setType("Plongée Piscine");
        }else {
            $newSortie->setType("Plongée Mer");
        }
        $admin = $this->utilisateurManager->getPlongeurAdmin();
        $newSortie->setDirecteurPlongee($admin);
        $newSortie->setSecuriteSurface($admin);
        $newSortie->setDateSortie(new DateTime($sortie['dateSortie']));
        $newSortie->setHeureSortie($sortie['heure_sortie']);
        $newSortie->setNbMaxInscription(count($sortie['participants']));
        $this->entityManager->persist($newSortie);
        $this->entityManager->flush();
        foreach ($sortie['participants'] as $plongeurName) {
            $plongeurName = explode(' ', trim($plongeurName));
            $plongeur = $this->utilisateurManager->getPlongeurByLastnameAndFirstname(trim($plongeurName[1]), trim($plongeurName[0]));
            if ($plongeur) {
                $this->signUpFor($plongeur, $newSortie);
            }
        }
    }


    // Exemple d'une fonction Symfony qui met à jour le fichier cron
    /*public function updateCronJob($intervalle)
    {
        $cronFile = '/etc/cron.d/spond-cron-job';
        exec('crontab -r');
        $cronJob = $this->generateCronJob($intervalle);
        file_put_contents($cronFile, $cronJob);
        exec('crontab ' . $cronFile);
    }*/

    //private function generateCronJob($intervalle)
    //{
        //$cronTime = match ($intervalle) {
         //   '2day' => '0 0 */2 * *',
         //   '3day' => '0 0 */3 * *',
         //   '4day' => '0 0 */4 * *',
         //   '5day' => '0 0 */5 * *',
         //   '6day' => '0 0 */6 * *',
         //   '1week' => '0 0 * * 0',
          //  '1month' => '0 0 1 * *',
          //  default => '0 0 * * *',
        //};
       // return "$cronTime /usr/local/bin/php /var/www/html/neptunedive/bin/console c:iae >> /var/www/html/cron.log 2>&1; echo 'Cron job executed' >> /var/www/html/cron.log\n";
    //}


    public function updateSpondUpdateDate(): void
    {
        $donneeClub = $this->donneeClubManager->getDonneeClub();
        if ($donneeClub->getLastSpondUpdateDate() && $donneeClub->getLastSpondUpdateDate() >= (new DateTime())->sub($donneeClub->getIntervalSpondUpdate())) {
            $this->importAllSortie(null, $donneeClub->getLastSpondUpdateDate());
            $donneeClub->setLastSpondUpdateDate(new DateTime());
            $this->entityManager->persist($donneeClub);
            $this->entityManager->flush();
        }
    }


    public function updateSpondUpdateInterval($interval): void
    {
        $dateInterval = match ($interval) {
            '2day' => new \DateInterval('P2D'),
            '3day' => new \DateInterval('P3D'),
            '4day' => new \DateInterval('P4D'),
            '5day' => new \DateInterval('P5D'),
            '6day' => new \DateInterval('P6D'),
            '1week' => new \DateInterval('P1W'),
            '2week' => new \DateInterval('P2W'),
            '1month' => new \DateInterval('P1M'),
            default => new \DateInterval('P1D'),
        };
        $donneeClub = $this->donneeClubManager->getDonneeClub();
        $donneeClub->setIntervalSpondUpdate($dateInterval);
        $donneeClub->setLastSpondUpdateDate(new DateTime());
        $this->entityManager->persist($donneeClub);
        $this->entityManager->flush();
    }

}
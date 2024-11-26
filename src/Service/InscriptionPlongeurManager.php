<?php

namespace App\Service;

use App\Entity\InscriptionPlongeur;
use App\Entity\Plongeur;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;
use App\Repository\InscriptionPlongeurRepository;
use Doctrine\ORM\EntityManagerInterface;

class InscriptionPlongeurManager implements InscriptionPlongeurManagerInterface
{
    public function __construct(
        private InscriptionPlongeurRepository $inscriptionPlongeurRepository,
        private UtilisateurManager            $utilisateurManager,
        private EntityManagerInterface $entityManager
    )
    {
    }

    /**
     * @throws AlreadyExistsException
     */
    private
    function checkIfEmailExistsAlready(InscriptionPlongeur $inscriptionPlongeur): void
    {
        $email = $inscriptionPlongeur->getEmail();
        $alreadyExist = $this->inscriptionPlongeurRepository->findOneBy(['email' => $email]);
        if ($alreadyExist) {
            throw new AlreadyExistsException('Cet email existe déjà');
        }
    }

    public
    function processNewInscription(InscriptionPlongeur $inscriptionPlongeur): void
    {
        $this->checkIfEmailExistsAlready($inscriptionPlongeur);
        $inscriptionPlongeur->setValidee(false);
    }

    /**
     * @throws NotAllowedException
     */
    public
    function createPlongeurFromDemande(InscriptionPlongeur $inscriptionPlongeur, string $plainPassword): Plongeur
    {
        if (!$inscriptionPlongeur->getValidee()) Throw new NotAllowedException("La demande d'inscription n'a pas été validée");
        $plongeur = new Plongeur();
        $plongeur->setEmail($inscriptionPlongeur->getEmail());
        $plongeur->setNom($inscriptionPlongeur->getNom());
        $plongeur->setPrenom($inscriptionPlongeur->getPrenom());
        $plongeur->setFormation($inscriptionPlongeur->isFormation());
        $plongeur->setNiveauMoniteur($inscriptionPlongeur->getNiveauMoniteur());
        if ($inscriptionPlongeur->getNiveauPlongeeAutonome() != null) {
            $plongeur->setNiveauPlongeeAutonome($inscriptionPlongeur->getNiveauPlongeeAutonome());
        }
        if ($inscriptionPlongeur->getNiveauPlongeeEncadre() != null) {
            $plongeur->setNiveauPlongeeEncadre($inscriptionPlongeur->getNiveauPlongeeEncadre());
        }

        $this->utilisateurManager->processNewUtilisateur($plongeur, $plainPassword);

        $this->entityManager->persist($plongeur);
        $this->entityManager->flush();

        return $plongeur;
    }

    /**
     * @throws NotAllowedException
     */
    public
    function validateInscription(InscriptionPlongeur $inscriptionPlongeur): void
    {
        if ($inscriptionPlongeur->getValidee()) throw new NotAllowedException('Cette demande a déjà été validée');
        $inscriptionPlongeur->setValidee(true);

    }
}
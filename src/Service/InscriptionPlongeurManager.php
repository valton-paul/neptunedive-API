<?php

namespace App\Service;

use App\DTO\CreateInscriptionInput;
use App\Entity\InscriptionPlongeur;
use App\Entity\Plongeur;
use App\Enum\NiveauPlongeeAutonome;
use App\Exception\AlreadyExistsException;
use App\Exception\NotAllowedException;
use App\Repository\InscriptionPlongeurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InscriptionPlongeurManager implements InscriptionPlongeurManagerInterface
{
    public function __construct(
        private InscriptionPlongeurRepository $inscriptionPlongeurRepository,
        private UtilisateurManager            $utilisateurManager,
        private EntityManagerInterface        $entityManager,
        private MailerInterface               $mailer,
        private Environment                   $twig,

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
    function processNewInscription(CreateInscriptionInput $data): void
    {
        $inscriptionPlongeur = new InscriptionPlongeur();

        $inscriptionPlongeur->setEmail($data->email);
        $inscriptionPlongeur->setNom($data->nom);
        $inscriptionPlongeur->setPrenom($data->prenom);
        $inscriptionPlongeur->setFormation($data->formation ?? false);
        $inscriptionPlongeur->setNiveauPlongeeAutonome($data->niveauPlongeeAutonome);
        $inscriptionPlongeur->setNiveauPlongeeEncadre($data->niveauPlongeeEncadre);
        $inscriptionPlongeur->setNiveauMoniteur($data->niveauMoniteur);
        $inscriptionPlongeur->setPhotoDeProfil($data->photoDeProfil);
        $inscriptionPlongeur->setCarteNiveau($data->carteNiveau);
        $inscriptionPlongeur->setCertificatMedical($data->certificatMedical);

        $this->checkIfEmailExistsAlready($inscriptionPlongeur);
        $inscriptionPlongeur->setValidee(false);

        $this->entityManager->persist($inscriptionPlongeur);
        $this->entityManager->flush();
    }

    /**
     * @throws NotAllowedException
     */
    private
    function createPlongeur(InscriptionPlongeur $inscriptionPlongeur, string $plainPassword): Plongeur
    {
        if (!$inscriptionPlongeur->getValidee()) throw new NotAllowedException("La demande d'inscription n'a pas été validée");
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

//    private function sendValidationEmail(InscriptionPlongeur $inscriptionPlongeur): void
//    {
//        $token = Uuid::v4()->toRfc4122();
//        $inscriptionPlongeur->setVerificationToken($token);
//        $inscriptionPlongeur->setTokenExpiration(new \DateTime('+3 days')); // Token valide pour 3 jours
//
//        $this->entityManager->persist($inscriptionPlongeur);
//        $this->entityManager->flush();
//
//        $emailContent = $this->twig->render('emails/verification.html.twig', [
//            'user' => $inscriptionPlongeur,
//            'token' => $token,
//        ]);
//
//        $email = (new Email())
//            ->from('neptunedivetest@gmail.com') // Adresse e-mail du club
//            ->to($inscriptionPlongeur->getEmail())
//            ->subject('Vérifiez votre adresse e-mail')
//            ->html($emailContent);
//
//        $this->mailer->send($email);
//    }

    /**
     * @throws NotAllowedException
     */
    public
    function validateInscription(InscriptionPlongeur $inscriptionPlongeur): void
    {
        if ($inscriptionPlongeur->getValidee()) throw new NotAllowedException('Cette demande a déjà été validée');

        $inscriptionPlongeur->setValidee(true);
//        $this->sendValidationEmail($inscriptionPlongeur);
    }

    /**
     * @throws NotAllowedException
     */
    public function createPlongeurFromDemande(InscriptionPlongeur $inscriptionPlongeur, string $plainPassword): Plongeur
    {
        if (!$inscriptionPlongeur) {
            throw new NotAllowedException('Token invalide');
        }
        if ($inscriptionPlongeur->getTokenExpiration() < new \DateTime()) {
            throw new NotAllowedException ('Le token a expiré');
        }

        $inscriptionPlongeur->setVerificationToken(null);
        $inscriptionPlongeur->setTokenExpiration(null);
        $this->entityManager->flush();

        $this->createPlongeur($inscriptionPlongeur, $plainPassword);
    }
}

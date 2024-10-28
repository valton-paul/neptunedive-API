<?php

namespace App\Command;

use App\Entity\Plongeur;
use App\Enum\NiveauMoniteur;
use App\Repository\PlongeurRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\UtilisateurManager;
use App\Service\UtilisateurManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'make:supervisor',
    description: 'Give supervisor permissions',
)]
class MakeSupervisorCommand extends Command
{

    public function __construct(
        private ManagerRegistry             $doctrine,
        private UserPasswordHasherInterface $passwordHasher,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, "Diver's email")
            ->addOption("em", mode: InputOption::VALUE_OPTIONAL, description: "Entity Manager to use");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('email');
        $em = $input->getOption('em');
        $io->comment("using $em");
        try {
            $entityManager = $this->doctrine->getManager($em ?: 'default');
            $plongeurRepository = $this->doctrine->getRepository(Plongeur::class, $em ?: "default");
            $utilisateurManager = new UtilisateurManager($this->passwordHasher, $plongeurRepository, $entityManager);
            if ($arg1) {
                $plongeur = $plongeurRepository->findOneBy(['email' => $arg1]);
                if (!$plongeur) {
                    $io->error("Diver's email $arg1 not found");
                    return Command::FAILURE;
                }
                $utilisateurManager->makeSupervisor($plongeur, NiveauMoniteur::E1);
            }
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}

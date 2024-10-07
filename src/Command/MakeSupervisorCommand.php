<?php

namespace App\Command;

use App\Enum\NiveauMoniteur;
use App\Repository\PlongeurRepository;
use App\Service\UtilisateurManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'make:supervisor',
    description: 'Give supervisor permissions',
)]
class MakeSupervisorCommand extends Command
{
    public function __construct(
        private PlongeurRepository $plongeurRepository,
        private UtilisateurManager $utilisateurManager,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, "Diver's email");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('email');

        try {
            if ($arg1) {
                $plongeur = $this->plongeurRepository->findOneBy(['email' => $arg1]);
                if (!$plongeur) {
                    $io->error("Diver's email $arg1 not found");
                    return Command::FAILURE;
                }
                $this->utilisateurManager->makeSupervisor($plongeur, NiveauMoniteur::E1);
            }
        }catch (\Exception $exception){
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}

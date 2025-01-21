<?php

namespace App\Command;

use App\Service\SortieManager;
use App\Service\SortieManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'cron:ImportAllEvent',
    description: 'Import toutes les sorties depuis Spond',
    aliases: ['c:iae'],
)]
class ImportAllEventCommand extends Command
{
    public function __construct(
        private SortieManagerInterface $sortieManager
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('option1')) {
            // ...
        }
        try {
            $this->sortieManager->importAllSortie();
        } catch (\Exception $exception) {
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}

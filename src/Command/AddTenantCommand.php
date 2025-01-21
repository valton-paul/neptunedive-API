<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:add-tenant',
    description: 'Add a new tenant for a diving club',
)]
class AddTenantCommand extends Command
{
    protected static $defaultName = 'app:add-tenant';

    protected function configure(): void
    {
        $this
            ->setDescription('Ajoute un nouveau tenant (nom du club)')
            ->addArgument('tenantName', InputArgument::REQUIRED, 'Le nom du tenant à ajouter');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tenantName = $input->getArgument('tenantName');

        if (!$this->isTenantNameValid($tenantName)) {
            $output->writeln('<error>Le nom du tenant contient des caractères invalides.</error>');
            return Command::FAILURE;
        }

        $this->createEnvFile($tenantName);

        $this->updateApacheConfig($tenantName);

        $this->createTenantDatabase($tenantName, $output);

        $this->runMigrations($tenantName, $output);

        $output->writeln("<info>Le tenant '$tenantName' a été ajouté avec succès !</info>");

        return Command::SUCCESS;
    }

    private function isTenantNameValid(string $tenantName): bool
    {
        return preg_match('/^[a-z0-9_-]+$/i', $tenantName);
    }

    private function updateApacheConfig(string $tenantName): void
    {
        $apacheConfigPath = '/etc/apache2/sites-available/000-default.conf';
        $config = file_get_contents($apacheConfigPath);

        $configApache =
            <<<EOT
                <VirtualHost *:80>
                    ServerName $tenantName.neptunedive.local
                    DocumentRoot /var/www/html/neptunedive/public
                    SetEnv APP_ENV $tenantName
                
                    <Directory /var/www/html/neptunedive/public>
                        AllowOverride All
                        Require all granted
                    </Directory>
                </VirtualHost>
            EOT;

        $config .= "\n" . $configApache;
        file_put_contents($apacheConfigPath, $config);
    }

    private function createEnvFile(string $tenantName): void
    {
        $tenantEnvPath = __DIR__ . '/../../.env.' . $tenantName;
        $envContent =
            <<<EOT
                APP_ENV=$tenantName
                DATABASE_URL=mysql://root:root@db:3306/$tenantName
            EOT;

        file_put_contents($tenantEnvPath, $envContent);
    }

    private function createTenantDatabase(string $tenantName, OutputInterface $output): void
    {
        $application = $this->getApplication();
        $command = $application->find('doctrine:database:create');

        $args = new ArrayInput([
            '--env' => $tenantName,
        ]);

        $outputBuffer = new BufferedOutput();
        try {
            $command->run($args, $outputBuffer);
        } catch (ExceptionInterface $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        $output->writeln($outputBuffer->fetch());
        $output->writeln("<info>Base de données pour '$tenantName' créée avec succès.</info>");
    }

    private function runMigrations(string $tenantName, OutputInterface $output): void
    {
        $application = $this->getApplication();
        $command = $application->find('doctrine:migrations:migrate');

        $args = new ArrayInput([
            '--env' => $tenantName,
            '--no-interaction' => true,
        ]);

        $outputBuffer = new BufferedOutput();
        try {
            $command->run($args, $outputBuffer);
        } catch (ExceptionInterface $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        $output->writeln($outputBuffer->fetch());
    }
}
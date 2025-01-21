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
use Symfony\Component\Dotenv\Dotenv;

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
        $tenantName = trim($input->getArgument('tenantName'));

        if (!$this->isTenantNameValid($tenantName)) {
            $output->writeln('<error>Le nom du tenant contient des caractères invalides.</error>');
            return Command::FAILURE;
        }

        $output->writeln('<info>.env creation...</info>');
        $this->createEnvFile($tenantName);

        $output->writeln('<info>Apache configuration...</info>');
        $this->updateApacheConfig($tenantName);

        $this->createTenantDatabase($tenantName, $output);

        $output->writeln("<info>Migrations running...</info>");
        $this->makeMigration($tenantName, $output);
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

        $config .= "\n" . "\n" . $configApache;
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
        $dotenv = new Dotenv();
        $envFile = __DIR__ . '/../../.env.' . $tenantName;

        if (!file_exists($envFile)) {
            throw new \RuntimeException("Le fichier .env.$tenantName est introuvable.");
        }

        $dotenv->overload($envFile);

        $application = $this->getApplication();
        $command = $application->find('doctrine:database:create');

        $args = new ArrayInput([
            '--if-not-exists' => true,
            '--env' => $tenantName,
        ]);

        $outputBuffer = new BufferedOutput();
        try {
            $returnCode = $command->run($args, $outputBuffer);
        } catch (ExceptionInterface $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        if ($returnCode === Command::SUCCESS) {
            $output->writeln($outputBuffer->fetch());
        } else {
            $output->writeln("<error>Erreur lors de la création de la base de données pour '$tenantName'.</error>");
        }
    }

    private function makeMigration(string $tenantName, OutputInterface $output): void
    {
        $command = 'php bin/console doctrine:migrations:diff --env=' . $tenantName . ' --no-interaction';

        $outputBuffer = shell_exec($command);

        if (strpos($outputBuffer, 'No changes detected') !== false) {
            $output->writeln('<info>Aucun changement de schéma détecté, aucune migration à générer.</info>');
        } else {
            $output->writeln('<info>Migration générée avec succès pour le tenant \'' . $tenantName . '\'.</info>');
        }
    }

    private function runMigrations(string $tenantName, OutputInterface $output): void
    {
        $command = 'php bin/console doctrine:database:create --env=' . $tenantName . ' --no-interaction';
        shell_exec($command);
    }
}
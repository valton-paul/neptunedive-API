<?php
namespace App\EventListener;

use App\DBAL\MultiDbConnectionWrapper;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;

class DatabaseSwitcherListener
{
    private $em;
    private $requestStack;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function onKernelController(ControllerEvent $event)
    {
        // Get the current request
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            throw new BadRequestException("No request found");
        }

        // Extract the host or path to decide the database
        $host = $request->getHost(); // e.g., upc.localhost or lol.localhost
        // You can also use $request->getPathInfo() to get the path like /xxx
        $dbName = $this->determineDatabase($host);

        // Get the Doctrine connection and switch databases
        $connection = $this->em->getConnection();
        if ($connection instanceof MultiDbConnectionWrapper) {
            $connection->selectDatabase($dbName);
        }
    }

    private function determineDatabase(string $host): string
    {
        //TODO faire en sorte que ça soit un fichier de config pour le switch case
        // You can add logic to map the host to a specific database
        switch ($host) {
            case 'upc.localhost':
                return 'upc_db';
            case 'lol.localhost':
                return 'lol_db';
            case 'localhost':
                return 'neptunedive';
            default:
                throw new \RuntimeException('Unknown host: ' . $host);
        }
    }
}

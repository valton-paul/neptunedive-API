<?php

namespace App\Service;

use App\Entity\DonneeClub;
use App\Exception\NotFoundException;
use App\Repository\DonneeClubRepository;
use App\Service\DonneeClubManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DonneeClubManager implements DonneeClubManagerInterface
{
    public function __construct(
        private DonneeClubRepository $donneeClubRepository,
        #[Autowire('%app.encryption_key%')]
        private string $encryptionKey
    )
    {
    }


    /**
     * @inheritDoc
     */
    function encryptPassword(string $plainPassword): string
    {
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encryptedData = openssl_encrypt($plainPassword,'AES-256-CBC', $this->encryptionKey,
            0,$iv);
        return base64_encode($iv . $encryptedData);
    }

    /**
     * @inheritDoc
     */
    function decryptPassword(string $encryptedPassword): string
    {
        $decodedData = base64_decode($encryptedPassword);
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($decodedData, 0, $ivLength); // Récupérez l'IV
        $encryptedData = substr($decodedData, $ivLength); // Récupérez les données chiffrées

        return openssl_decrypt(
            $encryptedData,
            'AES-256-CBC',
            $this->encryptionKey,
            0,
            $iv
        );
    }

    /**
     * @inheritDoc
     */
    function getDonneeClub(): DonneeClub
    {
        $donneeClubBD = $this->donneeClubRepository->findAll();
        if (empty($donneeClubBD) || !$donneeClubBD[0]->getEmailSpond()) {
            throw new NotFoundException('No credentials found');
        }
        return $donneeClubBD[0];
    }


    function findGroupIdSpond($username, $password, $groupName): string
    {
        $scriptPath = __DIR__ . '/../ScriptPython/groupIdFinder.py';
        $escapedUsername = escapeshellarg($username);
        $escapedPassword = escapeshellarg($password);
        $escapedGroupName = escapeshellarg(trim($groupName));

        exec("python3 $scriptPath --username $escapedUsername --password $escapedPassword --group_name $escapedGroupName 2>&1", $output);

        if (str_contains($output[0], 'Error')) {
            if (str_contains($output[0], 'group')) {
                throw new NotFoundException('Groupe introuvable');
            }
            throw new \Exception($output[0]);
        }
        return $output[0];
    }
}
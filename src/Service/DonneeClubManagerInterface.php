<?php

namespace App\Service;

use App\Entity\DonneeClub;
use App\Exception\NotFoundException;

interface DonneeClubManagerInterface
{
    /**
     * @param string $plainPassword
     * @return string
     */
    function encryptPassword(string $plainPassword): string;

    /**
     * @param string $encryptedPassword
     * @return string
     */
    function decryptPassword(string $encryptedPassword): string;

    /**
     * @return DonneeClub
     */
    function getDonneeClub(): DonneeClub;

    /**
     * @param $username
     * @param $password
     * @param $groupName
     * @return string
     * @throws NotFoundException
     * @throws \Exception
     */
    function findGroupIdSpond($username, $password, $groupName): string;


}
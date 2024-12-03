<?php

namespace App\State\Lieu;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\InscriptionSortieRepository;
use App\Repository\LieuRepository;

class PhotoProvider implements ProviderInterface
{
    public function __construct(
        private LieuRepository $lieuRepository,
    )
    {}
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $lieux = $this->lieuRepository->findAll();

        foreach ($lieux as $lieu) {
            if ($lieu->getPhoto()) {
                $lieu->setPhoto(
                    sprintf(
                        'data:image/png;base64,%s',
                        base64_encode(stream_get_contents($lieu->getPhotoResource()))
                    )
                );
            }
        }

        return $lieux;
    }
}

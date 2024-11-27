<?php

namespace App\Encoder;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

final class MultipartDecoder implements DecoderInterface
{
    public const FORMAT = 'multipart';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function decode(string $data, string $format, array $context = []): ?array
    {
        // Récupère la requête courante à partir du RequestStack.
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return null;
        }

        // Récupère les données de la requête (les champs texte).
        $requestData = $request->request->all();

        $files = $request->files->all();

        if (isset($files['photo']) && $files['photo'] instanceof UploadedFile) {
            $uploadedFile = $files['photo']; // Récupère l'instance UploadedFile pour le fichier 'photo'.

            // Ouvre le fichier téléchargé en mode lecture binaire (rb = read binary).
            $fileStream = fopen($uploadedFile->getPathname(), 'rb');

            if (!$fileStream) {
                throw new \RuntimeException('Failed to open the uploaded file as a stream.');
            }

            // Ajoute le flux du fichier (resource) dans les données à traiter.
            $requestData['photo'] = $fileStream;
        } else {
            throw new \InvalidArgumentException('The photo field is required and must be a valid file.');
        }

        return $requestData;
    }

    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}

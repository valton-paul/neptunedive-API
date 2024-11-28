<?php
// api/src/Encoder/MultipartDecoder.php

namespace App\Encoder;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

final class MultipartDecoder implements DecoderInterface
{
    public const FORMAT = 'multipart';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function decode(string $data, string $format, array $context = []): ?array
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return null;
        }

        $requestData = $request->request->all();
        $files = $request->files->all();

        if (isset($files['photo']) && $files['photo'] instanceof UploadedFile) {
            $uploadedFile = $files['photo'];

            $stream = fopen($uploadedFile->getPathname(), 'rb');

            if ($stream === false) {
                throw new \RuntimeException('Unable to open file stream for photo.');
            }

            $requestData['photo'] = $stream;
        } else {
            throw new \InvalidArgumentException('The photo field is required.');
        }

        return $requestData;
    }


    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
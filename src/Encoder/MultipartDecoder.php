<?php

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

        foreach ($files as $key => $file) {
            if ($file instanceof UploadedFile) {
                $stream = fopen($file->getPathname(), 'rb');

                if ($stream === false) {
                    throw new \RuntimeException(sprintf('Unable to open file stream for key "%s".', $key));
                }

                $requestData[$key] = $stream;
            } else {
                throw new \InvalidArgumentException(sprintf('The field "%s" must be a valid file.', $key));
            }
        }

        return $requestData;
    }



    public function supportsDecoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
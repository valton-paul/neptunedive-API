<?php

namespace App\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Palanquee;
use App\Entity\Sortie;
use App\Entity\Plongeur;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class PalanqueeDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    // On injecte le service qui permet de convertir un id en IRI.
    public function __construct(private IriConverterInterface $iriConverter)
    {}

    // On indique dans cette méthode quels sont les objets gérés par notre dénormalizer
    public function getSupportedTypes(?string $format): array
    {
        return [
            // Paramètres obligatoires
            Palanquee::class => true,
        ];
    }

    /*
    Cette méthode sert à déterminer si notre dénormaliseur doit être appliqué ou non :

    - Il faut que le format de la requête soit json ou jsonld.
    - Il faut que le type de l'entité soit Palanquee.
    - Il faut que les champs "sortie" et "encadrant" soient présents dans le payload.
    */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return \in_array($format, ['json', 'jsonld'], true) &&
            is_a($type, Palanquee::class, true) &&
            !empty($data['sortie']) && // Vérifie que la sortie est présente
            !isset($context[__CLASS__]); // Empêche une boucle infinie
    }

    // Cette méthode convertit les identifiants simples en IRI puis reprend le processus normal de dénormalisation
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        // Convertir le champ "sortie" contenant un ID simple en IRI
        if (isset($data['sortie'])) {
            $data['sortie'] = $this->iriConverter->getIriFromResource(resource: Sortie::class, context: ['uri_variables' => ['id' => $data['sortie']]]);
        }

        // Convertir le champ "encadrant" contenant un ID simple en IRI

        if (isset($data['encadrant'])) {
            $data['encadrant'] = $this->iriConverter->getIriFromResource(resource: Plongeur::class, context: ['uri_variables' => ['id' => $data['encadrant']]]);
        }

        // On reprend le processus de dénormalisation
        return $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
    }
}

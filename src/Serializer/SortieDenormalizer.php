<?php

namespace App\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Commentaire;
use App\Entity\Lieu;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SortieDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    //On injecte le service qui permet de convertir un id en IRI.
    public function __construct(private IriConverterInterface $iriConverter)
    {
    }

    //On indique dans cette méthode quel sont les objets gérés par notre denormalizer
    public function getSupportedTypes(?string $format): array
    {
        return [
            //Paramètres obligatoires
            'directeurPlongee' => Plongeur::class,
            'securiteSurface' => Plongeur::class,
            'idLieu' => Lieu::class,
            //On indique que cette classe permet seulement de gérer la dénormalisation d'un utilisateur
            Sortie::class => true
        ];
    }

    /*
    Cette méthode sert à déterminer si notre dénormaliseur doit être appliqué ou non :

    - Il faut que le format de la requête soit json ou jsonld (on peut éventuellement ne pas préciser cette condition)
    - Il faut que le type de l'entité soit Utilisateur
    - Il faut que la ville soit présente dans le payload
    */
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return \in_array($format, ['json', 'jsonld'], true) &&
            is_a($type, Sortie::class, true) &&
            !empty($data['directeurPlongee']) &&
            !empty($data['securiteSurface']) &&
            !empty($data['idLieu']) &&
            !isset($context[__CLASS__]);
    }

    //Cette méthode convertit l'identifiant simple en IRI puis reprend le processus normal de dénormalisation
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //Convertir le champ $data["ville"] contenant un id simple en IRI
        $data['directeurPlongee'] = $this->iriConverter->getIriFromResource(resource: Plongeur::class, context: ['uri_variables' => ['id' => $data['directeurPlongee']]]);
        $data['securiteSurface'] = $this->iriConverter->getIriFromResource(resource: Plongeur::class, context: ['uri_variables' => ['id' => $data['securiteSurface']]]);
        $data['idLieu'] = $this->iriConverter->getIriFromResource(resource: Lieu::class, context: ['uri_variables' => ['id' => $data['idLieu']]]);
        //On reprend le processus de dénormalisation
        return $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
    }
}
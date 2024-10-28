<?php
namespace App\Form\Sortie;

use App\Entity\Bateau;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Accéder aux entités passées via les options
        $moniteurs = $options['moniteurs'];
        $bateaux = $options['bateaux'];

        $builder
            ->add('type')
            ->add('dateSortie', null, [
                'widget' => 'single_text',
            ])
            ->add('lieu')
            ->add('creneau', ChoiceType::class, [
                'choices' => [
                    'Matin' => Creneaux::MATIN,
                    'Après-midi' => Creneaux::APRES_MIDI,
                    'Soir' => Creneaux::NUIT,
                ],
            ])
            ->add('securiteSurface', ChoiceType::class, [
                'choices' => $moniteurs, // Utiliser les moniteurs passés en option
                'choice_label' => function (Plongeur $moniteur) {
                    return $moniteur->getPrenom() . ' ' . $moniteur->getNom();
                },
            ])
            ->add('bateau', ChoiceType::class, [
                'choices' => $bateaux, // Utiliser les bateaux passés en option
                'choice_label' => function (Bateau $bateau) {
                    return $bateau->getImmatriculation() . ' (' . $bateau->getNbPlaces() . ' places)';
                },
            ])
            ->add('piloteBateau', ChoiceType::class, [
                'choices' => $moniteurs, // Utiliser les moniteurs passés en option
                'choice_label' => function (Plongeur $moniteur) {
                    return $moniteur->getPrenom() . ' ' . $moniteur->getNom();
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            // Ajouter des options pour les entités passées au formulaire
            'moniteurs' => [],
            'bateaux' => [],
        ]);
    }
}

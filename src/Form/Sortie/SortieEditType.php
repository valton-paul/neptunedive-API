<?php
namespace App\Form\Sortie;

use App\Entity\Bateau;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupérer les options passées au formulaire
        $moniteurs = $options['moniteurs'];
        $bateaux = $options['bateaux'];
        $sortie = $options['data'];

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
                'choices' => $moniteurs,
                'choice_label' => function (Plongeur $moniteur) {
                    return $moniteur->getPrenom() . ' ' . $moniteur->getNom();
                },
                'preferred_choices' => [$sortie->getSecuriteSurface()],
            ])
            ->add('bateau', ChoiceType::class, [
                'choices' => $bateaux,
                'choice_label' => function (Bateau $bateau) {
                    return $bateau->getImmatriculation() . ' (' . $bateau->getNbPlaces() . ' places)';
                },
            ])
            ->add('piloteBateau', ChoiceType::class, [
                'choices' => $moniteurs,
                'choice_label' => function (Plongeur $moniteur) {
                    return $moniteur->getPrenom() . ' ' . $moniteur->getNom();
                },
                'preferred_choices' => [$sortie->getPiloteBateau()],
            ])
            ->add('directeurPlongee', ChoiceType::class, [
                'choices' => $moniteurs,
                'choice_label' => function (Plongeur $moniteur) {
                    return $moniteur->getPrenom() . ' ' . $moniteur->getNom();
                },
                'preferred_choices' => [$sortie->getDirecteurPlongee()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
        $resolver->setDefined(['moniteurs', 'bateaux']);
    }
}

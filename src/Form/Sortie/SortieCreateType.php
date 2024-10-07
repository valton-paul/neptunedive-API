<?php

namespace App\Form\Sortie;

use App\Entity\Bateau;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use App\Enum\Creneaux;
use App\Repository\PlongeurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
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
            ->add('securiteSurface', EntityType::class, [
                'class' => Plongeur::class,
                'choice_label' => function (Plongeur $moniteur) {
                    return $moniteur->getPrenom() . ' ' . $moniteur->getNom();
                },
                'query_builder' => function (PlongeurRepository $repo) {
                    return $repo->createQueryBuilder('m')
                        ->where('m.isMoniteur LIKE :moniteur')
                        ->setParameter('moniteur', 1);
                },
            ])
            /*->add('bateau', EntityType::class, [
                'class' => Bateau::class,
                'choice_label' => 'immatriculation',
            ])*/
            ->add('piloteBateau', EntityType::class, [
                'class' => Plongeur::class,
                'choice_label' => function (Plongeur $moniteur) {
                    return $moniteur->getPrenom() . ' ' . $moniteur->getNom();
                },
                'query_builder' => function (PlongeurRepository $repo) {
                    return $repo->createQueryBuilder('m')
                        ->where('m.isMoniteur LIKE :moniteur')
                        ->setParameter('moniteur', 1);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}

<?php

namespace App\Form\Sortie;

use App\Entity\Bateau;
use App\Entity\Moniteur;
use App\Entity\Plongeur;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type')
            ->add('dateSortie', null, [
                'widget' => 'single_text',
            ])
            ->add('lieu')
            ->add('creneau')
            ->add('directeurPlongee', EntityType::class, [
                'class' => Moniteur::class,
                'choice_label' => 'id',
            ])
            ->add('securiteSurface', EntityType::class, [
                'class' => Moniteur::class,
                'choice_label' => 'id',
            ])
            ->add('bateau', EntityType::class, [
                'class' => Bateau::class,
                'choice_label' => 'id',
            ])
            ->add('piloteBateau', EntityType::class, [
                'class' => Moniteur::class,
                'choice_label' => 'id',
            ])
            ->add('inscriptions', EntityType::class, [
                'class' => Plongeur::class,
                'choice_label' => 'id',
                'multiple' => true,
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

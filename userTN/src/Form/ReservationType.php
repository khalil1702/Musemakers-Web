<?php

namespace App\Form;

use App\Entity\Exposition;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Reservation1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom_user', // Le nom de la propriété d'affichage de l'utilisateur
                'label' => 'Utilisateur'
            ])
            ->add('exposition', EntityType::class, [
                'class' => Exposition::class,
                'choice_label' => 'nom', // Le nom de la propriété d'affichage de l'exposition
                'label' => 'Exposition'
            ])
            ->add('ticketsNumber', IntegerType::class, [
                'label' => 'Nombre de tickets'
            ])
            ->add('dateReser', DateTimeType::class, [
                'label' => 'Date de réservation',
                'widget' => 'single_text',
            ])
            ->add('accessByAdmin', ChoiceType::class, [
                'label' => 'Accès administrateur',
                'choices' => [
                    'Oui' => 1,
                    'Non' => 0
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
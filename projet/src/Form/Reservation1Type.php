<?php

namespace App\Form;

use App\Entity\Exposition;
use App\Entity\Reservation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType as TypeEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EntityType;


class Reservation1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
    ->add('ticketsNumber')
    ->add('accessByAdmin')
    ->add('dateReser')
    ->add('user', TypeEntityType::class, [
        'class' => User::class,
        'choice_label' => 'nom_user', // Replace 'username' with the property you want to display for the User entity
    ])
    ->add('Exposition', TypeEntityType::class, [
        'class' => Exposition::class,
        'choice_label' => 'nom', // Replace 'username' with the property you want to display for the User entity
    ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}

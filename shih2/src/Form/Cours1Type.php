<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Cours1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titreCours')
            ->add('descriCours')
            ->add('datedebutCours')
            ->add('datefinCours')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'idUser', // Remplacez 'username' par la propriété appropriée de l'utilisateur à afficher dans le formulaire
                
            ]);
           /* ->add('idUser', EntityType::class, [
                // looks for choices from this entity
                'class' => User::class,
                // uses the User.username property as the visible option string
                'choice_label' => 'id',
                // used to render a select box, check boxes or radios
                'multiple' => false,
                'expanded' => false,
            ])*/

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}

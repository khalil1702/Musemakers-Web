<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class User2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomUser')
            ->add('prenomUser')
            ->add('email')
            ->add('mdp')
            ->add('numTel')
            ->add('dateDeNaissance')
            ->add('cartepro')
            ->add('role')
            ->add('status')
            ->add('sexe')
            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false, // make it optional
                'data_class' => null, // This is important, it prevents Symfony from trying to convert the uploaded file into an Exposition object
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

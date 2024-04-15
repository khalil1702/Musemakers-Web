<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class User1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
       
        $builder
        ->add('nomUser', null, [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Votre nom doit comporter au moins {{ limit }} caractères.']),
            ],
        ])
        ->add('prenomUser', null, [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 2, 'minMessage' => 'Votre prénom doit comporter au moins {{ limit }} caractères.']),
            ],
        ])
            ->add('email')
            ->add('mdp', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('numTel')
            ->add('dateDeNaissance')
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme',
                ],
                'expanded' => true, // Afficher comme des boutons radio
                'multiple' => false, // Sélection unique
                'required' => true, // Rendre la sélection obligatoire si nécessaire
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Artiste' => 'ROLE_ARTISTE',
                    'Client' => 'ROLE_USER',
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'data_class' => null,
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

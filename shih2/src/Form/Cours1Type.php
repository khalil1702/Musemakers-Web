<?php

namespace App\Form;

use App\Entity\Cours;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Regex;

class Cours1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titreCours', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Titre du cours'],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z]+$/',
                        'message' => 'Le titre du cours doit contenir uniquement des lettres de l\'alphabet.'
                    ]),
                ],
            ])
            ->add('descriCours', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Description du cours']
            ])
            ->add('datedebutCours', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date de début du cours ne peut pas être vide']),
                ],
                'required' => true,
            ])
            ->add('datefinCours', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date de fin du cours ne peut pas être vide']),
                    new GreaterThan([
                        'propertyPath' => 'parent.data.datedebutCours',
                        'message' => 'La date de fin doit être ultérieure à la date de début.',
                    ]),
                ],
                'required' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'idUser',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}

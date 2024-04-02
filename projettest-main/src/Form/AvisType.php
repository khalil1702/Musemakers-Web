<?php

namespace App\Form;

use App\Entity\Avis;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;
use App\Entity\Oeuvre;
use Symfony\Component\Validator\Constraints as Assert;

class AvisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentaire', Type\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le commentaire ne peut pas être vide']),
                ],
            ])
            ->add('note', Type\IntegerType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La note est obligatoire']),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 5,
                        'minMessage' => 'La note doit être au moins {{ 0 }}',
                        'maxMessage' => 'La note ne peut pas dépasser {{ 5t }}',
                    ]),
                ],
            ])
            ->add('likes', Type\IntegerType::class, [
                'required' => false,
            ])
            ->add('dislikes', Type\IntegerType::class, [
                'required' => false,
            ])
            ->add('favoris', Type\CheckboxType::class, [
                'required' => false,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nomUser', // Remplacez 'nom' par la propriété qui contient le nom de l'utilisateur dans votre entité User
            ])
            ->add('oeuvre',EntityType::class, [
                'class' => Oeuvre::class,
                'choice_label' => 'nomOeuvre', // Remplacez 'nom' par la propriété qui contient le nom de l'utilisateur dans votre entité User
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}

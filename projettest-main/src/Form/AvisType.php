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
use Sbyaute\StarRatingBundle\Form\StarRatingType;

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
            ->add('note', StarRatingType::class, [
                
                'stars' => 5,
                //'integer' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La note est obligatoire']), // Contrainte de validation NotBlank
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
          
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Avis::class,
        ]);
    }
}

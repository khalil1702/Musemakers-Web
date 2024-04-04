<?php

namespace App\Form;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('descrirec')
            ->add('daterec', DateType::class, [
                'disabled' => true, // Rendre le champ non modifiable
            ])
            ->add('categorierec', ChoiceType::class, [
                'choices' => [
                    'Produits' => 'Produits',
                    'Service Client' => 'Service Client',
                    'Problème Technique' => 'Problème Technique',
                ],
                'placeholder' => 'Choisir une catégorie', // Optionnel : affichez un libellé par défaut
                'required' => true, // Optionnel : spécifiez si la sélection d'une catégorie est obligatoire
            ])
            
            ->add('statutrec', ChoiceType::class, [
                'choices' => [
                    'Resolue' => 'Resolue',
                    'Fermée' => 'Fermée',
                    'En Cours' => 'En Cours',
                ],
                'placeholder' => 'Choisir le statut', // Optionnel : affichez un libellé par défaut
                'required' => true, // Optionnel : spécifiez si la sélection d'une catégorie est obligatoire
            ])
            
            ->add('idu', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'prenomUser', // Le champ de l'entité à afficher dans le formulaire
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
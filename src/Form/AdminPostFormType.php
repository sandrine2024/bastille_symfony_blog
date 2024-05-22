<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AdminPostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'placeholder' => "Choississez une catégorie"

            ])
            ->add('imageFile', VichImageType::class, [ // Ajoute un champ de formulaire pour le fichier image
                'required' => false, // Le champ n'est pas requis
                'allow_delete' => true, // Permet à l'utilisateur de supprimer l'image
                'delete_label' => "Supprimer l'image existante", // Affiche une étiquette pour l'option de suppression
                'download_label' => false, // N'affiche pas d'étiquette pour le téléchargement
                'download_uri' => false, // Désactive l'URI de téléchargement
                'image_uri' => false, // Désactive l'URI de l'image
                'imagine_pattern' => '', // Désactive le modèle d'imagination (filtre)
                // 'imagine_pattern' => 'admin_post'
                'asset_helper' => true, // Utilise l'assistant d'actifs pour générer l'URI
            ])
            ->add('content', TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}

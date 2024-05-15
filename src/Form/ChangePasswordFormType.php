<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class ChangePasswordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Le nouveau mot de passe est obligatoire.',
                        ]),
                        new Length([
                            'min' => 12,
                            'max' => 255,
                            'minMessage' => 'Le mot de passe doit contenir au minimun {{ limit }} caractères',
                            'maxMessage' => 'Le mot de passe doit contenir au maximun {{ limit }} caractères',
                        ]),
                        new Regex([
                            "pattern" => "/^(?=.*[a-zà-ÿ])(?=.*[A-ZÀ-Ỳ])(?=.*[0-9])(?=.*[^a-zà-ÿA-ZÀ-Ỳ0-9]).{11,255}$/",
                            "match" => true,
                            "message" => "Le mot de passe doit contentir au moins une lettre miniscule, majuscule, un chiffre et un caractère spécial.",
                        ]),
                        new NotCompromisedPassword([
                            "message" => "Ce mot de passe est facilement piratable, veuillez en choisir un autre."
                        ]),
                    ],
                    'label' => 'New password',
                ],
                'invalid_message' => 'Le mot de passe doit çetre identique à sa confirmation.',
              // Au lieu d'être défini directement sur l'objet,
                // ceci est lu et codé dans le contrôleur
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

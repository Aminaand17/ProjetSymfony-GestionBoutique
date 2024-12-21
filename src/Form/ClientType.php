<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('surname', TextType::class, [
                'label' => 'Prénom et Nom',
                'attr' => [
                    'placeholder' => 'Entrez le prénom et nom du client',
                    'class' => 'border border-gray-300 rounded px-4 py-2',
                ],
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => 'Entrez le numéro de téléphone',
                    'class' => 'border border-gray-300 rounded px-4 py-2',
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'attr' => [
                    'placeholder' => 'Entrez l\'adresse du client',
                    'class' => 'border border-gray-300 rounded px-4 py-2',
                ],
            ])
            ->add('createAccount', CheckboxType::class, [
                'label' => 'Créer un compte utilisateur',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'ml-3',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}

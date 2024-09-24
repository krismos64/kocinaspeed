<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Range;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Champs pour les visiteurs
        $builder
            ->add('visitorName', null, [
                'label' => 'Votre nom',
                'required' => false,
            ])
            ->add('visitorEmail', EmailType::class, [
                'label' => 'Votre email',
                'required' => false,
            ])
            ->add('rating', ChoiceType::class, [
                'label' => 'Note',
                'choices'  => [
                    '⭐' => 1,
                    '⭐⭐' => 2,
                    '⭐⭐⭐' => 3,
                    '⭐⭐⭐⭐' => 4,
                    '⭐⭐⭐⭐⭐' => 5,
                ],
                'expanded' => true,
                'multiple' => false,
                'attr' => [
                    'class' => 'uk-rating',
                ],
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 5,
                        'notInRangeMessage' => 'La note doit être entre {{ min }} et {{ max }}.',
                    ]),
                ],
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'Votre avis',
            ])
            ->add('images', FileType::class, [
                'label' => 'Ajouter des images (JPEG, PNG)',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'attr' => [
                    'accept' => 'image/jpeg, image/png',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
        ]);
    }
}

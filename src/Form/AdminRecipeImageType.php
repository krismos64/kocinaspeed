<?php

namespace App\Form;

use App\Entity\RecipeImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminRecipeImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('imageFile', FileType::class, [
            'label' => 'Téléchargez une image',
            'mapped' => false,  // Ce champ n'est pas directement mappé à l'entité, donc à gérer manuellement dans le contrôleur
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RecipeImage::class,  // Assurez-vous de bien utiliser l'entité RecipeImage
        ]);
    }
}

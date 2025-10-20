<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Recette;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class RecetteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, ['label' => 'Titre de la recette'])
            ->add('description', TextareaType::class, ['label' => 'Description'])
            ->add('ingredients', TextareaType::class, ['label' => 'Ingrédients (séparés par une virgule)'])
            ->add('etapes', TextareaType::class, ['label' => 'Étapes de préparation'])
            
            // "temp" (tekil) olarak kullandığınız adları koruduk.
            ->add('tempPreparation', IntegerType::class, ['label' => 'Temps de préparation (min)'])
            ->add('tempCuisson', IntegerType::class, ['label' => 'Temps de cuisson (min)'])
            
            ->add('image', FileType::class, [
                'label' => 'Image de la recette (JPG, PNG ou WEBP)',
                // ZORUNLU: Resim yükleme Controller'da manuel olarak yönetildiği için false olmalı.
                'mapped' => false,
                // ZORUNLU: Düzenleme sırasında yeni resim yüklemek isteğe bağlı olmalı.
                'required' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/webp', // 🔹 sadece görsel seçimine izin ver
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (.jpeg, .png, .webp)',
                    ])
                ],
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}

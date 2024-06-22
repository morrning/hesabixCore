<?php

namespace App\Form;

use App\Entity\BlogCat;
use App\Entity\BlogPost;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BlogPostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class)
            ->add('intero',TextareaType::class)
            ->add(
                'body',
                CKEditorType::class,
                [
                    'filebrowsers' => [
                        'VideoUpload',
                        'VideoBrowse',
                    ],
                    'config' => array(
                        'filebrowserBrowseRoute'           => 'my_route',
                        'filebrowserBrowseRouteParameters' => array('slug' => 'my-slug'),
                        'filebrowserBrowseRouteType'       => UrlGeneratorInterface::ABSOLUTE_URL,
                    ),
                ]
            )
            ->add('img', FileType::class, [
                'label' => 'Img',

                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/gif',
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'لطفا یک فایل تصویر انتخاب کنید.',
                    ])
                ],
            ])
            ->add('cat',EntityType::class, [
                // looks for choices from this entity
                'class' => BlogCat::class,

                // uses the User.username property as the visible option string
                'choice_label' => 'label',

                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ])
            ->add('submit',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BlogPost::class,
        ]);
    }
}

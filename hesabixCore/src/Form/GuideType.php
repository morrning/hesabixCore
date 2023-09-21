<?php

namespace App\Form;

use App\Entity\GuideContent;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuideType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cat', ChoiceType::class, [
                'choices'  => [
                    'عمومی' => 'general',
                    'اشخاص (طرف حساب ها) ' => 'person',
                    'کالاها و خدمات ' => 'commodity',
                    'مدیریت حساب های بانکی،تنخواه‌گردان،صندوق‌ها و انتقال بین حساب‌ها ' => 'banks',
                    'خرید و هزینه ' => 'buy',
                    'فروش و درآمد ' => 'sell',
                    'گزارشات ' => 'reports',
                    'تنظیمات ' => 'settings',
                ],
            ])
            ->add('title',TextType::class)
            ->add('body',CKEditorType::class)
            ->add('submit',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GuideContent::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifierSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class, array(
                'label'=>'Nom de la sortie : ',
                'label_attr' => ['class'=>'nom'],
                'required'=>true
            ))
            ->add('dateHeureDebut',
                DateTimeType::class,
                [
                    'label' => 'Date et heure de la sortie :',
                    'label_attr' => ['class' => 'dateHeureDebut'],
                    'html5' => true,
                    'widget' => 'single_text',
                    'required' => true
                ])

            ->add('dateLimiteInscription',DateType::class,[
                'label'=>'Date limite de l\'inscription',
                'label_attr' => ['class' => 'dateLimiteInscription'],
                'html5'=>true,
                'widget'=>'single_text',
                'required'=>true
            ])

            ->add('nbInscriptionsMax', NumberType::class,[
                'label'=>'Nombre de place : ',
                'label_attr'=> ['class' => 'nbInscriptionsMax'],
                'html5'=> true,
                'required'=>true
            ])

            ->add('duree', NumberType::class,[
                'label'=>'Durée : ',
                'label_attr' => ['class'=>'duree'],
                'html5'=> true,
                'required'=>true
            ])

            ->add('infosSortie',TextareaType::class,[
                'label'=> 'Information sur la sortie : ',
                'label_attr' => ['class' => 'infoSortie'],
                'required'=>true
            ])

            ->add('siteOrganisateur',TextType::class,[
                'label'=> 'Campus : ',
                'label_attr' => ['class' => 'siteOrganisateur'],
                'disabled'=>true
            ])

            ->add('ville', EntityType::class, [
                'mapped'=>false,
                'label'=>'Ville :',
                'class'=>Ville::class,
                'choice_label'=>'nom',
                'placeholder'=>'--Sélectionnez une ville--',
                'choice_value'=> function (?Ville $ville){
                    return $ville ? $ville->getId() : '' ;
                },
                'data'=> $options ['ville']
            ])

            ->add('save', SubmitType::class,[
                'label'=>'Enregistrer'
            ])

            ->add('saveAndAdd', SubmitType::class,[
                'label'=>'Publier'
            ]);


        $formModifier = function (FormInterface $form, Ville $ville = null): void
        {
            $lieux = null === $ville ? [] : $ville->getLieux();
            $form->add('lieu', EntityType::class, [
                'label'=> 'Lieu : ',
                'class'=> Lieu::class,
                'placeholder'=>'--Sélectionnez un lieu--',
                'choices'=>$lieux,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier): void
            {
                $form = $event->getForm();
                $ville = $form['ville']->getData();
                $formModifier($form, $ville);
            }
        );
        $builder->get('ville')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) use ($formModifier): void
            {
                $ville = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $ville);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'ville'=> ville::class,
        ]);
    }
}
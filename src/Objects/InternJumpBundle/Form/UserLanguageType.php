<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class UserLanguageType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('spokenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced')))
                ->add('writtenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced')))
                ->add('readFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced')))
                ->add('language', 'entity', array('class' => 'ObjectsInternJumpBundle:Language'))
        ;
    }

    public function getName() {
        return 'objects_internjumpbundle_userlanguagetype';
    }

    public function getDefaultOptions(array $options) {
        $options['data_class'] = '\Objects\InternJumpBundle\Entity\UserLanguage';
        return $options;
    }

}

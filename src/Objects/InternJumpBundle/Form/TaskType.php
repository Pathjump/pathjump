<?php

namespace Objects\InternJumpBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class TaskType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('title')
                ->add('description')
                ->add('status','choice', array('choices' => array('new' => 'new','inprogress' => 'inprogress','done' => 'done')))
                ->add('startedAt')
                ->add('endedAt')
                ->add('internship')
                //->add('company')
                ->add('user')
        ;
    }
    
    public function getDefaultOptions(array $options) {
        $options['validation_groups'] = array('new');
        return $options;
    }

    public function getName() {
        return 'objects_internjumpbundle_tasktype';
    }

}

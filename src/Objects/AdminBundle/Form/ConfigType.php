<?php

namespace Objects\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ConfigType extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('consumer_key')
                ->add('consumer_secret')
                ->add('facebook_app_id')
                ->add('facebook_app_secret')
                ->add('fb_page_name')
                ->add('post_twitter', 'checkbox', array('required'  => false))
                ->add('post_facebook', 'checkbox', array('required'  => false))
                ->add('site_links_stall_time')
                ->add('site_links_fault_times')
        ;
    }

    public function getName() {
        return 'objects_adminbundle_configtype';
    }

}

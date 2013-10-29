<?php

/**
 * Description of FounderAdmin
 *
 * @author Ola
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Doctrine\ORM\EntityRepository;

class FounderAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string 
     */
    protected $baseRouteName = 'founder_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string 
     */
    protected $baseRoutePattern = 'founder';

    /**
     * this function configure the list action fields
     * @author Ola
     * @param ListMapper $listMapper 
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->add('founderName')
                ->add('founderEmail','email')
                ->add('founderImage',NULL, array('template' => 'ObjectsAdminBundle:General:founder_image.html.twig'))
                ->add('founderPosition')
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'view' => array(),
                        'edit' => array(),
                        'delete' => array(),
                    )
                ))
        ;
    }

    /**
     * this function configure the show action fields
     * @author Ahmed
     * @param ShowMapper $showMapper 
     */
    public function configureShowField(ShowMapper $showMapper) {
        $showMapper
                ->add('founderName')
                ->add('founderEmail')
                ->add('founderImage')
                ->add('founderPosition')
        ;
    }

    /**
     * this function configure the list action filters fields
     * @todo add the date filters if sonata project implemented it
     * @author Ola
     * @param DatagridMapper $datagridMapper 
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('founderName')
                ->add('founderEmail')
                ->add('founderImage')
                ->add('founderPosition')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Ola
     * @param FormMapper $formMapper 
     */
    public function configureFormFields(FormMapper $formMapper) {

        $formMapper
                ->add('founderName')
                ->add('founderEmail')
                ->add('file','file',array('required'=>false,'label' => 'FounderImage'))
                ->add('founderPosition')
        ;
    }

     //for update image 
     public function PreUpdate($founderImage){
        $founderImage->preUpload();
    }  
}

?>

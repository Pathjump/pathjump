<?php

/**
 * Description of AskedQuestionAdmin
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

class AskedQuestionAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string 
     */
    protected $baseRouteName = 'FAQ_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string 
     */
    protected $baseRoutePattern = 'FAQ';

    /**
     * this function configure the list action fields
     * @author Ola
     * @param ListMapper $listMapper 
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->add('question')
                ->add('answer')
                ->add('createdAt')
                ->add('isActive')
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
                ->add('question')
                ->add('answer')
                ->add('createdAt')
                ->add('isActive')
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
                ->add('question')
                ->add('answer')
                ->add('createdAt')
                ->add('isActive')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Ola
     * @param FormMapper $formMapper 
     */
    public function configureFormFields(FormMapper $formMapper) {

        $formMapper
                ->add('question')
                ->add('answer')
                ->add('isActive')
        ;
    }

}

?>

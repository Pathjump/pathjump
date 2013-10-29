<?php

/**
 * Description of EmploymentHistoryAdmin
 *
 * @author Ahmed
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class EmploymentHistoryAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string 
     */
    protected $baseRouteName = 'employment_history_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string 
     */
    protected $baseRoutePattern = 'employment_history';

    /**
     * this function configure the list action fields
     * @param ListMapper $listMapper 
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('user')
                ->add('title')
                ->add('description')
                ->add('startedFrom')
                ->add('endedIn')
                //->add('jobPosition')
                ->add('companyName')
                ->add('companyUrl')
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
                ->add('id')
                ->add('user')
                ->add('title')
                ->add('description')
                ->add('startedFrom')
                ->add('endedIn')
                //->add('jobPosition')
                ->add('companyName')
                ->add('companyUrl')
        ;
    }

    /**
     * this function configure the list action filters fields
     * @param DatagridMapper $datagridMapper 
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('id')
                ->add('user')
                ->add('title')
                ->add('description')
                ->add('startedFrom')
                ->add('endedIn')
                //->add('jobPosition')
                ->add('companyName')
                ->add('companyUrl')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @param FormMapper $formMapper 
     */
    public function configureFormFields(FormMapper $formMapper) {
        $formMapper
                ->with('Required Fields')
                ->add('title')
                ->add('description')
                //->add('jobPosition')
                ->add('startedFrom', 'date', array('years' => range(1940, date('Y'))))
                ->add('endedIn', 'date', array('years' => range(1940, date('Y'))))
                ->add('companyName')
                ->end()
                ->with('Not Required Fields')
                ->add('companyUrl', 'url', array('required' => FALSE))
                ->end()
        ;
    }

}

?>

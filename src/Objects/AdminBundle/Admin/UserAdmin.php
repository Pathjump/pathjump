<?php

/**
 * Description of UserAdmin
 *
 * @author Ahmed
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Doctrine\ORM\EntityRepository;

class UserAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string
     */
    protected $baseRouteName = 'user_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string
     */
    protected $baseRoutePattern = 'user';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('firstName')
                ->add('lastName')
                ->add('loginName')
                ->add('email')
                ->add('image', NULL, array('template' => 'ObjectsAdminBundle:General:list_image.html.twig'))
                ->add('userRoles')
                ->add('createdAt')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('dateOfBirth')
                ->add('url', NULL, array('template' => 'ObjectsAdminBundle:General:list_url.html.twig'))
                ->add('gender', NULL, array('template' => 'ObjectsAdminBundle:General:list_gender.html.twig'))
                ->add('locked')
                ->add('enabled')
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
     * @author Mahmoud
     * @param ShowMapper $showMapper
     */
    public function configureShowField(ShowMapper $showMapper) {
        $showMapper
                ->add('id')
                ->add('firstName')
                ->add('lastName')
                ->add('loginName')
                ->add('email')
                ->add('about')
                ->add('image', NULL, array('template' => 'ObjectsAdminBundle:General:show_image.html.twig'))
                ->add('userRoles')
                ->add('createdAt')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('dateOfBirth')
                ->add('url', NULL, array('template' => 'ObjectsAdminBundle:General:show_url.html.twig'))
                ->add('gender', NULL, array('template' => 'ObjectsAdminBundle:General:show_gender.html.twig'))
                ->add('skills')
                ->add('employmentHistories')
                ->add('locked')
                ->add('enabled')
        ;
    }

    /**
     * this function configure the list action filters fields
     * @todo add the date filters if sonata project implemented it
     * @author Mahmoud
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('id')
                ->add('firstName')
                ->add('lastName')
                ->add('loginName')
                ->add('email')
                ->add('userRoles')
                ->add('about')
                ->add('createdAt')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('dateOfBirth')
                ->add('url')
                ->add('gender')
                ->add('locked')
                ->add('enabled')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Mahmoud
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper) {
        //define the default arrays
        $countries = array();
//        $cities = array();
        $states = array();
        //check if we have a new object
        if ($this->getSubject()->getId()) {
            //set the object values
            $countries[$this->getSubject()->getCountry()] = $this->getSubject()->getCountry();
//            $cities[$this->getSubject()->getCity()] = $this->getSubject()->getCity();
            $states[$this->getSubject()->getState()] = $this->getSubject()->getState();
        }
        $formMapper
                ->with('Required Fields')
                ->add('gender', 'choice', array('choices' => array('1' => 'Male', '0' => 'Female'), 'expanded' => true, 'multiple' => false))
                ->add('firstName')
                ->add('lastName')
                ->add('loginName')
                ->add('userPassword', 'password', array('required' => false, 'attr' => array('autocomplete' => 'off')))
                ->add('email')
                ->add('country', 'choice', array(
                    'choices' => $countries,
                    'attr' => array('class' => 'chosen countrySelect')
                ))
                ->add('city')
                ->add('state', 'choice', array(
                    'choices' => $states,
                    'attr' => array('class' => 'chosen stateSelect'),
                    'required' => false
                ))
                ->add('address')
                ->add('userRoles', 'entity', array(
                    'class' => 'ObjectsUserBundle:Role',
                    'multiple' => true,
                    'query_builder' => function(EntityRepository $er) {
                        $qb = $er->createQueryBuilder('r');
                        return $qb->where($qb->expr()->notIn('r.name', array('ROLE_COMPANY', 'ROLE_NOTACTIVE_COMPANY')));
                    },
                    'attr' => array('class' => 'chosen')
                ))
                ->end()
                ->with('Not Required Fields', array('collapsed' => true))
                ->add('file', 'file', array('required' => false, 'label' => 'image'))
                ->add('dateOfBirth', 'birthday', array('required' => false))
                ->add('zipcode')
                ->add('url')
                ->add('about')
                ->add('skills', 'sonata_type_model', array('required' => false, 'attr' => array('class' => 'chosen')))
                ->add('employmentHistories', 'sonata_type_collection', array('required' => false), array(
                    'edit' => 'inline',
                    'inline' => 'table'
                ))
                ->add('educations', 'sonata_type_collection', array('required' => false), array(
                    'edit' => 'inline',
                    'inline' => 'table'
                ))
                ->add('locked', NULL, array('required' => false))
                ->add('enabled', NULL, array('required' => false))
                ->end()
                ->setHelps(array(
                    'locked' => 'to prevent the user from logging into his account',
                    'enabled' => 'uncheck to mark the user account as deleted'
                ))
        ;
    }

    /**
     * this function is used to set a different validation group for the form
     */
    public function getFormBuilder() {
        if (is_null($this->getSubject()->getId())) {
            $this->formOptions = array('validation_groups' => array('adminsignup', 'education'));
        } else {
            $this->formOptions = array('validation_groups' => array('adminedit', 'education'));
        }
        $formBuilder = parent::getFormBuilder();
        return $formBuilder;
    }

    /**
     * this function is for editing the routes of this class
     * @param RouteCollection $collection
     */
//    protected function configureRoutes(RouteCollection $collection) {
//        $collection->remove('delete');
//    }

    /**
     * @param \Objects\UserBundle\Entity\User $user
     */
    public function prePersist($user) {
        $user->setRequiredData();
        $user->setValidPassword();
    }

    /**
     * @param \Objects\UserBundle\Entity\User $user
     */
    public function preUpdate($user) {
        $user->setRequiredData();
        $user->preUpload();
        $user->setValidPassword();
    }

}

?>

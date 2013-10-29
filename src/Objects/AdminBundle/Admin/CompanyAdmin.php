<?php

/**
 * Description of CompanyAdmin
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

class CompanyAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string
     */
    protected $baseRouteName = 'company_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string
     */
    protected $baseRoutePattern = 'company';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('name')
                ->add('loginName')
                ->add('email')
                ->add('companyRoles')
                //->add('establishmentDate')
                ->add('telephone')
                ->add('fax')
                ->add('url', NULL, array('template' => 'ObjectsAdminBundle:General:list_url.html.twig'))
                ->add('facebookUrl')
                ->add('twitterUrl')
                ->add('googlePlusUrl')
                ->add('linkedInUrl')
                ->add('youtubeUrl')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('Latitude')
                ->add('Longitude')
                ->add('createdAt')
                ->add('isHome')
                ->add('locked')
                ->add('enabled')
                ->add('image', NULL, array('template' => 'ObjectsAdminBundle:General:list_image.html.twig'))
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
                ->add('name')
                ->add('loginName')
                ->add('email')
                ->add('companyRoles')
                //->add('establishmentDate')
                ->add('telephone')
                ->add('fax')
                ->add('url', NULL, array('template' => 'ObjectsAdminBundle:General:show_url.html.twig'))
                ->add('facebookUrl')
                ->add('twitterUrl')
                ->add('googlePlusUrl')
                ->add('linkedInUrl')
                ->add('youtubeUrl')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('Latitude')
                ->add('Longitude')
                ->add('createdAt')
                ->add('isHome')
                ->add('locked')
                ->add('enabled')
                ->add('image', NULL, array('template' => 'ObjectsAdminBundle:General:show_image.html.twig'))
                ->add('professions')
        ;
    }

    /**
     * this function configure the list action filters fields
     * @todo add the date filters if sonata project implemented it
     * @author Ahmed
     * @param DatagridMapper $datagridMapper
     */
    public function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('id')
                ->add('name')
                ->add('loginName')
                ->add('email')
                ->add('companyRoles')
                //->add('establishmentDate')
                ->add('telephone')
                ->add('fax')
                ->add('url')
                ->add('facebookUrl')
                ->add('twitterUrl')
                ->add('googlePlusUrl')
                ->add('linkedInUrl')
                ->add('youtubeUrl')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('createdAt')
                ->add('Latitude')
                ->add('Longitude')
                ->add('isHome')
                ->add('locked')
                ->add('enabled')
                ->add('professions')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Ahmed
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper) {
        $currentDate = new \DateTime();
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
                ->add('name')
                ->add('zipcode','text',array('attr' => array('class' => 'zipcode')))
                ->add('Longitude','text',array('attr' => array('class' => 'longitude')))
                ->add('Latitude','text',array('attr' => array('class' => 'latitude')))
                ->add('loginName')
                ->add('userPassword', 'password', array('required' => false, 'attr' => array('autocomplete' => 'off'), 'label' => 'password'))
                ->add('email')
                ->add('companyRoles', 'entity', array(
                    'class' => 'ObjectsUserBundle:Role',
                    'multiple' => true,
                    'query_builder' => function(EntityRepository $er) {
                        $qb = $er->createQueryBuilder('r');
                        return $qb->where($qb->expr()->in('r.name', array('ROLE_COMPANY', 'ROLE_NOTACTIVE_COMPANY')));
                    },
                    'attr' => array('class' => 'chosen')))
                //->add('establishmentDate', 'date', array('years' => range('1900', $currentDate->format('Y'))))

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
                ->add('professions', 'sonata_type_model', array('attr' => array('class' => 'chosen')))
                ->end()
                ->with('Not Required Fields', array('collapsed' => true))
                ->add('telephone')
                ->add('fax')
                ->add('url')
                ->add('facebookUrl')
                ->add('twitterUrl')
                ->add('googlePlusUrl')
                ->add('linkedInUrl')
                ->add('youtubeUrl')
                ->add('file', 'file', array('required' => false, 'label' => 'image'))
                ->add('locked', NULL, array('required' => false))
                ->add('enabled', NULL, array('required' => false))
                ->add('isHome', NULL, array('required' => false))
                ->end()
                ->setHelps(array(
                    'locked' => 'to prevent the user from logging into his account',
                    'enabled' => 'uncheck to mark the company account as deleted'
                ))
        ;
    }

    /**
     * @author Mahmoud
     * @param \Objects\InternJumpBundle\Entity\Company $company
     */
    public function preUpdate($company) {
        $company->preUpload();
        $company->setValidPassword();
    }

    public function prePersist($company) {
        $company->setValidPassword();
    }

    /**
     * this function is used to set a different validation group for the form
     */
    public function getFormBuilder() {
        if (is_null($this->getSubject()->getId())) {
            $this->formOptions = array('validation_groups' => 'adminsignup');
        } else {
            $this->formOptions = array('validation_groups' => 'adminedit');
        }
        $formBuilder = parent::getFormBuilder();
        return $formBuilder;
    }


}

?>

<?php

/**
 * Description of InternshipAdmin
 *
 * @author Mahmoud
 */

namespace Objects\AdminBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class InternshipAdmin extends Admin {

    /**
     * this variable holds the route name prefix for this actions
     * @var string
     */
    protected $baseRouteName = 'internship_admin';

    /**
     * this variable holds the url route prefix for this actions
     * @var string
     */
    protected $baseRoutePattern = 'internship';

    /**
     * this function configure the list action fields
     * @author Ahmed
     * @param ListMapper $listMapper
     */
    public function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id')
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('minimumGPA')
                ->add('skills')
                ->add('compensation')
                ->add('keywords')
                ->add('numberOfOpenings')
                ->add('sessionPeriod')
                ->add('positionType')
                ->add('workLocation')
                ->add('Longitude')
                ->add('Latitude')
                ->add('createdAt')
                ->add('activeFrom')
                ->add('activeTo')
                ->add('active')
                ->add('title')
                //->add('position')
                ->add('description', NULL, array('template' => 'ObjectsAdminBundle:General:list_description.html.twig'))
                ->add('requirements')
                ->add('company')
                ->add('categories')
                ->add('languages')
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
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('keywords')
                ->add('minimumGPA')
                ->add('skills')
                ->add('compensation')
                ->add('numberOfOpenings')
                ->add('sessionPeriod')
                ->add('positionType')
                ->add('workLocation')
                ->add('Longitude')
                ->add('Latitude')
                ->add('createdAt')
                ->add('activeFrom')
                ->add('activeTo')
                ->add('active')
                ->add('title')
                //->add('position')
                ->add('description')
                ->add('requirements')
                ->add('company')
                ->add('languages')
                ->add('categories')
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
                ->add('zipcode')
                ->add('country')
                ->add('city')
                ->add('state')
                ->add('address')
                ->add('minimumGPA')
                ->add('numberOfOpenings')
                ->add('sessionPeriod')
                ->add('positionType')
                ->add('workLocation')
                ->add('Longitude')
                ->add('Latitude')
                ->add('createdAt')
                ->add('activeFrom')
                ->add('activeTo')
                ->add('active')
                ->add('title')
                //->add('position')
                ->add('description')
                ->add('requirements')
                ->add('company')
                ->add('categories')
        ;
    }

    /**
     * this function configure the new, edit form fields
     * @author Mahmoud
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

        //numberOfOpenings list
        $numberOfOpeningsArray = array();
        for ($index = 1; $index <= 30; $index++) {
            $numberOfOpeningsArray [$index] = $index;
        }

        //sessionPeriod list
        $nowYear = date("Y");
        $nextYear = $nowYear + 1;
        $sessionPeriodArray = array(
            'ASAP' => 'ASAP',
            'Flexable' => 'Flexable',
            'As Defined' => 'As Defined',
            'Spring 2013' => 'Spring ' . $nowYear,
            'Summer ' . $nowYear => 'Summer ' . $nowYear,
            'Fall ' . $nowYear => 'Fall ' . $nowYear,
            'Winter ' . $nowYear => 'Winter ' . $nowYear,
            'Spring ' . $nextYear => 'Spring ' . $nextYear,
            'Summer ' . $nextYear => 'Summer ' . $nextYear,
            'Fall ' . $nextYear => 'Fall ' . $nextYear,
            'Winter ' . $nextYear => 'Winter ' . $nextYear
        );

        //minimumGPA list
        $minimumGPAArray = array();
        $No = 0.1;
        for ($index = 1; $index <= 40; $index++) {
            $minimumGPAArray ["$No"] = $No;
            $No += 0.1;
        }
        $formMapper
                ->with('Required Fields')
                ->add('company', 'sonata_type_model', array('required' => true), array('edit' => 'list', 'admin_code' => 'company_list_admin'))
                ->add('languages', 'sonata_type_collection', array('required' => false), array(
                    'edit' => 'inline',
                    'inline' => 'table'
                ))
                ->add('zipcode', 'text', array('attr' => array('class' => 'zipcode')))
                ->add('Longitude', 'text', array('attr' => array('class' => 'longitude')))
                ->add('Latitude', 'text', array('attr' => array('class' => 'latitude')))
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
                ->add('minimumGPA', 'choice', array('choices' => $minimumGPAArray, 'attr' => array('class' => 'chosen')))
                ->add('skills', 'sonata_type_model', array('required' => false, 'attr' => array('class' => 'chosen')), array('edit' => 'inline'))
                ->add('compensation')
                ->add('keywords', 'sonata_type_model', array('required' => true, 'attr' => array('class' => 'chosen')), array('edit' => 'inline'))
                ->add('numberOfOpenings', 'choice', array('choices' => $numberOfOpeningsArray, 'attr' => array('class' => 'chosen')))
                ->add('sessionPeriod', 'choice', array('choices' => $sessionPeriodArray, 'attr' => array('class' => 'chosen')))
                ->add('positionType', 'choice', array('choices' => array('Internship' => 'Internship', 'Entry Level' => 'Entry Level'), 'expanded' => true))
                ->add('workLocation', 'choice', array('choices' => array('Office' => 'Office', 'Virtual' => 'Virtual', 'Doesn’t Matter' => 'Doesn’t Matter'), 'expanded' => true))
                ->add('activeFrom', 'date', array('years' => range($currentDate->format('Y'), $currentDate->format('Y') + 5)))
                ->add('activeTo', 'date', array('years' => range($currentDate->format('Y'), $currentDate->format('Y') + 5)))
                ->add('active', NULL, array('required' => false))
                ->add('categories', 'sonata_type_model', array('attr' => array('class' => 'chosen'), 'required' => FALSE))
                ->add('title')
                ->add('description')
                ->add('requirements')
                ->end()
        ;
    }

    /**
     * @author ahmed
     * @param object $internship
     */
    public function prePersist($internship) {
        //add the new internship to languages
        foreach ($internship->getLanguages() as $language) {
            $language->setInternship($internship);
        }
    }

    public function preUpdate($internship) {
        //add the new internship to languages
        foreach ($internship->getLanguages() as $language) {
            $language->setInternship($internship);
        }
    }

    /**
     * this function is used to set a different validation group for the form
     */
    public function getFormBuilder() {
        if (is_null($this->getSubject()->getId())) {
            $this->formOptions = array('validation_groups' => 'newInternship');
        } else {
            $this->formOptions = array('validation_groups' => 'editInternship');
        }
        $formBuilder = parent::getFormBuilder();
        return $formBuilder;
    }

}

?>

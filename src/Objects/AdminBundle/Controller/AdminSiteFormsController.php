<?php

namespace Objects\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin controller.
 * @author Ola
 *
 */
class AdminSiteFormsController extends Controller {

    /**
     * the default admin home page action
     * @return Response
     */
    public function homeAction() {
        return $this->render('ObjectsAdminBundle:Admin:home.html.twig');
    }

    /**
     *
     * Admin Action for editing Constants in service file
     * @return response
     */
    public function adminEditSiteFormsAction() {
        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            //create a new config
            $config = array();
            //get the container
            $container = $this->container;

            //set the forms' data from the files

            /****************Login Form***************/
            $config['loginFormName'] = $container->getParameter('loginFormName');
            $config['loginFormDesc'] = $container->getParameter('loginFormDesc');

            /****************Student Sign Up*****************/
            $config['studentSignUp_FormName'] = $container->getParameter('studentSignUp_FormName');
            $config['studentSignUp_FormDesc'] = $container->getParameter('studentSignUp_FormDesc');

            /****************Student Sign Up step4 Language*****************/
            $config['studentSignUpLanguage_FormName'] = $container->getParameter('studentSignUpLanguage_FormName');
            $config['studentSignUpLanguage_FormDesc'] = $container->getParameter('studentSignUpLanguage_FormDesc');

            /****************Student Sign Up step3 Education Level*****************/
            $config['studentSignUpEducation_FormName'] = $container->getParameter('studentSignUpEducation_FormName');
            $config['studentSignUpEducation_FormDesc'] = $container->getParameter('studentSignUpEducation_FormDesc');

            /****************Student Sign Up step4 Create Cv steps1*****************/
            $config['studentSignUpCv_FormName'] = $container->getParameter('studentSignUpCv_FormName');
            $config['studentSignUpCv_FormName1'] = $container->getParameter('studentSignUpCv_FormName1');
            $config['studentSignUpCv_FormDesc'] = $container->getParameter('studentSignUpCv_FormDesc');

            /****************Student Create Cv steps2(Skills)*****************/
            $config['studentSignUpCvSkills_FormName'] = $container->getParameter('studentSignUpCvSkills_FormName');
            $config['studentSignUpCvSkills_FormDesc'] = $container->getParameter('studentSignUpCvSkills_FormDesc');

            /****************Student Create Cv steps3(Experience)*****************/
            $config['studentSignUpCvExperience_FormName'] = $container->getParameter('studentSignUpCvExperience_FormName');
            $config['studentSignUpCvExperience_FormDesc'] = $container->getParameter('studentSignUpCvExperience_FormDesc');

            /****************Student Create Cv steps4(Success)*****************/
            $config['studentSignUpCvSuccess_FormName'] = $container->getParameter('studentSignUpCvSuccess_FormName');
            $config['studentSignUpCvSuccess_FormDesc'] = $container->getParameter('studentSignUpCvSuccess_FormDesc');

            /****************Student Edit Account*****************/
            $config['studentEditAccount_FormName'] = $container->getParameter('studentEditAccount_FormName');
            $config['studentEditAccount_FormDesc'] = $container->getParameter('studentEditAccount_FormDesc');

            /****************Student Edit Resume*****************/
            $config['studentEditResume_FormName'] = $container->getParameter('studentEditResume_FormName');
            $config['studentEditResume_FormDesc'] = $container->getParameter('studentEditResume_FormDesc');

            /****************Student Edit Resume/ Add Employment History*****************/
            $config['studentEditResumeEmpHistory_FormName'] = $container->getParameter('studentEditResumeEmpHistory_FormName');
            $config['studentEditResumeEmpHistory_FormDesc'] = $container->getParameter('studentEditResumeEmpHistory_FormDesc');

            /****************Student Edit Resume/ Add Skills*****************/
            $config['studentEditCvAddSkill_FormName'] = $container->getParameter('studentEditCvAddSkill_FormName');
            $config['studentEditCvAddSkill_FormDesc'] = $container->getParameter('studentEditCvAddSkill_FormDesc');

            /****************Student Edit Skills*****************/
            $config['studentEditSkill_FormName'] = $container->getParameter('studentEditSkill_FormName');
            $config['studentEditSkill_FormDesc'] = $container->getParameter('studentEditSkill_FormDesc');

            /****************Student Edit Education*****************/
            $config['studentEditEducation_FormName'] = $container->getParameter('studentEditEducation_FormName');
            $config['studentEditEducation_FormDesc'] = $container->getParameter('studentEditEducation_FormDesc');

            /****************Student Edit Employment History*****************/
            $config['studentEditEmpHistory_FormName'] = $container->getParameter('studentEditEmpHistory_FormName');
            $config['studentEditEmpHistory_FormDesc'] = $container->getParameter('studentEditEmpHistory_FormDesc');

            /****************Student Add new Resume*****************/
            $config['studentAddResume_FormName'] = $container->getParameter('studentAddResume_FormName');
            $config['studentAddResume_FormDesc'] = $container->getParameter('studentAddResume_FormDesc');

            /****************Student Add new Education*****************/
            $config['studentAddEducation_FormName'] = $container->getParameter('studentAddEducation_FormName');
            $config['studentAddEducation_FormDesc'] = $container->getParameter('studentAddEducation_FormDesc');

            /****************Student Add new Employment History*****************/
            $config['studentAddEmpHistory_FormName'] = $container->getParameter('studentAddEmpHistory_FormName');
            $config['studentAddEmpHistory_FormDesc'] = $container->getParameter('studentAddEmpHistory_FormDesc');

            /****************Company/Employers SignUp*****************/
            $config['companySignUp_FormName'] = $container->getParameter('companySignUp_FormName');
            $config['companySignUp_FormDesc'] = $container->getParameter('companySignUp_FormDesc');

            /****************Company/Employer Add New Task*****************/
            $config['companyAddTask_FormName'] = $container->getParameter('companyAddTask_FormName');
            $config['companyAddTask_FormName1'] = $container->getParameter('companyAddTask_FormName1');
            $config['companyAddTask_FormDesc'] = $container->getParameter('companyAddTask_FormDesc');

            /****************Company/Employer Add new Internship/Job*****************/
            $config['companyAddJob_FormName'] = $container->getParameter('companyAddJob_FormName');
            $config['companyAddJob_FormDesc'] = $container->getParameter('companyAddJob_FormDesc');

            /****************Company/Employer Edit Task*****************/
            $config['companyEditTask_FormName'] = $container->getParameter('companyEditTask_FormName');
            $config['companyEditTask_FormName1'] = $container->getParameter('companyEditTask_FormName1');
            $config['companyEditTask_FormDesc'] = $container->getParameter('companyEditTask_FormDesc');

            /****************Company/Employer Edit Internship/Job*****************/
            $config['companyEditJob_FormName'] = $container->getParameter('companyEditJob_FormName');
            $config['companyEditJob_FormDesc'] = $container->getParameter('companyEditJob_FormDesc');

            /****************User Add/Edit Languages*****************/
            $config['studentAddLanguage_FormName'] = $container->getParameter('studentAddLanguage_FormName');
            $config['studentAddLanguage_FormDesc'] = $container->getParameter('studentAddLanguage_FormDesc');
            $config['studentEditLanguage_FormName'] = $container->getParameter('studentEditLanguage_FormName');
            $config['studentEditLanguage_FormDesc'] = $container->getParameter('studentEditLanguage_FormDesc');

            //make form to fill it with data
            $form = $this->createFormBuilder($config)
                    /****************Login Form***************/
                    ->add('loginFormName', 'text')
                    ->add('loginFormDesc', 'textarea', array('required'=> false))
                     /****************Student Sign Up*****************/
                    ->add('studentSignUp_FormName', 'text')
                    ->add('studentSignUp_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Sign Up step4 Language*****************/
                    ->add('studentSignUpLanguage_FormName', 'text')
                    ->add('studentSignUpLanguage_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Sign Up step3 Education Level*****************/
                    ->add('studentSignUpEducation_FormName', 'text')
                    ->add('studentSignUpEducation_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Sign Up step4 Creat Cv steps1,2,3,4*****************/
                    ->add('studentSignUpCv_FormName', 'text')
                    ->add('studentSignUpCv_FormName1', 'text')
                    ->add('studentSignUpCv_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Create Cv steps2(Skills)*****************/
                    ->add('studentSignUpCvSkills_FormName', 'text')
                    ->add('studentSignUpCvSkills_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Create Cv steps3(Experience)*****************/
                    ->add('studentSignUpCvExperience_FormName', 'text')
                    ->add('studentSignUpCvExperience_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Create Cv steps4(Success)*****************/
                    ->add('studentSignUpCvSuccess_FormName', 'textarea')
                    ->add('studentSignUpCvSuccess_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Edit Account*****************/
                    ->add('studentEditAccount_FormName', 'text')
                    ->add('studentEditAccount_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Edit Resume*****************/
                    ->add('studentEditResume_FormName', 'text')
                    ->add('studentEditResume_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Edit Resume/ Add Employment History*****************/
                    ->add('studentEditResumeEmpHistory_FormName', 'text')
                    ->add('studentEditResumeEmpHistory_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Edit Resume/ Add Skills*****************/
                    ->add('studentEditCvAddSkill_FormName', 'text')
                    ->add('studentEditCvAddSkill_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Edit Skills*****************/
                    ->add('studentEditSkill_FormName', 'text')
                    ->add('studentEditSkill_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Edit Education*****************/
                    ->add('studentEditEducation_FormName', 'text')
                    ->add('studentEditEducation_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Edit Employment History*****************/
                    ->add('studentEditEmpHistory_FormName', 'text')
                    ->add('studentEditEmpHistory_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Add new Resume*****************/
                    ->add('studentAddResume_FormName', 'text')
                    ->add('studentAddResume_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Add new Education*****************/
                    ->add('studentAddEducation_FormName', 'text')
                    ->add('studentAddEducation_FormDesc', 'textarea', array('required'=> false))
                    /****************Student Add new Employment History*****************/
                    ->add('studentAddEmpHistory_FormName', 'text')
                    ->add('studentAddEmpHistory_FormDesc', 'textarea', array('required'=> false))
                    /****************Company/Employers SignUp*****************/
                    ->add('companySignUp_FormName', 'text')
                    ->add('companySignUp_FormDesc', 'textarea', array('required'=> false))
                    /****************Company/Employer Add New Task*****************/
                    ->add('companyAddTask_FormName', 'text')
                    ->add('companyAddTask_FormName1', 'text')
                    ->add('companyAddTask_FormDesc', 'textarea', array('required'=> false))
                    /****************Company/Employer Add new Internship/Job*****************/
                    ->add('companyAddJob_FormName', 'text')
                    ->add('companyAddJob_FormDesc', 'textarea', array('required'=> false))
                    /****************Company/Employer Edit Task*****************/
                    ->add('companyEditTask_FormName', 'text')
                    ->add('companyEditTask_FormName1', 'text')
                    ->add('companyEditTask_FormDesc', 'textarea', array('required'=> false))
                    /****************Company/Employer Edit Internship/Job*****************/
                    ->add('companyEditJob_FormName', 'text')
                    ->add('companyEditJob_FormDesc', 'textarea', array('required'=> false))
                    /****************User Add/Edit Languages*****************/
                    ->add('studentAddLanguage_FormName', 'text')
                    ->add('studentAddLanguage_FormDesc', 'textarea', array('required'=> false))
                    ->add('studentEditLanguage_FormName', 'text')
                    ->add('studentEditLanguage_FormDesc', 'textarea', array('required'=> false))
                    ->getForm();

            $request = $this->getRequest();


            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                //check that the form is valid
                if ($form->isValid()) {

                    //form data array
                    $formDataArray = $form->getData();
                    $message = FALSE;
                    //this flag is for opening the first configuration file
                    $firstFileChange = FALSE;
                    //compare existed data with the data entered by the user
                    //check if any of the configurations in the first file need change

                    /****************Login Form***************/
                    if ($formDataArray['loginFormName'] != $container->getParameter('loginFormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['loginFormDesc'] != $container->getParameter('loginFormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Sign Up*****************/
                    if ($formDataArray['studentSignUp_FormName'] != $container->getParameter('studentSignUp_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentSignUp_FormDesc'] != $container->getParameter('studentSignUp_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Sign Up step4 Language*****************/
                    if ($formDataArray['studentSignUpLanguage_FormName'] != $container->getParameter('studentSignUpLanguage_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentSignUpLanguage_FormDesc'] != $container->getParameter('studentSignUpLanguage_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Sign Up step3 Education Level*****************/
                    if ($formDataArray['studentSignUpEducation_FormName'] != $container->getParameter('studentSignUpEducation_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentSignUpEducation_FormDesc'] != $container->getParameter('studentSignUpEducation_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Sign Up step4 Creat Cv steps1,2,3,4*****************/
                    if ($formDataArray['studentSignUpCv_FormName'] != $container->getParameter('studentSignUpCv_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentSignUpCv_FormName1'] != $container->getParameter('studentSignUpCv_FormName1')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentSignUpCv_FormDesc'] != $container->getParameter('studentSignUpCv_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Create Cv steps2(Skills)*****************/
                     if ($formDataArray['studentSignUpCvSkills_FormName'] != $container->getParameter('studentSignUpCvSkills_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentSignUpCvSkills_FormDesc'] != $container->getParameter('studentSignUpCvSkills_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Create Cv steps3(Experience)*****************/
                    if ($formDataArray['studentSignUpCvExperience_FormName'] != $container->getParameter('studentSignUpCvExperience_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentSignUpCvExperience_FormDesc'] != $container->getParameter('studentSignUpCvExperience_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Create Cv steps4(Success)*****************/
                    if ($formDataArray['studentSignUpCvSuccess_FormName'] != $container->getParameter('studentSignUpCvSuccess_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentSignUpCvSuccess_FormDesc'] != $container->getParameter('studentSignUpCvSuccess_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Edit Account*****************/
                    if ($formDataArray['studentEditAccount_FormName'] != $container->getParameter('studentEditAccount_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentEditAccount_FormDesc'] != $container->getParameter('studentEditAccount_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Edit Resume*****************/
                    if ($formDataArray['studentEditResume_FormName'] != $container->getParameter('studentEditResume_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentEditResume_FormDesc'] != $container->getParameter('studentEditResume_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Edit Resume/ Add Employment History*****************/
                    if ($formDataArray['studentEditResumeEmpHistory_FormName'] != $container->getParameter('studentEditResumeEmpHistory_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentEditResumeEmpHistory_FormDesc'] != $container->getParameter('studentEditResumeEmpHistory_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Edit Resume/ Add Skills*****************/
                    if ($formDataArray['studentEditCvAddSkill_FormName'] != $container->getParameter('studentEditCvAddSkill_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentEditCvAddSkill_FormDesc'] != $container->getParameter('studentEditCvAddSkill_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Edit Skills*****************/
                    if ($formDataArray['studentEditSkill_FormName'] != $container->getParameter('studentEditSkill_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentEditSkill_FormDesc'] != $container->getParameter('studentEditSkill_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Edit Education*****************/
                    if ($formDataArray['studentEditEducation_FormName'] != $container->getParameter('studentEditEducation_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentEditEducation_FormDesc'] != $container->getParameter('studentEditEducation_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************Student Edit Employment History*****************/
                    if ($formDataArray['studentEditEmpHistory_FormName'] != $container->getParameter('studentEditEmpHistory_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentEditEmpHistory_FormDesc'] != $container->getParameter('studentEditEmpHistory_FormDesc')) {
                        $firstFileChange = TRUE;
                    }

                    /****************Student Add new Resume*****************/
                    if ($formDataArray['studentAddResume_FormName'] != $container->getParameter('studentAddResume_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentAddResume_FormDesc'] != $container->getParameter('studentAddResume_FormDesc')) {
                        $firstFileChange = TRUE;
                    }

                    /****************Student Add new Education*****************/
                    if ($formDataArray['studentAddEducation_FormName'] != $container->getParameter('studentAddEducation_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentAddEducation_FormDesc'] != $container->getParameter('studentAddEducation_FormDesc')) {
                        $firstFileChange = TRUE;
                    }

                    /****************Student Add new Employment History*****************/
                    if ($formDataArray['studentAddEmpHistory_FormName'] != $container->getParameter('studentAddEmpHistory_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentAddEmpHistory_FormDesc'] != $container->getParameter('studentAddEmpHistory_FormDesc')) {
                        $firstFileChange = TRUE;
                    }

                    /****************Company/Employers SignUp*****************/
                    if ($formDataArray['companySignUp_FormName'] != $container->getParameter('companySignUp_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['companySignUp_FormDesc'] != $container->getParameter('companySignUp_FormDesc')) {
                        $firstFileChange = TRUE;
                    }

                    /****************Company/Employer Add New Task*****************/
                    if ($formDataArray['companyAddTask_FormName'] != $container->getParameter('companyAddTask_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['companyAddTask_FormName1'] != $container->getParameter('companyAddTask_FormName1')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['companyAddTask_FormDesc'] != $container->getParameter('companyAddTask_FormDesc')) {
                        $firstFileChange = TRUE;
                    }

                    /****************Company/Employer Add new Internship/Job*****************/
                    if ($formDataArray['companyAddJob_FormName'] != $container->getParameter('companyAddJob_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['companyAddJob_FormDesc'] != $container->getParameter('companyAddJob_FormDesc')) {
                        $firstFileChange = TRUE;
                    }

                    /****************Company/Employer Edit Task*****************/
                    if ($formDataArray['companyEditTask_FormName'] != $container->getParameter('companyEditTask_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['companyEditTask_FormName1'] != $container->getParameter('companyEditTask_FormName1')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['companyEditTask_FormDesc'] != $container->getParameter('companyEditTask_FormDesc')) {
                        $firstFileChange = TRUE;
                    }

                    /****************Company/Employer Edit Internship/Job*****************/
                    if ($formDataArray['companyEditJob_FormName'] != $container->getParameter('companyEditJob_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['companyEditJob_FormDesc'] != $container->getParameter('companyEditJob_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    /****************User Add/Edit Languages*****************/
                    if ($formDataArray['studentAddLanguage_FormName'] != $container->getParameter('studentAddLanguage_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentAddLanguage_FormDesc'] != $container->getParameter('studentAddLanguage_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentEditLanguage_FormName'] != $container->getParameter('studentEditLanguage_FormName')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['studentEditLanguage_FormDesc'] != $container->getParameter('studentEditLanguage_FormDesc')) {
                        $firstFileChange = TRUE;
                    }
                    //check if we need to open the first file to change it is config
                    if ($firstFileChange) {
                        //the configuration file path
                        $configFile = __DIR__ . '/../../InternJumpBundle/Resources/config/configSiteForms.yml';
                        //create a new yaml parser
                        $yaml = new \Symfony\Component\Yaml\Parser();
                        //try to open the configuration file
                        $content = @file_get_contents($configFile);
                        //check if we opened the file
                        if ($content !== FALSE) {

                            //file opened try to parse the content
                            try {
                                //parser to parse services.yml and get its data
                                $value = $yaml->parse($content);
                            } catch (\Exception $e) {
                                // an error occurred during parsing
                                return $this->render('::general_admin.html.twig', array(
                                            'message' => 'Unable to parse the YAML File: ' . $configFile . '<br/><strong>Please fix this: </strong>' . $e->getMessage()
                                        ));
                            }
                            //set the parameters in the file
                            /****************Login Form***************/
                            $value['parameters']['loginFormName'] = $formDataArray['loginFormName'];
                            $value['parameters']['loginFormDesc'] = $formDataArray['loginFormDesc'];

                            /****************Student Sign Up*****************/
                            $value['parameters']['studentSignUp_FormName'] = $formDataArray['studentSignUp_FormName'];
                            $value['parameters']['studentSignUp_FormDesc'] = $formDataArray['studentSignUp_FormDesc'];

                            /****************Student Sign Up step4 Language*****************/
                            $value['parameters']['studentSignUpLanguage_FormName'] = $formDataArray['studentSignUpLanguage_FormName'];
                            $value['parameters']['studentSignUpLanguage_FormDesc'] = $formDataArray['studentSignUpLanguage_FormDesc'];

                            /****************Student Sign Up step3 Education Level*****************/
                            $value['parameters']['studentSignUpEducation_FormName'] = $formDataArray['studentSignUpEducation_FormName'];
                            $value['parameters']['studentSignUpEducation_FormDesc'] = $formDataArray['studentSignUpEducation_FormDesc'];

                            /****************Student Sign Up step4 Creat Cv steps1,2,3,4*****************/
                            $value['parameters']['studentSignUpCv_FormName'] = $formDataArray['studentSignUpCv_FormName'];
                            $value['parameters']['studentSignUpCv_FormName1'] = $formDataArray['studentSignUpCv_FormName1'];
                            $value['parameters']['studentSignUpCv_FormDesc'] = $formDataArray['studentSignUpCv_FormDesc'];

                            /****************Student Create Cv steps2(Skills)*****************/
                            $value['parameters']['studentSignUpCvSkills_FormName'] = $formDataArray['studentSignUpCvSkills_FormName'];
                            $value['parameters']['studentSignUpCvSkills_FormDesc'] = $formDataArray['studentSignUpCvSkills_FormDesc'];

                            /****************Student Create Cv steps3(Experience)*****************/
                            $value['parameters']['studentSignUpCvExperience_FormName'] = $formDataArray['studentSignUpCvExperience_FormName'];
                            $value['parameters']['studentSignUpCvExperience_FormDesc'] = $formDataArray['studentSignUpCvExperience_FormDesc'];

                            /****************Student Create Cv steps4(Success)*****************/
                            $value['parameters']['studentSignUpCvSuccess_FormName'] = $formDataArray['studentSignUpCvSuccess_FormName'];
                            $value['parameters']['studentSignUpCvSuccess_FormDesc'] = $formDataArray['studentSignUpCvSuccess_FormDesc'];

                            /****************Student Edit Account*****************/
                            $value['parameters']['studentEditAccount_FormName'] = $formDataArray['studentEditAccount_FormName'];
                            $value['parameters']['studentEditAccount_FormDesc'] = $formDataArray['studentEditAccount_FormDesc'];

                            /****************Student Edit Resume*****************/
                            $value['parameters']['studentEditResume_FormName'] = $formDataArray['studentEditResume_FormName'];
                            $value['parameters']['studentEditResume_FormDesc'] = $formDataArray['studentEditResume_FormDesc'];

                            /****************Student Edit Resume/ Add Employment History*****************/
                            $value['parameters']['studentEditResumeEmpHistory_FormName'] = $formDataArray['studentEditResumeEmpHistory_FormName'];
                            $value['parameters']['studentEditResumeEmpHistory_FormDesc'] = $formDataArray['studentEditResumeEmpHistory_FormDesc'];

                            /****************tudent Edit Resume/ Add Skills*****************/
                            $value['parameters']['studentEditCvAddSkill_FormName'] = $formDataArray['studentEditCvAddSkill_FormName'];
                            $value['parameters']['studentEditCvAddSkill_FormDesc'] = $formDataArray['studentEditCvAddSkill_FormDesc'];

                            /****************Student Edit Skills*****************/
                            $value['parameters']['studentEditSkill_FormName'] = $formDataArray['studentEditSkill_FormName'];
                            $value['parameters']['studentEditSkill_FormDesc'] = $formDataArray['studentEditSkill_FormDesc'];

                            /****************Student Edit Education*****************/
                            $value['parameters']['studentEditEducation_FormName'] = $formDataArray['studentEditEducation_FormName'];
                            $value['parameters']['studentEditEducation_FormDesc'] = $formDataArray['studentEditEducation_FormDesc'];

                            /****************Student Edit Employment History*****************/
                            $value['parameters']['studentEditEmpHistory_FormName'] = $formDataArray['studentEditEmpHistory_FormName'];
                            $value['parameters']['studentEditEmpHistory_FormDesc'] = $formDataArray['studentEditEmpHistory_FormDesc'];

                            /****************Student Add new Resume*****************/
                            $value['parameters']['studentAddResume_FormName'] = $formDataArray['studentAddResume_FormName'];
                            $value['parameters']['studentAddResume_FormDesc'] = $formDataArray['studentAddResume_FormDesc'];

                            /****************Student Add new Education*****************/
                            $value['parameters']['studentAddEducation_FormName'] = $formDataArray['studentAddEducation_FormName'];
                            $value['parameters']['studentAddEducation_FormDesc'] = $formDataArray['studentAddEducation_FormDesc'];

                            /****************Student Add new Employment History*****************/
                            $value['parameters']['studentAddEmpHistory_FormName'] = $formDataArray['studentAddEmpHistory_FormName'];
                            $value['parameters']['studentAddEmpHistory_FormDesc'] = $formDataArray['studentAddEmpHistory_FormDesc'];

                            /****************Company/Employers SignUp*****************/
                            $value['parameters']['companySignUp_FormName'] = $formDataArray['companySignUp_FormName'];
                            $value['parameters']['companySignUp_FormDesc'] = $formDataArray['companySignUp_FormDesc'];

                            /****************Company/Employer Add New Task*****************/
                            $value['parameters']['companyAddTask_FormName'] = $formDataArray['companyAddTask_FormName'];
                            $value['parameters']['companyAddTask_FormName1'] = $formDataArray['companyAddTask_FormName1'];
                            $value['parameters']['companyAddTask_FormDesc'] = $formDataArray['companyAddTask_FormDesc'];

                            /****************Company/Employer Add new Internship/Job*****************/
                            $value['parameters']['companyAddJob_FormName'] = $formDataArray['companyAddJob_FormName'];
                            $value['parameters']['companyAddJob_FormDesc'] = $formDataArray['companyAddJob_FormDesc'];

                            /****************Company/Employer Edit Task*****************/
                            $value['parameters']['companyEditTask_FormName'] = $formDataArray['companyEditTask_FormName'];
                            $value['parameters']['companyEditTask_FormName1'] = $formDataArray['companyEditTask_FormName1'];
                            $value['parameters']['companyEditTask_FormDesc'] = $formDataArray['companyEditTask_FormDesc'];

                            /****************Company/Employer Edit Internship/Job*****************/
                            $value['parameters']['companyEditJob_FormName'] = $formDataArray['companyEditJob_FormName'];
                            $value['parameters']['companyEditJob_FormDesc'] = $formDataArray['companyEditJob_FormDesc'];

                            /****************User Add/Edit Languages*****************/
                            $value['parameters']['studentAddLanguage_FormName'] = $formDataArray['studentAddLanguage_FormName'];
                            $value['parameters']['studentAddLanguage_FormDesc'] = $formDataArray['studentAddLanguage_FormDesc'];
                            $value['parameters']['studentEditLanguage_FormName'] = $formDataArray['studentEditLanguage_FormName'];
                            $value['parameters']['studentEditLanguage_FormDesc'] = $formDataArray['studentEditLanguage_FormDesc'];

                            //dump to make spaces and format of the file before update it
                            $dumper = new \Symfony\Component\Yaml\Dumper();
                            $yaml = $dumper->dump($value, 3);
                            //write the data to the file
                            $writed = @file_put_contents($configFile, $yaml);
                            if ($writed === FALSE) {
                                //an error occurred during parsing
                                $message = "Unable to write in the YAML file: $configFile";
                            }
                        } else {
                            // an error occurred during parsing
                            $message = "Unable to open the YAML file: $configFile";
                        }
                    }
                    //get the session to set flag
                    $session = $request->getSession();
                    //clear the previous flashes
                    $session->clearFlashes();
                    //check if any error occured
                    if ($message) {
                        //set the error flag
                        $session->setFlash('error', $message);
                    } else {
                        //check if user changed any thing in the form
                        if ($firstFileChange) {
                            //clear the cache for the new configurations to take effect
                            exec(PHP_BINDIR . '/php-cli ' . __DIR__ . '/../../../../app/console cache:clear -e prod');
                            exec(PHP_BINDIR . '/php-cli ' . __DIR__ . '/../../../../app/console cache:warmup --no-debug -e prod');
                            //set the success flag
                            $session->setFlash('success', 'Your edits were saved');
                            return $this->redirect($this->generateUrl('admin_edit_site_forms'));
                        } else {
                            //set the notice flag
                            $session->setFlash('notice', 'Nothing changed');
                        }
                    }
                } else {
                    echo 'not valid';
                }
            }

            return $this->render('ObjectsAdminBundle:Admin:editSiteForms.html.twig', array(
                        'form' => $form->createView(),
                        'config' => $config
                    ));
        } else {
            return $this->redirect($this->generateUrl('ObjectsInternJumpBundle_homepage'));
        }
    }
}

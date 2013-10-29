<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Objects\InternJumpBundle\Entity\Manager;
use Objects\InternJumpBundle\Form\ManagerType;

/**
 * Manager controller.
 *
 */
class ManagerController extends Controller {

    /**
     * this function used for manager home page
     * @author ahmed
     */
    public function managerHomeAction($page) {
        //check for logged manager
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $InterviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        //get logedin manager objects
        $manager = $this->get('security.context')->getToken()->getUser();
        $company = $manager->getCompany();

        //the results per page number
        $itemsPerPage = $this->container->getParameter('jobs_per_page_index_jobs_page');

        //get company jobs
        $companyJobs = $internshipRepo->getCompanyJobs($company->getId(), $page, $itemsPerPage, TRUE);

        $allcompanyJobsCount = $internshipRepo->countCompanyJobs($company->getId(), TRUE);

        $allcompanyJobsCount = $allcompanyJobsCount['0']['jobsCount'];
        //calculate the last page number
        $lastPageNumber = (int) ($allcompanyJobsCount / $itemsPerPage);
        if (($allcompanyJobsCount % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }

        return $this->render('ObjectsInternJumpBundle:Manager:managerHome.html.twig', array(
                    'company' => $company,
                    'manager' => $manager,
                    'companyJobs' => $companyJobs,
                    'page' => $page,
                    'lastPageNumber' => $lastPageNumber
        ));
    }

    /**
     * this function used to list managers for company
     * @author ahmed
     * @param string $loginName
     */
    public function companyManagersAction($loginName) {
        //check for logged company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        $managerRepo = $em->getRepository('ObjectsInternJumpBundle:Manager');

        //get all company managers
        $companyManagers = $managerRepo->findBy(array('company' => $company->getId()));

        return $this->render('ObjectsInternJumpBundle:Manager:companyManagers.html.twig', array(
                    'company' => $company,
                    'companyManagers' => $companyManagers
        ));
    }

    /**
     * Displays a form to create a new Manager entity.
     *
     */
    public function newAction() {
        //check for loggedin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('login'));
        }
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the role repo
        $roleRepository = $em->getRepository('ObjectsUserBundle:Role');
        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();

        $manager = new Manager();
        //get the request object
        $request = $this->getRequest();
        //create a signup form
        $formBuilder = $this->createFormBuilder($manager, array(
                    'validation_groups' => array('newmanager')
                ))
                ->add('name')
                ->add('loginName')
                ->add('email')
                ->add('userPassword', 'repeated', array(
            'type' => 'password',
            'first_name' => 'Password',
            'second_name' => 'RePassword',
            'invalid_message' => "The passwords don't match"))
        ;
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //get a ROLE_COMPANY_MANAGER
                $roleCompanyManager = $roleRepository->findOneByName('ROLE_COMPANY_MANAGER');
                $manager->addRole($roleCompanyManager);
                $manager->setCompany($company);

                $em->persist($manager);
                //store the object in the database
                $em->flush();
                //prepare the body of the email
                $body = $this->renderView('ObjectsInternJumpBundle:Manager:welcome_to_site.txt.twig', array(
                    'company' => $company,
                    'manager' => $manager,
                    'password' => $manager->getUserPassword(),
                    'Email' => $this->container->getParameter('contact_us_email'),
                ));

                //prepare the message object
                $message = \Swift_Message::newInstance()
                        ->setSubject($this->get('translator')->trans('Welcome to InternJump'))
                        ->setFrom($this->container->getParameter('mailer_user'))
                        ->setTo($manager->getEmail())
                        ->setBody($body)
                ;
                //send the email
                $this->get('mailer')->send($message);
                //set the success flag in the session
                $this->getRequest()->getSession()->setFlash('success', 'New Manager Added successfully');
                return $this->redirect($this->generateUrl('company_managers', array('loginName' => $company->getLoginName())));
            }
        }
        return $this->render('ObjectsInternJumpBundle:Manager:new.html.twig', array(
                    'entity' => $manager,
                    'form' => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Manager entity.
     *
     */
    public function editAction($id) {
        //check for loggedin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getEntityManager();

        $manager = $em->getRepository('ObjectsInternJumpBundle:Manager')->find($id);

        if (!$manager) {
            throw $this->createNotFoundException('Unable to find Manager entity.');
        }

        if ($company->getId() != $manager->getCompany()->getId()) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('login'));
        }

        //get the request object
        $request = $this->getRequest();
        //create a signup form
        $formBuilder = $this->createFormBuilder($manager, array(
                    'validation_groups' => array('editmanager')
                ))
                ->add('name')
                ->add('loginName')
                ->add('email')
                ->add('userPassword', 'repeated', array(
            'required' => false,
            'type' => 'password',
            'first_name' => 'Password',
            'second_name' => 'RePassword',
            'invalid_message' => "The passwords don't match"))
        ;
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //set the password for the user if changed
                $manager->setValidPassword();
                //store the object in the database
                $em->flush();
                //set the success flag in the session
                $this->getRequest()->getSession()->setFlash('success', 'Manager Updated successfully');
                return $this->redirect($this->generateUrl('company_managers', array('loginName' => $company->getLoginName())));
            }
        }

        return $this->render('ObjectsInternJumpBundle:Manager:edit.html.twig', array(
                    'entity' => $manager,
                    'form' => $form->createView()
        ));
    }

    /**
     * Deletes a Manager entity.
     *
     */
    public function deleteAction($id) {
        //check for loggedin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('login'));
        }
        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:Manager')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Manager entity.');
        }

        if ($company->getId() == $entity->getCompany()->getId()) {
            $em->remove($entity);
            $em->flush();
            $this->getRequest()->getSession()->setFlash('success', 'Manager Deleted successfully');
        }
        return $this->redirect($this->generateUrl('company_managers', array('loginName' => $company->getLoginName())));
    }

}

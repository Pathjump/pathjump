<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Objects\InternJumpBundle\Entity\EmploymentHistory;
use Objects\InternJumpBundle\Form\EmploymentHistoryType;

/**
 * FacebookEmploymentHistoryController.
 *
 */
class FacebookEmploymentHistoryController extends Controller {

    /**
     * the create cv third step
     * @author Mahmoud
     */
    public function signupCVExperienceAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        if (count($user->getEmploymentHistories()) == 0) {
            //add one education entity to the user
            $user->addEmploymentHistory(new EmploymentHistory());
        }
        //create an education form
        $formBuilder = $this->createFormBuilder($user)
                ->add('employmentHistories', 'collection', array('type' => new EmploymentHistoryType(), 'allow_add' => true, 'by_reference' => false, 'allow_delete' => true));
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                $cv = $user->getCvs()->first();
                $cv->setEmploymentHistory($user->getEmploymentHistories());
                //update the user education points
                $experiencePoints = 0;
                foreach ($cv->getEmploymentHistory() as $employmentHistory) {
                    $experiencePoints += $employmentHistory->getYearsCount() * $this->container->getParameter('one_year_experience_points');
                }
                $cv->setEmploymentHistoryPoints($experiencePoints);
                $cv->setTotalPoints();
                //save the user data
                $this->getDoctrine()->getEntityManager()->flush();
                return $this->redirect($this->generateUrl('fb_signup_cv_success'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:FacebookEmploymentHistory:signup_cv_experiences.html.twig', array(
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentSignUpCvExperience_FormName'),
                    'formDesc' => $this->container->getParameter('studentSignUpCvExperience_FormDesc'),
                ));
    }

    /**
     * this function check if the current logged in user is the owner of the requested object
     * @param object $entity
     * @throws AccessDeniedHttpException if the user is not the owner
     */
    private function checkUserOwnObject($entity) {
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //check if the user is the owner of the object
        if ($entity->getUser()->getId() != $user->getId()) {
            throw new AccessDeniedHttpException('This Employment History is not yours');
        }
    }

    /**
     * Displays a form to create a new EmploymentHistory entity.
     *
     */
    public function newAction() {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $entity = new EmploymentHistory();
        $form = $this->createForm(new EmploymentHistoryType(), $entity);
        return $this->render('ObjectsInternJumpBundle:FacebookEmploymentHistory:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentAddEmpHistory_FormName'),
                    'formDesc' => $this->container->getParameter('studentAddEmpHistory_FormDesc'),
                ));
    }

    /**
     * Creates a new EmploymentHistory entity.
     *
     */
    public function createAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $entity = new EmploymentHistory();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        $entity->setUser($user);
        $request = $this->getRequest();
        $form = $this->createForm(new EmploymentHistoryType(), $entity);
        $form->bindRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            $request->getSession()->setFlash('success', 'Created Successfuly');
            return $this->redirect($this->generateUrl('fb_employmenthistory_new'));
        }
        return $this->render('ObjectsInternJumpBundle:FacebookEmploymentHistory:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentAddEmpHistory_FormName'),
                    'formDesc' => $this->container->getParameter('studentAddEmpHistory_FormDesc'),
                ));
    }

    /**
     * Displays a form to edit an existing EmploymentHistory entity.
     *
     */
    public function editAction($id) {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:EmploymentHistory')->find($id);
        if (!$entity) {
            $message = $this->container->getParameter('emp_history_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }
        $this->checkUserOwnObject($entity);
        $editForm = $this->createForm(new EmploymentHistoryType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
        return $this->render('ObjectsInternJumpBundle:FacebookEmploymentHistory:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'formName' => $this->container->getParameter('studentEditEmpHistory_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditEmpHistory_FormDesc'),
                ));
    }

    /**
     * Edits an existing EmploymentHistory entity.
     *
     */
    public function updateAction($id) {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:EmploymentHistory')->find($id);
        if (!$entity) {
            $message = $this->container->getParameter('emp_history_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }
        $this->checkUserOwnObject($entity);
        $editForm = $this->createForm(new EmploymentHistoryType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
        $request = $this->getRequest();
        $editForm->bindRequest($request);
        if ($editForm->isValid()) {
            //get the user object
            $user = $entity->getUser();
            //update all the cvs points
            foreach ($user->getCvs() as $cv) {
                $experiencePoints = 0;
                foreach ($cv->getEmploymentHistory() as $employmentHistory) {
                    $experiencePoints += $employmentHistory->getYearsCount() * $this->container->getParameter('one_year_experience_points');
                }
                $cv->setEmploymentHistoryPoints($experiencePoints);
                $cv->setTotalPoints();
            }
            $em->flush();
            $request->getSession()->setFlash('success', 'Edited Successfuly');
            return $this->redirect($this->generateUrl('fb_employmenthistory_edit', array('id' => $id)));
        }
        return $this->render('ObjectsInternJumpBundle:FacebookEmploymentHistory:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'formName' => $this->container->getParameter('studentEditEmpHistory_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditEmpHistory_FormDesc'),
                ));
    }

    /**
     * Deletes a EmploymentHistory entity.
     *
     */
    public function deleteAction($id) {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();
        $form->bindRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ObjectsInternJumpBundle:EmploymentHistory')->find($id);
            if (!$entity) {
                $message = $this->container->getParameter('emp_history_not_found_error_msg');
                return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
            }
            $this->checkUserOwnObject($entity);
            //get the user object
            $user = $entity->getUser();
            $em->remove($entity);
            $em->flush();
            //update all the cvs points
            foreach ($user->getCvs() as $cv) {
                $experiencePoints = 0;
                foreach ($cv->getEmploymentHistory() as $employmentHistory) {
                    $experiencePoints += $employmentHistory->getYearsCount() * $this->container->getParameter('one_year_experience_points');
                }
                $cv->setEmploymentHistoryPoints($experiencePoints);
                $cv->setTotalPoints();
            }
            $em->flush();
            $request->getSession()->setFlash('success', 'Deleted Successfuly');
        }
        return $this->redirect($this->generateUrl('fb_student_task', array('loginName' => $this->get('security.context')->getToken()->getUser()->getLoginName())));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

}

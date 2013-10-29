<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Objects\InternJumpBundle\Entity\Education;
use Objects\InternJumpBundle\Form\EducationType;

/**
 * Education controller.
 *
 */
class FacebookEducationController extends Controller {

    /**
     * signup third step
     * @author Mahmoud
     */
    public function signupEducationAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        if (count($user->getEducations()) == 0) {
            //add one education entity to the user
            $user->addEducation(new Education());
        }
        //create an education form
        $formBuilder = $this->createFormBuilder($user, array(
                    'validation_groups' => 'education'
                ))
                ->add('educations', 'collection', array('type' => new EducationType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false));
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //update the user education points
                $educationPoints = 0;
//                foreach ($user->getEducations() as $education) {
//                    if ($education->getDegree()) {
//                        $educationPoints += $this->container->getParameter($education->getDegree() . '_degree_points');
//                    }
//                    if ($education->getCumulativeGPA()) {
//                        $educationPoints += $this->container->getParameter($education->getCumulativeGPA() . '_grade_points');
//                    }
//                }
                $user->setEducationsPoints($educationPoints);
                //update all the cvs points
                foreach ($user->getCvs() as $cv) {
                    $cv->setTotalPoints();
                }
                //save the user data
                $this->getDoctrine()->getEntityManager()->flush();
                return $this->redirect($this->generateUrl('fb_signup_language'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:FacebookEducation:signup_education.html.twig', array(
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentSignUpEducation_FormName'),
                    'formDesc' => $this->container->getParameter('studentSignUpEducation_FormDesc'),
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
            throw new AccessDeniedHttpException('This Education is not yours');
        }
    }

    /**
     * Displays a form to create a new Education entity.
     *
     */
    public function newAction() {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $entity = new Education();
        $form = $this->createForm(new EducationType(), $entity, array('validation_groups' => 'education'));
        return $this->render('ObjectsInternJumpBundle:FacebookEducation:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentAddEducation_FormName'),
                    'formDesc' => $this->container->getParameter('studentAddEducation_FormDesc'),
                ));
    }

    /**
     * Creates a new Education entity.
     *
     */
    public function createAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $entity = new Education();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        $entity->setUser($user);
        $request = $this->getRequest();
        $form = $this->createForm(new EducationType(), $entity, array('validation_groups' => 'education'));
        $form->bindRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            //update the user education points
            $educationPoints = 0;
//            if ($entity->getDegree()) {
//                $educationPoints += $this->container->getParameter($entity->getDegree() . '_degree_points');
//            }
//            if ($entity->getCumulativeGPA()) {
//                $educationPoints += $this->container->getParameter($entity->getCumulativeGPA() . '_grade_points');
//            }
            $user->setEducationsPoints($user->getEducationsPoints() + $educationPoints);
            //update all the cvs points
            foreach ($user->getCvs() as $cv) {
                $cv->setTotalPoints();
            }
            $em->persist($entity);
            $em->flush();
            $request->getSession()->setFlash('success', 'Created Successfuly');
            return $this->redirect($this->generateUrl('fb_education_new'));
        }
        return $this->render('ObjectsInternJumpBundle:FacebookEducation:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentAddEducation_FormName'),
                    'formDesc' => $this->container->getParameter('studentAddEducation_FormDesc'),
                ));
    }

    /**
     * Displays a form to edit an existing Education entity.
     *
     */
    public function editAction($id) {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:Education')->find($id);
        if (!$entity) {
            $message = $this->container->getParameter('education_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }
        $this->checkUserOwnObject($entity);
        $editForm = $this->createForm(new EducationType(), $entity, array('validation_groups' => 'education'));
        $deleteForm = $this->createDeleteForm($id);
        return $this->render('ObjectsInternJumpBundle:FacebookEducation:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'formName' => $this->container->getParameter('studentEditEducation_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditEducation_FormDesc'),
                ));
    }

    /**
     * Edits an existing Education entity.
     *
     */
    public function updateAction($id) {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:Education')->find($id);
        if (!$entity) {
            $message = $this->container->getParameter('education_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }
        $this->checkUserOwnObject($entity);
        $editForm = $this->createForm(new EducationType(), $entity, array('validation_groups' => 'education'));
        $deleteForm = $this->createDeleteForm($id);
        $request = $this->getRequest();
        $editForm->bindRequest($request);
        if ($editForm->isValid()) {
            //get the user object
            $user = $entity->getUser();
            //update the user education points
            $educationPoints = 0;
//            foreach ($user->getEducations() as $education) {
//                if ($education->getDegree()) {
//                    $educationPoints += $this->container->getParameter($education->getDegree() . '_degree_points');
//                }
//                if ($education->getCumulativeGPA()) {
//                    $educationPoints += $this->container->getParameter($education->getCumulativeGPA() . '_grade_points');
//                }
//            }
            $user->setEducationsPoints($educationPoints);
            //update all the cvs points
            foreach ($user->getCvs() as $cv) {
                $cv->setTotalPoints();
            }
            $em->flush();
            $request->getSession()->setFlash('success', 'Edited Successfuly');
            return $this->redirect($this->generateUrl('fb_education_new'));
        }
        return $this->render('ObjectsInternJumpBundle:FacebookEducation:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'formName' => $this->container->getParameter('studentEditEducation_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditEducation_FormDesc'),
                ));
    }

    /**
     * Deletes a Education entity.
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
            $entity = $em->getRepository('ObjectsInternJumpBundle:Education')->find($id);
            if (!$entity) {
                $message = $this->container->getParameter('education_not_found_error_msg');
                return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
            }
            $this->checkUserOwnObject($entity);
            //get the user object
            $user = $entity->getUser();
            //remove the education
            $em->remove($entity);
            $em->flush();
            //update the user education points
            $educationPoints = 0;
//            foreach ($user->getEducations() as $education) {
//                if ($education->getDegree()) {
//                    $educationPoints += $this->container->getParameter($education->getDegree() . '_degree_points');
//                }
//                if ($education->getGrade()) {
//                    $educationPoints += $this->container->getParameter($education->getGrade() . '_grade_points');
//                }
//            }
            $user->setEducationsPoints($educationPoints);
            //update all the cvs points
            foreach ($user->getCvs() as $cv) {
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

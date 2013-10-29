<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Objects\APIBundle\Controller\TwitterController;
use Objects\APIBundle\Controller\LinkedinController;
use Objects\APIBundle\Controller\FacebookController;
use Objects\InternJumpBundle\Entity\CV;
use Objects\InternJumpBundle\Form\CVType;

/**
 * FacebookCVController.
 * @author Mahmoud
 */
class FacebookCVController extends Controller {

    /**
     * the cv create first step
     */
    public function signupCVAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        $new = TRUE;
        if ($user->getCvs()->first()) {
            $cv = $user->getCvs()->first();
            $new = FALSE;
        } else {
            //create new cv object
            $cv = new CV();
            $cv->setUser($user);
            $user->addCV($cv);
        }
        //create the form
        $form = $this->createForm(new CVType(), $cv);
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //save the user data
                $this->getDoctrine()->getEntityManager()->flush();
                //check if this is the first time to create the cv
                if ($new) {
                    //check if user have social acounts
                    $userSocialAccounts = $user->getSocialAccounts();
                    $container = $this->container;
                    if ($userSocialAccounts) {
                        $status = 'I just created my resume on InternJump, boy was it easy!!';
                        $link = $this->generateUrl('site_fb_homepage', array(), true);

                        // check if have facebook
                        if ($userSocialAccounts->isFacebookLinked()) {
                            FacebookController::postOnUserWallAndFeedAction($userSocialAccounts->getFacebookId(), $userSocialAccounts->getAccessToken(), $status, null, null, $link, NULL);
                        }

                        // check if have twitter
                        if ($userSocialAccounts->isTwitterLinked()) {
                            TwitterController::twitt($status . ' ' . $link, $container->getParameter('consumer_key'), $container->getParameter('consumer_secret'), $userSocialAccounts->getOauthToken(), $userSocialAccounts->getOauthTokenSecret());
                        }

                        // check if have linkedin
                        if ($userSocialAccounts->isLinkedInLinked()) {
                            LinkedinController::linkedInShare($container->getParameter('linkedin_api_key'), $container->getParameter('linkedin_secret_key'), $userSocialAccounts->getLinkedinOauthToken(), $userSocialAccounts->getLinkedinOauthTokenSecret(), $status, $status, $status, $link, NULL);
                        }
                    }
                }
                return $this->redirect($this->generateUrl('fb_signup_cv_skills'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:FacebookCV:signup_cv.html.twig', array(
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentSignUpCv_FormName'),
                    'formName1' => $this->container->getParameter('studentSignUpCv_FormName1'),
                    'formDesc' => $this->container->getParameter('studentSignUpCv_FormDesc'),
                ));
    }

    /**
     * the cv create success step
     * @author Mahmoud
     */
    public function signupCVSuccessAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        return $this->render('ObjectsInternJumpBundle:FacebookCV:signup_cv_success.html.twig', array(
                    'loginName' => $user->getLoginName(),
                    'cvId' => $user->getCvs()->first()->getId()
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
            throw new AccessDeniedHttpException('This CV is not yours');
        }
    }

    /**
     * the edit cv employment history page
     * @param integer $id
     */
    public function editCVEmloymentHistoryAction($id) {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:CV')->find($id);
        if (!$entity) {
             $message = $this->container->getParameter('cv_not_found_error_msg');
             return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }
        $this->checkUserOwnObject($entity);
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //get the user skills
        $userEmploymentHistories = $user->getEmploymentHistories();
        //get the cv employment histories
        $cvEmploymentHistories = $entity->getEmploymentHistory();
        //initialize the button text
        $buttonText = 'Save';
        //check if we are in a cv create wizard
        if ($request->getSession()->has('cvCreate')) {
            //change the button text
            $buttonText = 'Next';
        }
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //get the submited skills
            $cv_employment_histories = $request->get('cv_employment_histories', array());
            $entity->getEmploymentHistory()->clear();
            $empoymentHistoryPoints = 0;
            foreach ($userEmploymentHistories as $employmentHistory) {
                //check if the cv has this skill
                if (in_array($employmentHistory->getId(), $cv_employment_histories)) {
                    //add skill
                    $entity->addEmploymentHistory($employmentHistory);
                    $empoymentHistoryPoints += $employmentHistory->getYearsCount() * $this->container->getParameter('one_year_experience_points');
                }
            }
            $entity->setEmploymentHistoryPoints($empoymentHistoryPoints);
            //update the total points
            $entity->setTotalPoints();
            //save the user data
            $this->getDoctrine()->getEntityManager()->flush();
            if ($request->getSession()->has('cvCreate')) {
                //remove the flag
                $request->getSession()->remove('cvCreate');
            }
            return $this->redirect($this->generateUrl('fb_user_portal_home', array('loginName' => $user->getLoginName(), 'cvId' => $entity->getId())));
        }
        return $this->render('ObjectsInternJumpBundle:FacebookCV:cv_employment_histories.html.twig', array(
                    'entity' => $entity,
                    'userEmploymentHistories' => $userEmploymentHistories,
                    'cvEmploymentHistories' => $cvEmploymentHistories,
                    'buttonText' => $buttonText,
                    'formName' => $this->container->getParameter('studentEditResumeEmpHistory_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditResumeEmpHistory_FormDesc'),
                ));
    }

    /**
     * the edit cv skills page
     * @param integer $id
     */
    public function editCVSkillsAction($id) {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:CV')->find($id);
        if (!$entity) {
            $message = $this->container->getParameter('cv_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }
        $this->checkUserOwnObject($entity);
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //get the user skills
        $userSkills = $user->getSkills();
        //get the cv skills
        $cvSkills = $entity->getSkills();
        //initialize the button text
        $buttonText = 'Save';
        //check if we are in a cv create wizard
        if ($request->getSession()->has('cvCreate')) {
            //change the button text
            $buttonText = 'Next';
        }
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //get the submited skills
            $cv_skills = $request->get('cv_skills', array());
            $entity->getSkills()->clear();
            foreach ($userSkills as $userSkill) {
                //check if the cv has this skill
                if (in_array($userSkill->getId(), $cv_skills)) {
                    //add skill
                    $entity->addSkill($userSkill);
                }
            }
            //set the cv skills points
            $entity->setSkillsPoints(count($entity->getSkills()) * $this->container->getParameter('skill_point'));
            //update the total points
            $entity->setTotalPoints();
            //save the user data
            $this->getDoctrine()->getEntityManager()->flush();
            //check if we are in a cv create wizard
            if ($request->getSession()->has('cvCreate')) {
                return $this->redirect($this->generateUrl('fb_cv_employment_history', array('id' => $entity->getId())));
            }
        }
        return $this->render('ObjectsInternJumpBundle:FacebookCV:cv_skills.html.twig', array(
                    'entity' => $entity,
                    'userSkills' => $userSkills,
                    'cvSkills' => $cvSkills,
                    'buttonText' => $buttonText,
                    'formName' => $this->container->getParameter('studentEditCvAddSkill_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditCvAddSkill_FormDesc'),
                ));
    }

    /**
     * Displays a form to create a new CV entity.
     *
     */
    public function newAction() {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $entity = new CV();
        $form = $this->createForm(new CVType(), $entity);
        return $this->render('ObjectsInternJumpBundle:FacebookCV:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentAddResume_FormName'),
                    'formDesc' => $this->container->getParameter('studentAddResume_FormDesc'),
                ));
    }

    /**
     * Creates a new CV entity.
     *
     */
    public function createAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        $entity = new CV();
        $entity->setUser($user);
        $request = $this->getRequest();
        $form = $this->createForm(new CVType(), $entity);
        $form->bindRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($entity);
            $em->flush();
            $request->getSession()->set('cvCreate', TRUE);
            //check if user have social acounts
            $userSocialAccounts = $user->getSocialAccounts();
            $container = $this->container;
            if ($userSocialAccounts) {
                $status = 'I just created my resume on InternJump, boy was it easy!';
                $link = $this->generateUrl('site_fb_homepage', array(), true);

                // check if have facebook
                if ($userSocialAccounts->isFacebookLinked()) {
                    FacebookController::postOnUserWallAndFeedAction($userSocialAccounts->getFacebookId(), $userSocialAccounts->getAccessToken(), $status, null, null, $link, NULL);
                }

                // check if have twitter
                if ($userSocialAccounts->isTwitterLinked()) {
                    TwitterController::twitt($status . ' ' . $link, $container->getParameter('consumer_key'), $container->getParameter('consumer_secret'), $userSocialAccounts->getOauthToken(), $userSocialAccounts->getOauthTokenSecret());
                }

                // check if have linkedin
                if ($userSocialAccounts->isLinkedInLinked()) {
                    LinkedinController::linkedInShare($container->getParameter('linkedin_api_key'), $container->getParameter('linkedin_secret_key'), $userSocialAccounts->getLinkedinOauthToken(), $userSocialAccounts->getLinkedinOauthTokenSecret(), $status, $status, $status, $link, NULL);
                }
            }
            return $this->redirect($this->generateUrl('fb_cv_skills', array('id' => $entity->getId())));
        }
        return $this->render('ObjectsInternJumpBundle:FacebookCV:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),

                ));
    }

    /**
     * Displays a form to edit an existing CV entity.
     *
     */
    public function editAction($id) {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:CV')->find($id);
        if (!$entity) {
            $message = $this->container->getParameter('cv_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }
        $this->checkUserOwnObject($entity);
        $editForm = $this->createForm(new CVType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
        return $this->render('ObjectsInternJumpBundle:FacebookCV:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'formName' => $this->container->getParameter('studentEditResume_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditResume_FormDesc'),
                ));
    }

    /**
     * Edits an existing CV entity.
     *
     */
    public function updateAction($id) {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:CV')->find($id);
        if (!$entity) {
            $message = $this->container->getParameter('cv_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }
        $this->checkUserOwnObject($entity);
        $editForm = $this->createForm(new CVType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
        $request = $this->getRequest();
        $editForm->bindRequest($request);
        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $request->getSession()->setFlash('success', 'Edited Successfuly');
            return $this->redirect($this->generateUrl('fb_cv_edit', array('id' => $id)));
        }
        return $this->render('ObjectsInternJumpBundle:FacebookCV:edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                ));
    }

    /**
     * Deletes a CV entity.
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
            $entity = $em->getRepository('ObjectsInternJumpBundle:CV')->find($id);
            if (!$entity) {
                $message = $this->container->getParameter('cv_not_found_error_msg');
                return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
            }
            $this->checkUserOwnObject($entity);
            $em->remove($entity);
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

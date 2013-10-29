<?php

namespace Objects\InternJumpBundle\Controller;

use Objects\InternJumpBundle\Entity\Skill;
use Objects\InternJumpBundle\Form\SkillType;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Skill controller.
 *
 */
class SkillController extends Controller {

    /**
     * this function will delete user skill
     * @author Ahmed
     * @param int $skillId
     */
    public function userRemoveSkillAction($skillId) {
        //check for logrdin user
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            return new Response('faild');
        }
        $em = $this->getDoctrine()->getEntityManager();
        $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');

        //get skill object
        $skillObject = $skillRepo->find($skillId);
        //decrement skill user count
        $skillObject->setUsersCount($skillObject->getUsersCount() - 1);

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        $user->getSkills()->removeElement($skillObject);

        //remove this skill from user cvs
        $userCvs = $user->getCvs();
        foreach ($userCvs as $cv) {
            if ($cv->getSkills()->contains($skillObject)) {
                //remove skill points from this cv
                $skillPoints = $this->container->getParameter('skill_point');
                $cv->setSkillsPoints($cv->getSkillsPoints() - $skillPoints);
                $cv->setTotalPoints();
                $cv->getSkills()->removeElement($skillObject);
            }
        }

        $em->flush();

        return new Response('done');
    }

    /**
     * this function will save the user skills
     * @author Ahmed
     * @param array $skills
     */
    public function submitAddEditUserSkillsAction($skills) {
        //check for logrdin user
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            return new Response('faild');
        }
        $em = $this->getDoctrine()->getEntityManager();
        $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');

        $skills = explode(',', $skills);

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        foreach ($skills as $skill) {
            $skill = trim($skill);
            //check if this skill not exist in database
            $skillObject = $skillRepo->findOneBy(array('title' => $skill));
            if (!$skillObject) {
                //create new skill
                $newSkill = new Skill();
                $newSkill->setTitle($skill);
                $newSkill->setUsersCount(1);
                $em->persist($newSkill);
                //add the skill to the user
                $user->addSkill($newSkill);
            } else {
                //check if the user have this skill
                if (!$user->getSkills()->contains($skillObject)) {
                    //add the skill to the user
                    $user->addSkill($skillObject);
                    //increment skill user count
                    $skillObject->setUsersCount($skillObject->getUsersCount() + 1);
                }
            }
        }

        $em->flush();

        return new Response('done');
    }

    /**
     * this function will used to add/edit logedin user skills
     * @author Ahmed
     */
    public function addEditUserSkillsAction() {
        //check for logrdin user
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //get all user skills
        $userSkills = $user->getSkills();

        return $this->render('ObjectsInternJumpBundle:Skill:addEditUserSkills.html.twig', array(
                    'userSkills' => $userSkills,
                    'formName' => $this->container->getParameter('studentEditSkill_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditSkill_FormDesc'),
        ));
    }

    /**
     * this function will used to add/edit logedin user skills
     * @author Ahmed
     */
    public function fb_addEditUserSkillsAction() {
        //check for logrdin user
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //get all user skills
        $userSkills = $user->getSkills();

        return $this->render('ObjectsInternJumpBundle:Skill:fb_addEditUserSkills.html.twig', array(
                    'userSkills' => $userSkills,
                    'formName' => $this->container->getParameter('studentEditSkill_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditSkill_FormDesc'),
        ));
    }

    /**
     * the cv create second step
     * @author Mahmoud
     */
    public function signupCVSkillsAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //the current user skills ids
        $currentSkillsIds = array();
        foreach ($user->getSkills() as $userSkill) {
            //add the skill to the user skills ids array
            $currentSkillsIds[$userSkill->getId()] = TRUE;
        }
        if (count($user->getSkills()) == 0) {
            //add one skill entity to the user
            $user->addSkill(new Skill());
        }
        //create an education form
        $formBuilder = $this->createFormBuilder($user, array('validation_groups' => array('skill')))
                ->add('skills', 'collection', array('type' => new SkillType(), 'allow_add' => true, 'allow_delete' => true));
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //get the entity manager
                $em = $this->getDoctrine()->getEntityManager();
                $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');
                //the new skills array
                $newTitles = array();
                $skillsIds = array();
                $skills = new ArrayCollection();
                foreach ($user->getSkills() as $userSkill) {
                    if ($userSkill->getId()) {
                        $em->detach($userSkill);
                    }
                    //check if we have this skill in our database
                    $skill = $skillRepo->findOneByTitle($userSkill->getTitle());
                    if ($skill) {
                        if (!isset($skillsIds[$skill->getId()])) {
                            //add the database object
                            $skills->add($skill);
                            $skillsIds[$skill->getId()] = true;
                            //check if we increased this skill before
                            if (!isset($currentSkillsIds[$skill->getId()])) {
                                //increase the users count for this skill
                                $skillRepo->increaseSkillUsersCount($skill->getId());
                            }
                        }
                    } else {
                        //check if we added this skill before
                        if (!isset($newTitles[$userSkill->getTitle()])) {
                            //add the new skill
                            $newTitles[$userSkill->getTitle()] = true;
                            $newSkill = new Skill();
                            $newSkill->setTitle($userSkill->getTitle());
                            $em->persist($newSkill);
                            $skills->add($newSkill);
                        }
                    }
                }
                $user->setSkills($skills);
                //set the skills to the cv
                $cv = $user->getCvs()->first();
                //set the cv skills
                $cv->setSkills($skills);
                //set the cv skills points
                $cv->setSkillsPoints(count($skills) * $this->container->getParameter('skill_point'));
                //update the total points
                $cv->setTotalPoints();
                //save the user data
                $em->flush();
                return $this->redirect($this->generateUrl('signup_cv_experience'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:Skill:signup_cv_skills.html.twig', array(
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentSignUpCvSkills_FormName'),
                    'formDesc' => $this->container->getParameter('studentSignUpCvSkills_FormDesc'),
        ));
    }

    /**
     * the cv create second step
     * @author Mahmoud
     */
    public function facebookSignupCVSkillsAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //the current user skills ids
        $currentSkillsIds = array();
        foreach ($user->getSkills() as $userSkill) {
            //add the skill to the user skills ids array
            $currentSkillsIds[$userSkill->getId()] = TRUE;
        }
        if (count($user->getSkills()) == 0) {
            //add one skill entity to the user
            $user->addSkill(new Skill());
        }
        //create an education form
        $formBuilder = $this->createFormBuilder($user, array('validation_groups' => array('skill')))
                ->add('skills', 'collection', array('type' => new SkillType(), 'allow_add' => true, 'allow_delete' => true));
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //get the entity manager
                $em = $this->getDoctrine()->getEntityManager();
                $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');
                //the new skills array
                $newTitles = array();
                $skillsIds = array();
                $skills = new ArrayCollection();
                foreach ($user->getSkills() as $userSkill) {
                    if ($userSkill->getId()) {
                        $em->detach($userSkill);
                    }
                    //check if we have this skill in our database
                    $skill = $skillRepo->findOneByTitle($userSkill->getTitle());
                    if ($skill) {
                        if (!isset($skillsIds[$skill->getId()])) {
                            //add the database object
                            $skills->add($skill);
                            $skillsIds[$skill->getId()] = true;
                            //check if we increased this skill before
                            if (!isset($currentSkillsIds[$skill->getId()])) {
                                //increase the users count for this skill
                                $skillRepo->increaseSkillUsersCount($skill->getId());
                            }
                        }
                    } else {
                        //check if we added this skill before
                        if (!isset($newTitles[$userSkill->getTitle()])) {
                            //add the new skill
                            $newTitles[$userSkill->getTitle()] = true;
                            $newSkill = new Skill();
                            $newSkill->setTitle($userSkill->getTitle());
                            $em->persist($newSkill);
                            $skills->add($newSkill);
                        }
                    }
                }
                $user->setSkills($skills);
                //set the skills to the cv
                $cv = $user->getCvs()->first();
                //set the cv skills
                $cv->setSkills($skills);
                //set the cv skills points
                $cv->setSkillsPoints(count($skills) * $this->container->getParameter('skill_point'));
                //update the total points
                $cv->setTotalPoints();
                //save the user data
                $em->flush();
                return $this->redirect($this->generateUrl('fb_signup_cv_experience'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:Skill:facebook_signup_cv_skills.html.twig', array(
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentSignUpCvSkills_FormName'),
                    'formDesc' => $this->container->getParameter('studentSignUpCvSkills_FormDesc'),
        ));
    }

    /**
     * the action is used by the skill auto complete script
     * @author Mahmoud
     */
    public function getSkillsAction() {
        $skills = $this->getDoctrine()->getRepository('ObjectsInternJumpBundle:Skill')->getSkills($this->getRequest()->get('term'), $this->container->getParameter('skills_autocomplete_number'));
        $data = array();
        foreach ($skills as $skill) {
            $data [] = $skill['title'];
        }
        return new Response(json_encode($data), 200, array('Content-type' => 'application/json'));
    }

    /**
     * Lists all Skill entities.
     *
     */
    public function StudentAllSkillsAction() {

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            if ($flag == "facebook") {
                return $this->redirect($this->generateUrl('site_homepage', array('access_method' => "facebook")));
            } else {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }

        //Get current user
        $user = $this->get('security.context')->getToken()->getUser();
        $uid = $user->getId();


        $em = $this->getDoctrine()->getEntityManager();
        $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');

        $skills = $skillRepo->getStudentAllSkills($uid);
        //     $entities = $em->getRepository('ObjectsInternJumpBundle:Skill')->findAll();


        return $this->render('ObjectsInternJumpBundle:Skill:allSkills.html.twig', array(
                    'entities' => $skills,
                    'flag' => $flag,
        ));
    }

    /**
     * Finds and displays a Skill entity.
     *
     */
    public function showAction($id) {
        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            if ($flag == "facebook") {
                return $this->redirect($this->generateUrl('site_homepage', array('access_method' => "facebook")));
            } else {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('ObjectsInternJumpBundle:Skill')->find($id);

        if (!$entity) {
            if ($flag == "facebook") {
                return $this->redirect($this->generateUrl('site_homepage', array('access_method' => "facebook")));
            } else {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }

        $deleteForm = $this->createDeleteForm($id);


        return $this->render('ObjectsInternJumpBundle:Skill:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
                    'flag' => $flag,
        ));
    }

    /**
     * Displays a form to create a new Skill entity.
     *
     */
    public function newSkillAction() {

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            if ($flag == "facebook") {
                return $this->redirect($this->generateUrl('site_homepage', array('access_method' => "facebook")));
            } else {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }

        $entity = new Skill();
        $form = $this->createForm(new SkillType(), $entity);


        return $this->render('ObjectsInternJumpBundle:Skill:newSkill.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'flag' => $flag,
        ));
    }

    /**
     * Creates a new Skill entity.
     *
     */
    public function createAction() {
        $em = $this->getDoctrine()->getEntityManager();

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        $entity = new Skill();
        $request = $this->getRequest();
        $form = $this->createForm(new SkillType(), $entity);
        $form->bindRequest($request);


        $skill = $em->getRepository('ObjectsInternJumpBundle:Skill')->findOneBy(array('title' => $entity->getTitle()));

        if ($skill) {
            //echo "yes exist";exit;
            //Get current user
            $user = $this->get('security.context')->getToken()->getUser();
            $user->addSkill($skill);
            $em->persist($user);
            $em->flush();
        } else {
            //echo "doesn't exist";exit;

            if ($form->isValid()) {
                $em->persist($entity);
                $em->flush();
                $skill = $em->getRepository('ObjectsInternJumpBundle:Skill')->findOneBy(array('title' => $entity->getTitle()));
                //Get current user
                $user = $this->get('security.context')->getToken()->getUser();
                $user->addSkill($skill);
                $em->persist($user);
                $em->flush();
            }


            if (isset($_POST['create'])) {

                if ($flag == "facebook") {
                    return $this->redirect($this->generateUrl('skill', array('flag' => $flag, 'access_method' => "facebook")));
                } else {
                    return $this->redirect($this->generateUrl('skill', array('flag' => $flag)));
                }
            } elseif (isset($_POST['create-add-another'])) {
                return $this->redirect($this->generateUrl('skill_new'));
            }


            return $this->render('ObjectsInternJumpBundle:Skill:newSkill.html.twig', array(
                        'entity' => $entity,
                        'form' => $form->createView(),
                        'flag' => $flag,
            ));
        }
    }

    /**
     * Displays a form to edit an existing Skill entity.
     *
     */
    public function editSkillAction($id) {

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        $em = $this->getDoctrine()->getEntityManager();

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        $entity = $em->getRepository('ObjectsInternJumpBundle:Skill')->find($id);

        if (!$entity) {
            if ($flag == "facebook") {
                return $this->redirect($this->generateUrl('site_homepage', array('access_method' => "facebook")));
            } else {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }

        $editForm = $this->createForm(new SkillType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ObjectsInternJumpBundle:Skill:editSkill.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'flag' => $flag,
        ));
    }

    /**
     * Edits an existing Skill entity.
     *
     */
    public function updateAction($id) {

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ObjectsInternJumpBundle:Skill')->find($id);

        if (!$entity) {
            if ($flag == "facebook") {
                return $this->redirect($this->generateUrl('site_homepage', array('access_method' => "facebook")));
            } else {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }

        $editForm = $this->createForm(new SkillType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->bindRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('skill_edit', array('id' => $id, 'flag' => $flag)));
        }

        return $this->render('ObjectsInternJumpBundle:Skill:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Skill entity.
     *
     */
    public function deleteAction($id) {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('ObjectsInternJumpBundle:Skill')->find($id);

            if (!$entity) {
                $message = $this->container->getParameter('skill_not_found_error_msg');
                return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                            'message' => $message,));
            }

            $em->remove($entity);
            $em->flush();
        }

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        return $this->redirect($this->generateUrl('skill', array('flag' => $flag)));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

    public function deleteSkillAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        //Check if inside facebook or Not
        //$url = $this->getRequest()->getHost();
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        $skill = $entity = $em->getRepository('ObjectsInternJumpBundle:Skill')->find($id);


        if ($skill) {
            //Get current user
            $user = $this->get('security.context')->getToken()->getUser();
            foreach ($user->getSkills() as $key => $myskill) {
                if ($myskill->getId() == $id) {
                    $user->getSkills()->removeElement($myskill);
                }
            }
            $em->flush();

            return $this->redirect($this->generateUrl('skill', array('flag' => $flag)));
        }
    }

    public function checkWhere($url) {

        if (isset($url)) {
            $url = $this->getRequest()->get('access_method');
        }

        if ($url == 'facebook') {
            $flag = "facebook";
            return $flag;
            //echo "Yes, inside facebook";exit;
        } else {
            $flag = "normal";
            return $flag;
            //echo "Normal";exit;
        }
    }

}

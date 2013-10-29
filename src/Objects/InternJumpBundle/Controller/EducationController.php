<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Objects\InternJumpBundle\Entity\Education;
use Objects\InternJumpBundle\Form\EducationType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Education controller.
 *
 */
class EducationController extends Controller {

    /**
     * the undergraduate degrees auto complete action
     * @author Mahmoud
     * @param string $for graduate or undergraduate
     */
    public function getEducationDegreesAction($for) {
        $graduateDegrees = array(
            "Doctor of Business Administration",
            "Doctor of Canon Law",
            "Doctor of Chiropractic",
            "Doctor of Commerce",
            "Doctor of Dental Surgery",
            "Doctor of Divinity",
            "Doctor of Education",
            "Doctor of Engineering Science",
            "Doctor of Health Administration",
            "Doctor of Health Science",
            "Doctor of Juridical Science; Juris Doctor",
            "Doctor of Law; Legum Doctor",
            "Doctor of Liberal Studies",
            "Doctor of Management",
            "Doctor of Medicine",
            "Doctor of Ministry",
            "Doctor of Musical Arts",
            "Doctor of Osteopathic Medicine",
            "Doctor of Optometry",
            "Doctor of Pharmacy",
            "Doctor of Philosophy",
            "Doctor of Public Administration",
            "Doctor of Science",
            "Doctor of Theology",
            "Doctor of Veterinary Medicine",
        );
        $underGraduateDegrees = array(
            "Bachelor of Architecture",
            "Bachelor of Biomedical Science",
            "Bachelor of Business Administration",
            "Bachelor of Clinical Science",
            "Bachelor of Commerce",
            "Bachelor of Computer Information Systems",
            "Bachelor of Science in Construction Technology",
            "Bachelor of Criminal Justice",
            "Bachelor of Divinity",
            "Bachelor of Economics",
            "Bachelor of Education",
            "Bachelor of Engineering",
            "Bachelor of Fine Arts",
            "Bachelor of Information Systems",
            "Bachelor of Management",
            "Bachelor of Music",
            "Bachelor of Pharmacy",
            "Bachelor of Philosophy",
            "Bachelor of Social Work",
            "Bachelor of Accountancy",
            "Bachelor of Arts in American Studies",
            "Bachelor of Arts in Biology",
            "Bachelor of Science in Aerospace Engineering",
            "Bachelor of Science in Actuarial",
            "Bachelor of Science in Agriculture",
            "Bachelor of Science in Architecture",
            "Bachelor of Science in Architectural Engineering",
            "Bachelor of Science in Biology",
            "Bachelor of Science in Biomedical Engineering",
            "Bachelor of Science in Business Administration",
            "Bachelor of Science in Chemical Engineering",
            "Bachelor of Science in Chemistry",
            "Bachelor of Science in Civil Engineering",
            "Bachelor of Science in Clinical Laboratory Science",
            "Bachelor of Science in Computer Engineering",
            "Bachelor of Science in Computer Science",
            "Bachelor of Science in Construction Engineering",
            "Bachelor of Science in Construction Management",
            "Bachelor of Science in Criminal Justice",
            "Bachelor of Science in Criminology",
            "Bachelor of Science in Diagnostic Radiography",
            "Bachelor of Science in Education",
            "Bachelor of Science in Electrical Engineering",
            "Bachelor of Science in Engineering Physics",
            "Bachelor of Science in Engineering Science",
            "Bachelor of Science in Engineering Technology",
            "Bachelor of Science in English Literature",
            "Bachelor of Science in Environmental Engineering",
            "Bachelor of Science in Environmental Science",
            "Bachelor of Science in Environmental Studies",
            "Bachelor of Science in Food Science",
            "Bachelor of Science in Foreign Service",
            "Bachelor of Science in Forensic Science",
            "Bachelor of Science in Forestry",
            "Bachelor of Science in History",
            "Bachelor of Science in Hospitality Management",
            "Bachelor of Science in Human Resources Management",
            "Bachelor of Science in Industrial Engineering",
            "Bachelor of Science in Information Technology",
            "Bachelor of Science in Information Systems",
            "Bachelor of Science in Integrated Science, Business and Technology",
            "Bachelor of Science in International Relations",
            "Bachelor of Science in Journalism",
            "Bachelor of Science in Management",
            "Bachelor of Science in Manufacturing Engineering",
            "Bachelor of Science in Marketing",
            "Bachelor of Science in Mathematics",
            "Bachelor of Science in Mechanical Engineering",
            "Bachelor of Science in Medical Technology",
            "Bachelor of Science in Meteorology",
            "Bachelor of Science in Microbiology",
            "Bachelor of Science in Mining Engineering",
            "Bachelor of Science in Molecular Biology",
            "Bachelor of Science in Neuroscience",
            "Bachelor of Science in Nursing",
            "Bachelor of Science in Nutrition science",
            "Bachelor of Science in Occupational Therapy",
            "Bachelor of Science in Petroleum Engineering",
            "Bachelor of Science in Podiatry",
            "Bachelor of Science in Pharmacology",
            "Bachelor of Science in Pharmacy",
            "Bachelor of Science in Physical Therapy",
            "Bachelor of Science in Physics",
            "Bachelor of Science in Plant Science",
            "Bachelor of Science in Politics",
            "Bachelor of Science in Psychology",
            "Bachelor of Science in Quantity Surveying Engineering",
            "Bachelor of Science in Radiologic Technology",
            "Bachelor of Science in Real-Time Interactive Simulation",
            "Bachelor of Science in Religion",
            "Bachelor of Science in Respiratory Therapy",
            "Bachelor of Science in Risk Management and Insurance",
            "Bachelor of Science in Science Education",
            "Bachelor of Science in Systems Engineering",
            "Bachelor of Music in Jazz Studies",
            "Bachelor of Music in Composition",
            "Bachelor of Music in Performance",
            "Bachelor of Music in Theory",
            "Bachelor of Music in Music Education",
        );
        $term = $this->getRequest()->get('term');
        if ($for == 'graduate') {
            $degrees = preg_grep("/$term/iu", $graduateDegrees);
        } else {
            $degrees = preg_grep("/$term/iu", $underGraduateDegrees);
        }
        return new Response(json_encode(array_slice($degrees, 0, 10)), 200, array('Content-type' => 'application/json'));
    }

    /**
     * signup third step
     * @author Mahmoud
     */
    public function signupEducationAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
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
                return $this->redirect($this->generateUrl('signup_language'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:Education:signup_education.html.twig', array(
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
            return $this->redirect($this->generateUrl('login'));
        }
        $entity = new Education();
        $form = $this->createForm(new EducationType(), $entity, array('validation_groups' => 'education'));
        return $this->render('ObjectsInternJumpBundle:Education:new.html.twig', array(
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
            return $this->redirect($this->generateUrl('login'));
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
            return $this->redirect($this->generateUrl('education_new'));
        }
        return $this->render('ObjectsInternJumpBundle:Education:new.html.twig', array(
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
            return $this->redirect($this->generateUrl('login',array('access_method' => 'facebook')));
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
        return $this->render('ObjectsInternJumpBundle:Education:edit.html.twig', array(
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
            return $this->redirect($this->generateUrl('login'));
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
            return $this->redirect($this->generateUrl('education_new'));
        }
        return $this->render('ObjectsInternJumpBundle:Education:edit.html.twig', array(
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
            return $this->redirect($this->generateUrl('login'));
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
        return $this->redirect($this->generateUrl('student_task', array('loginName' => $this->get('security.context')->getToken()->getUser()->getLoginName())));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

}

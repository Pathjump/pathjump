<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Objects\APIBundle\Controller\TwitterController;
use Objects\APIBundle\Controller\FacebookController;
use Objects\APIBundle\Controller\LinkedinController;
use Objects\InternJumpBundle\Entity\UserLanguage;
use Objects\InternJumpBundle\Form\UserLanguageType;
use Objects\InternJumpBundle\Entity\Skill;
use Objects\InternJumpBundle\Entity\Education;
use Objects\InternJumpBundle\Entity\EmploymentHistory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Objects\InternJumpBundle\Entity\ReportedUser;

class InternjumpUserController extends Controller {

    /**
     * this function used to increment banner no of clicks
     * @author Mahmoud, ahmed
     * @param integer $id
     */
    public function incrementBannerNoOfClicksAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $banner = $em->getRepository('ObjectsInternJumpBundle:Banner')->find($id);
        if (!$banner) {
            throw $this->createNotFoundException('banner not found');
        }
        $banner->setNumberOfClicks($banner->getNumberOfClicks() + 1);
        $em->flush();
        return $this->redirect($banner->getUrl());
    }

    /**
     * company report user cv
     * @author ahmed
     * @param int $userId
     * @param int $cvId
     */
    public function reportUserAction($userId, $cvId) {
        //check for loggedin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $request = $this->getRequest();

        $company = $this->get('security.context')->getToken()->getUser();
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');
        $userRepo = $em->getRepository('ObjectsUserBundle:User');

        $user = $userRepo->find($userId);
        $cv = $cvRepo->find($cvId);

        //create new report user
        $newReport = new ReportedUser();
        $newReport->setCv($cv);
        $newReport->setUser($user);
        $newReport->setCompany($company);

        //create report form
        $form = $this->createFormBuilder($newReport)
                ->add('reason', 'choice', array(
                    'choices' => array(
                        'I believe this is a spam resume' => 'I believe this is a spam resume',
                        'Contains foul languages / contents' => 'Contains foul languages / contents',
                        'Others' => 'Others'
                    )
                ))
                ->add('reasonText')
                ->getForm();
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                $em->persist($newReport);
                $em->flush();

                //send email to admin
                //prepare the body of the email
                $body = $this->renderView('ObjectsInternJumpBundle:InternjumpUser:user_report.txt.twig', array(
                    'company' => $company,
                    'user' => $user,
                    'newReport' => $newReport,
                ));
                //prepare the message object
                $message = \Swift_Message::newInstance()
                        ->setSubject('Internjump - User Report')
                        ->setFrom($this->container->getParameter('contact_us_email'))
                        ->setTo($this->container->getParameter('contact_us_email'))
                        ->setBody($body)
                ;
                //send the activation mail to the user
                $this->get('mailer')->send($message);

                return $this->render('ObjectsInternJumpBundle:InternjumpUser:reportUserSuccess.html.twig', array(
                ));
            }
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:reportUser.html.twig', array(
                    'form' => $form->createView(),
                    'userId' => $userId,
                    'cvId' => $cvId
        ));
    }

    /**
     * this function used to add company to favorite list for user
     * @author ahmed
     * @param integer $companyId
     * @param string $status
     */
    public function addCompanyToFavoriteAction($companyId, $status) {
        //check if the company is already active
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            return new Response('failed');
        }
        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        //get the user
        $company = $companyRepo->find($companyId);

        if (!$company) {
            return new Response('failed');
        }

        if ($status == 'add') {
            //add the company to user favorite list
            $user->addCompany($company);
        } else {
            $user->getFavoriteComapnies()->removeElement($company);
        }
        $em->flush();

        return new Response('done');
    }

    /**
     * @author Mahmoud
     * @param string $searchString
     * @param integer $start
     * @param integer $limit
     * @param type $jobLocation
     * @return false | array
     */
    private function searchForIndeedJobs($searchString = null, $start = 1, $limit = 10, $jobLocation = null, $jobType = null, $company = "empty") {
        $userAgent = 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:16.0) Gecko/20100101 Firefox/16.0';
        $start--;
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
        $userIp = '41.178.223.' . rand(3, 200);
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $userIp = $_SERVER['REMOTE_ADDR'];
        }
        if ($jobType == "empty" || $jobType == null) {
            //$jobType = "internship";
        }

        if ($searchString) {
            $searchString = urlencode($searchString);
        }
        if ($company != "empty") {
            if ($searchString != null && $searchString != "empty")
                $searchString = urlencode("company:" . $company) . "+" . $searchString;
            else
                $searchString = urlencode("company:" . $company);
        }
        if($searchString == "empty")$searchString="";

        $apiSearchString = 'http://api.indeed.com/ads/apisearch?publisher=5399161479070076&jt=' . urlencode($jobType) . '&v=2&format=json&latlong=1&useragent=' . urlencode($userAgent) . '&userip=' . urlencode($userIp) . '&start=' . $start . '&limit=' . $limit . '&q=' . $searchString . '&sort=date'; //urlencode($searchString)

        if ($jobLocation) {
            $apiSearchString .= '&l=' . urlencode($jobLocation);
        }
        $result = file_get_contents($apiSearchString);
        if ($result === false) {
            return false;
        }
        $jobsArray = json_decode($result, true);
        $data = array();
        $data['count'] = 0;
        $data['results'] = array();
        if (isset($jobsArray['totalResults'])) {
            $data['count'] = $jobsArray['totalResults'];
        }
        $searchResults = array();
        if (isset($jobsArray['results'])) {
            $results = $jobsArray['results'];
            foreach ($results as $result) {
                $searchResult = array();
                $searchResult['jobtitle'] = '';
                $searchResult['company'] = '';
                $searchResult['snippet'] = '';
                $searchResult['city'] = '';
                $searchResult['state'] = '';
                $searchResult['country'] = '';
                $searchResult['longitude'] = '';
                $searchResult['latitude'] = '';
                $searchResult['date'] = null;
                $searchResult['formattedRelativeTime'] = '';
                $searchResult['expired'] = false;
                $searchResult['url'] = '';
                $searchResult['jobkey'] = '';
                if (isset($result['jobtitle'])) {
                    $searchResult['jobtitle'] = $result['jobtitle'];
                }
                if (isset($result['company'])) {
                    $searchResult['company'] = $result['company'];
                }
                if (isset($result['snippet'])) {
                    $searchResult['snippet'] = $result['snippet'];
                }
                if (isset($result['city'])) {
                    $searchResult['city'] = $result['city'];
                }
                if (isset($result['state'])) {
                    $searchResult['state'] = $result['state'];
                }
                if (isset($result['country'])) {
                    $searchResult['country'] = $result['country'];
                }
                if (isset($result['longitude'])) {
                    $searchResult['longitude'] = $result['longitude'];
                }
                if (isset($result['latitude'])) {
                    $searchResult['latitude'] = $result['latitude'];
                }
                if (isset($result['date'])) {
                    $searchResult['date'] = new \DateTime($result['date']);
                }
                if (isset($result['formattedRelativeTime'])) {
                    $searchResult['formattedRelativeTime'] = $result['formattedRelativeTime'];
                }
                if (isset($result['expired'])) {
                    $searchResult['expired'] = (boolean) $result['expired'];
                }
                if (isset($result['url'])) {
                    $searchResult['url'] = $result['url'];
                }
                if (isset($result['jobkey'])) {
                    $searchResult['jobkey'] = $result['jobkey'];
                }
                $searchResults[] = $searchResult;
            }
        }
        $data['results'] = $searchResults;
        return $data;
    }

    /**
     * @author Mahmoud
     * @param type $jobkey
     * @return boolean
     */
    private function getIndeedJob($jobkey) {
        $apiSearchString = 'http://api.indeed.com/ads/apigetjobs?publisher=5399161479070076&v=2&format=json&jobkeys=' . $jobkey;
        $result = file_get_contents($apiSearchString);
        if ($result === false) {
            return false;
        }
        $jobsArray = json_decode($result, true);
        $searchResult = array();
        $searchResult['jobtitle'] = '';
        $searchResult['company'] = '';
        $searchResult['snippet'] = '';
        $searchResult['city'] = '';
        $searchResult['state'] = '';
        $searchResult['country'] = '';
        $searchResult['longitude'] = '';
        $searchResult['latitude'] = '';
        $searchResult['date'] = null;
        $searchResult['formattedRelativeTime'] = '';
        $searchResult['expired'] = false;
        $searchResult['url'] = '';
        $searchResult['jobkey'] = '';
        if (isset($jobsArray['results'])) {
            $results = $jobsArray['results'];
            foreach ($results as $result) {
                if (isset($result['jobtitle'])) {
                    $searchResult['jobtitle'] = $result['jobtitle'];
                }
                if (isset($result['company'])) {
                    $searchResult['company'] = $result['company'];
                }
                if (isset($result['snippet'])) {
                    $searchResult['snippet'] = $result['snippet'];
                }
                if (isset($result['city'])) {
                    $searchResult['city'] = $result['city'];
                }
                if (isset($result['state'])) {
                    $searchResult['state'] = $result['state'];
                }
                if (isset($result['country'])) {
                    $searchResult['country'] = $result['country'];
                }
                if (isset($result['longitude'])) {
                    $searchResult['longitude'] = $result['longitude'];
                }
                if (isset($result['latitude'])) {
                    $searchResult['latitude'] = $result['latitude'];
                }
                if (isset($result['date'])) {
                    $searchResult['date'] = new \DateTime($result['date']);
                }
                if (isset($result['formattedRelativeTime'])) {
                    $searchResult['formattedRelativeTime'] = $result['formattedRelativeTime'];
                }
                if (isset($result['expired'])) {
                    $searchResult['expired'] = (boolean) $result['expired'];
                }
                if (isset($result['url'])) {
                    $searchResult['url'] = $result['url'];
                }
                if (isset($result['jobkey'])) {
                    $searchResult['jobkey'] = $result['jobkey'];
                }
            }
        }
        return $searchResult;
    }

    /**
     * signup fourth step
     * @author Mahmoud
     */
    public function signupLanguageAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        if (count($user->getLanguages()) == 0) {
            //add one education entity to the user
            $user->addUserLanguage(new UserLanguage());
        }
        //create an education form
        $formBuilder = $this->createFormBuilder($user, array(
                    'validation_groups' => 'language'
                ))
                ->add('languages', 'collection', array('type' => new UserLanguageType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false));
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                $user = $form->getData();
                foreach ($user->getLanguages() as $language) {
                    $language->setUser($user);
                }
                //save the user data
                $this->getDoctrine()->getEntityManager()->flush();
                return $this->redirect($this->generateUrl('signup_cv'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:InternjumpUser:signup_language.html.twig', array(
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentSignUpLanguage_FormName'),
                    'formDesc' => $this->container->getParameter('studentSignUpLanguage_FormDesc'),
        ));
    }

    /**
     * signup fourth step
     * @author Mahmoud
     */
    public function facebookSignupLanguageAction() {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        if (count($user->getLanguages()) == 0) {
            //add one education entity to the user
            $user->addUserLanguage(new UserLanguage());
        }
        //create an education form
        $formBuilder = $this->createFormBuilder($user, array(
                    'validation_groups' => 'language'
                ))
                ->add('languages', 'collection', array('type' => new UserLanguageType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false));
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                $user = $form->getData();
                foreach ($user->getLanguages() as $language) {
                    $language->setUser($user);
                }
                //save the user data
                $this->getDoctrine()->getEntityManager()->flush();
                return $this->redirect($this->generateUrl('fb_signup_cv'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:InternjumpUser:facebook_signup_language.html.twig', array(
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentSignUpLanguage_FormName'),
                    'formDesc' => $this->container->getParameter('studentSignUpLanguage_FormDesc'),
        ));
    }

    public function deleteLanguageAction($id) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        //get loggedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        //check if this language exist
        $language = $em->getRepository('ObjectsInternJumpBundle:UserLanguage')->find($id);
        if (!$language) {
            throw $this->createNotFoundException('Language Not Found');
        }
        if ($user->getId() !== $language->getUser()->getId()) {
            throw new AccessDeniedHttpException('This Language is not yours');
        }
        $em->remove($language);
        $em->flush();
        $this->getRequest()->getSession()->setFlash('success', 'Language Deleted successfully');
        return $this->redirect($this->generateUrl('student_task', array('loginName' => $user->getLoginName())));
    }

    /**
     * this function used to edit langauge
     * @author ahmed
     * @param integer $id
     */
    public function userEditLanguageAction($id) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        //get request object
        $request = $this->getRequest();

        //get loggedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $languageRepo = $em->getRepository('ObjectsInternJumpBundle:UserLanguage');
        //check if this language exist
        $language = $languageRepo->find($id);
        if (!$language) {
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => "This language dosn't exist."));
        }

        //check if this user the owner
        if ($user->getId() == $language->getUser()->getId()) {
            $form = $this->createForm(new UserLanguageType(), $language);

            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                if ($form->isValid()) {
                    //save the new language
                    $em->flush();

                    $request->getSession()->setFlash('success', 'Language Updated successfully');
                    return $this->redirect($this->generateUrl('user_languages'));
                }
            }

            return $this->render('ObjectsInternJumpBundle:InternjumpUser:editLanguage.html.twig', array(
                        'form' => $form->createView(),
                        'id' => $id,
                        'formName' => $this->container->getParameter('studentEditLanguage_FormName'),
                        'formDesc' => $this->container->getParameter('studentEditLanguage_FormDesc'),
            ));
        } else {
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => "You can't edit this language."));
        }
    }

    /**
     * this function used to add languages for active users
     * @author ahmed
     */
    public function userLanguagesAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        //get request object
        $request = $this->getRequest();

        //get loggedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        //create new user language
        $newUserLanguage = new UserLanguage();
        $newUserLanguage->setUser($user);
        $form = $this->createForm(new UserLanguageType(), $newUserLanguage);

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                //save the new language
                $em->persist($newUserLanguage);
                $em->flush();

                $request->getSession()->setFlash('success', 'New Language Added successfully');
                return $this->redirect($this->generateUrl('user_languages'));
            }
        }

        //get user langauges
        $userLangauges = $user->getLanguages();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:newLanguage.html.twig', array(
                    'newUserLanguage' => $newUserLanguage,
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentAddLanguage_FormName'),
                    'formDesc' => $this->container->getParameter('studentAddLanguage_FormDesc'),
                    'userLangauges' => $userLangauges
        ));
    }

    public function fb_deleteLanguageAction($id) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        //get loggedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        //check if this language exist
        $language = $em->getRepository('ObjectsInternJumpBundle:UserLanguage')->find($id);
        if (!$language) {
            throw $this->createNotFoundException('Language Not Found');
        }
        if ($user->getId() !== $language->getUser()->getId()) {
            throw new AccessDeniedHttpException('This Language is not yours');
        }
        $em->remove($language);
        $em->flush();
        $this->getRequest()->getSession()->setFlash('success', 'Language Deleted successfully');
        return $this->redirect($this->generateUrl('fb_student_task', array('loginName' => $user->getLoginName())));
    }

    /**
     * this function used to edit langauge
     * @author ahmed
     * @param integer $id
     */
    public function fb_userEditLanguageAction($id) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        //get request object
        $request = $this->getRequest();

        //get loggedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $languageRepo = $em->getRepository('ObjectsInternJumpBundle:UserLanguage');
        //check if this language exist
        $language = $languageRepo->find($id);
        if (!$language) {
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => "This language dosn't exist."));
        }

        //check if this user the owner
        if ($user->getId() == $language->getUser()->getId()) {
            $form = $this->createForm(new UserLanguageType(), $language);

            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                if ($form->isValid()) {
                    //save the new language
                    $em->flush();

                    $request->getSession()->setFlash('success', 'Language Updated successfully');
                    return $this->redirect($this->generateUrl('fb_user_languages'));
                }
            }

            return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_editLanguage.html.twig', array(
                        'form' => $form->createView(),
                        'id' => $id,
                        'formName' => $this->container->getParameter('studentEditLanguage_FormName'),
                        'formDesc' => $this->container->getParameter('studentEditLanguage_FormDesc'),
            ));
        } else {
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => "You can't edit this language."));
        }
    }

    /**
     * this function used to add languages for active users
     * @author ahmed
     */
    public function fb_userLanguagesAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //redirect to login page if user not loggedin
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        //get request object
        $request = $this->getRequest();

        //get loggedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        //create new user language
        $newUserLanguage = new UserLanguage();
        $newUserLanguage->setUser($user);
        $form = $this->createForm(new UserLanguageType(), $newUserLanguage);

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                //save the new language
                $em->persist($newUserLanguage);
                $em->flush();

                $request->getSession()->setFlash('success', 'New Language Added successfully');
                return $this->redirect($this->generateUrl('fb_user_languages'));
            }
        }

        //get user langauges
        $userLangauges = $user->getLanguages();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_newLanguage.html.twig', array(
                    'newUserLanguage' => $newUserLanguage,
                    'form' => $form->createView(),
                    'formName' => $this->container->getParameter('studentAddLanguage_FormName'),
                    'formDesc' => $this->container->getParameter('studentAddLanguage_FormDesc'),
                    'userLangauges' => $userLangauges
        ));
    }

    /**
     * this function used to extract data from user cv template
     * @author ahmed
     * @param type $documentFile
     */
    public function getUplodedCvData($documentFile) {
        //read the cv file
//        $document_file = '/opt/lampp/htdocs/symfony-projects/ij/web/cvFiles/working.doc';
        if (!file_exists($documentFile)) {
            // File doesn't exist, output error
            die('file not found');
        }
        $text_from_doc = shell_exec('antiword ' . $documentFile);

//        echo $text_from_doc;exit;
//        $text_from_doc = shell_exec('abiword --to=html ' . $document_file);
        //explode it for each new line
        $lines = explode(PHP_EOL, $text_from_doc);


        //prepare the result cv data array
        $cvDataArray = array();
        //try to get the objective
        $cvDataArray ['objective'] = '';
        $objectiveFlag = FALSE;
        foreach ($lines as $line) {
            if ($objectiveFlag) {
                //check for end of objective
                if (preg_match('/^\d*\s*(education{1}|experience{1}|skills{1})/i', trim($line))) {
                    break;
                }
                $cvDataArray ['objective'] .= $line . ' ';
            } elseif (preg_match('/^\d*\s*objective/i', trim($line))) {
                $objectiveFlag = TRUE;
            }
        }


        //try to get the skills
        $cvDataArray ['skills'] = '';
        $skillsFlag = FALSE;
        foreach ($lines as $line) {
            if ($skillsFlag) {
                //check for end of objective
                if (preg_match('/^\d*\s*(education{1}|experience{1}|objective{1})/i', trim($line))) {
                    break;
                }
                $cvDataArray ['skills'] .= trim($line);
            } elseif (preg_match('/^\d*\s*skill/i', trim($line))) {
                $skillsFlag = TRUE;
            }
        }

        //try to get the educations
        $cvDataArray ['educations'] = '';
        $skillsFlag = FALSE;
        $index = 0;
        foreach ($lines as $line) {
            if ($skillsFlag) {
                //check for end of objective
                if (preg_match('/^\d*\s*(skills{1}|experience{1}|objective{1})/i', trim($line))) {
                    break;
                }
                //intialize new education array index
                if (!isset($cvDataArray ['educations'][$index]))
                    $cvDataArray ['educations'][$index] = '';

                //check if line not the separator
                if (trim($line) != '-------------------------------------------------------') {
                    if (preg_match('/^\d*\s*School Name/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['educations'][$index]['shoolName'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*Major GPA/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['educations'][$index]['majorGPA'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*Major/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['educations'][$index]['major'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*Minor/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['educations'][$index]['minor'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*Start Date/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['educations'][$index]['startDate'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*End Date/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['educations'][$index]['endDate'] = trim($result['1']);
                    }
                }

                //check if the line is the separator to increment the array index
                if (trim($line) == '-------------------------------------------------------') {
                    $index++;
                }
            } elseif (preg_match('/^\d*\s*education/i', trim($line))) {
                $skillsFlag = TRUE;
            }
        }

        //try to get the Experience
        $cvDataArray ['experience'] = '';
        $skillsFlag = FALSE;
        $index = 0;
        foreach ($lines as $line) {
            if ($skillsFlag) {
                //check for end of objective
                if (preg_match('/^\d*\s*(skills{1}|education{1}|objective{1})/i', trim($line))) {
                    break;
                }

                //intialize new education array index
                if (!isset($cvDataArray ['experience'][$index]))
                    $cvDataArray ['experience'][$index] = '';

                //check if line not the separator
                if (trim($line) != '-------------------------------------------------------') {
                    if (preg_match('/^\d*\s*Company Name/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['experience'][$index]['companyName'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*Job Title/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['experience'][$index]['jobTitle'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*Company Url/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['experience'][$index]['companyUrl'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*Start Date/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['experience'][$index]['startDate'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*End Date/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['experience'][$index]['endDate'] = trim($result['1']);
                    } elseif (preg_match('/^\d*\s*present/i', trim($line))) {
                        $result = explode(':', trim($line));
                        $cvDataArray ['experience'][$index]['present'] = 1;
                    }
                }

                //check if the line is the separator to increment the array index
                if (trim($line) == '-------------------------------------------------------') {
                    $index++;
                }
            } elseif (preg_match('/^\d*\s*experience/i', trim($line))) {
                $skillsFlag = TRUE;
            }
        }

        return $cvDataArray;
    }

    /**
     * this function used to upload users cv file
     * @author ahmed
     */
    public function uploadCvAction() {
        //check for logrdin user
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');
        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();
        $session = $this->getRequest()->getSession();

        //create new cv
        $newCV = new \Objects\InternJumpBundle\Entity\CV();
        $newCV->setUser($user);

        // creating the upload form
        $formValidationGroups = array('cvFile');
        $form = $this->createFormBuilder($newCV, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('file', 'file')
                ->getForm();
        // checking the validation of the form
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                //set cv name
                $nowDate = new \DateTime();
                $newCV->setName('auto ' . $nowDate->format('Y-m-d h:i:s A'));

                $newCV->setObjective('');

                $em->persist($newCV);
                $em->flush();
                $cvDataArray = $this->getUplodedCvData($newCV->getAbsolutePath());
                if (sizeof($cvDataArray) > 0) {
                    //add cv objective
                    if (isset($cvDataArray['objective'])) {
                        $newCV->setObjective($cvDataArray['objective']);
                    }
                    //create skills
                    if (isset($cvDataArray['skills'])) {
                        $skills = explode(',', $cvDataArray['skills']);
                        //check if this skill not exist
                        foreach ($skills as $skill) {
                            $skillObject = $skillRepo->findOneBy(array('title' => $skill));
                            if (!$skillObject) {
                                //create new skill
                                $newSkill = new Skill();
                                $newSkill->setTitle($skill);
                                $newSkill->setUsersCount(1);
                                $em->persist($newSkill);
                                //add the skill to the user
                                $newCV->addSkill($newSkill);

                                $user->addSkill($newSkill);
                            } else {
                                //check if the user have this skill
                                if (!$newCV->getSkills()->contains($skillObject)) {
                                    //add the skill to the user
                                    $newCV->addSkill($skillObject);
                                }
                                //check if the user have this skill
                                if (!$user->getSkills()->contains($skillObject)) {
                                    //add the skill to the user
                                    $user->addSkill($skillObject);
                                    //increment skill user count
                                    $skillObject->setUsersCount($skillObject->getUsersCount() + 1);
                                }
                            }
                        }
                    }
                    //creat educations
                    if (isset($cvDataArray['educations'])) {
                        foreach ($cvDataArray['educations'] as $newEducation) {
                            $newEducationObject = new Education();
                            $newEducationObject->setUser($user);

                            if (isset($newEducation['shoolName'])) {
                                $newEducationObject->setSchoolName($newEducation['shoolName']);
                            } else {
                                $newEducationObject->setSchoolName('');
                            }
                            if (isset($newEducation['major']))
                                $newEducationObject->setMajor($newEducation['major']);
                            if (isset($newEducation['minor']))
                                $newEducationObject->setMinor($newEducation['minor']);
                            if (isset($newEducation['majorGPA']))
                                $newEducationObject->setMajorGPA($newEducation['majorGPA']);
                            if (isset($newEducation['startDate']))
                                $newEducationObject->setStartDate($newEducation['startDate']);
                            if (isset($newEducation['endDate']))
                                $newEducationObject->setEndDate($newEducation['endDate']);

                            $em->persist($newEducationObject);
                        }
                    }

                    //creat employment history
                    if (isset($cvDataArray['experience'])) {
                        foreach ($cvDataArray['experience'] as $experience) {
                            $newEmploymentHistory = new EmploymentHistory();
                            $newEmploymentHistory->setUser($user);
                            if (isset($experience['companyName'])) {
                                $newEmploymentHistory->setCompanyName($experience['companyName']);
                            } else {
                                $newEmploymentHistory->setCompanyName('');
                            }
                            if (isset($experience['jobTitle'])) {
                                $newEmploymentHistory->setTitle($experience['jobTitle']);
                            } else {
                                $newEmploymentHistory->setTitle('');
                            }
                            if (isset($experience['companyUrl']))
                                $newEmploymentHistory->setCompanyUrl($experience['companyUrl']);
                            if (isset($experience['startDate'])) {
                                $newEmploymentHistory->setStartedFrom(new \DateTime($experience['startDate']));
                            } else {
                                $newEmploymentHistory->setStartedFrom(new \DateTime());
                            }
                            if (isset($experience['endDate']))
                                $newEmploymentHistory->setEndedIn(new \DateTime($experience['endDate']));
                            if (isset($experience['present']))
                                $newEmploymentHistory->setIsCurrent(TRUE);

                            $em->persist($newEmploymentHistory);
                            $newCV->addEmploymentHistory($newEmploymentHistory);
                        }
                    }

                    $em->flush();

                    $session->setFlash('success', 'Uploaded successfully');
                    return $this->redirect($this->generateUrl('user_portal_home', array(
                                        'loginName' => $user->getLoginName(),
                                        'cvId' => $newCV->getid()
                    )));
                }
            }
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:uploadCv.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    /**
     * this function used to upload users cv file
     * @author ahmed
     */
    public function fb_uploadCvAction() {
        //check for logrdin user
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');
        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();
        $session = $this->getRequest()->getSession();

        //create new cv
        $newCV = new \Objects\InternJumpBundle\Entity\CV();
        $newCV->setUser($user);

        // creating the upload form
        $formValidationGroups = array('cvFile');
        $form = $this->createFormBuilder($newCV, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('file', 'file')
                ->getForm();
        // checking the validation of the form
        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                //set cv name
                $nowDate = new \DateTime();
                $newCV->setName('auto ' . $nowDate->format('Y-m-d h:i:s A'));

                $newCV->setObjective('');

                $em->persist($newCV);
                $em->flush();
                $cvDataArray = $this->getUplodedCvData($newCV->getAbsolutePath());
                if (sizeof($cvDataArray) > 0) {
                    //add cv objective
                    if (isset($cvDataArray['objective'])) {
                        $newCV->setObjective($cvDataArray['objective']);
                    }
                    //create skills
                    if (isset($cvDataArray['skills'])) {
                        $skills = explode(',', $cvDataArray['skills']);
                        //check if this skill not exist
                        foreach ($skills as $skill) {
                            $skillObject = $skillRepo->findOneBy(array('title' => $skill));
                            if (!$skillObject) {
                                //create new skill
                                $newSkill = new Skill();
                                $newSkill->setTitle($skill);
                                $newSkill->setUsersCount(1);
                                $em->persist($newSkill);
                                //add the skill to the user
                                $newCV->addSkill($newSkill);

                                $user->addSkill($newSkill);
                            } else {
                                //check if the user have this skill
                                if (!$newCV->getSkills()->contains($skillObject)) {
                                    //add the skill to the user
                                    $newCV->addSkill($skillObject);
                                }
                                //check if the user have this skill
                                if (!$user->getSkills()->contains($skillObject)) {
                                    //add the skill to the user
                                    $user->addSkill($skillObject);
                                    //increment skill user count
                                    $skillObject->setUsersCount($skillObject->getUsersCount() + 1);
                                }
                            }
                        }
                    }
                    //creat educations
                    if (isset($cvDataArray['educations'])) {
                        foreach ($cvDataArray['educations'] as $newEducation) {
                            $newEducationObject = new Education();
                            $newEducationObject->setUser($user);

                            if (isset($newEducation['shoolName'])) {
                                $newEducationObject->setSchoolName($newEducation['shoolName']);
                            } else {
                                $newEducationObject->setSchoolName('');
                            }
                            if (isset($newEducation['major']))
                                $newEducationObject->setMajor($newEducation['major']);
                            if (isset($newEducation['minor']))
                                $newEducationObject->setMinor($newEducation['minor']);
                            if (isset($newEducation['majorGPA']))
                                $newEducationObject->setMajorGPA($newEducation['majorGPA']);
                            if (isset($newEducation['startDate']))
                                $newEducationObject->setStartDate($newEducation['startDate']);
                            if (isset($newEducation['endDate']))
                                $newEducationObject->setEndDate($newEducation['endDate']);

                            $em->persist($newEducationObject);
                        }
                    }

                    //creat employment history
                    if (isset($cvDataArray['experience'])) {
                        foreach ($cvDataArray['experience'] as $experience) {
                            $newEmploymentHistory = new EmploymentHistory();
                            $newEmploymentHistory->setUser($user);
                            if (isset($experience['companyName'])) {
                                $newEmploymentHistory->setCompanyName($experience['companyName']);
                            } else {
                                $newEmploymentHistory->setCompanyName('');
                            }
                            if (isset($experience['jobTitle'])) {
                                $newEmploymentHistory->setTitle($experience['jobTitle']);
                            } else {
                                $newEmploymentHistory->setTitle('');
                            }
                            if (isset($experience['companyUrl']))
                                $newEmploymentHistory->setCompanyUrl($experience['companyUrl']);
                            if (isset($experience['startDate'])) {
                                $newEmploymentHistory->setStartedFrom(new \DateTime($experience['startDate']));
                            } else {
                                $newEmploymentHistory->setStartedFrom(new \DateTime());
                            }
                            if (isset($experience['endDate']))
                                $newEmploymentHistory->setEndedIn(new \DateTime($experience['endDate']));
                            if (isset($experience['present']))
                                $newEmploymentHistory->setIsCurrent(TRUE);

                            $em->persist($newEmploymentHistory);
                            $newCV->addEmploymentHistory($newEmploymentHistory);
                        }
                    }

                    $em->flush();

                    $session->setFlash('success', 'Uploaded successfully');
                    return $this->redirect($this->generateUrl('fb_user_portal_home', array(
                                        'loginName' => $user->getLoginName(),
                                        'cvId' => $newCV->getid()
                    )));
                }
            }
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_uploadCv.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    /**
     * this function used to add/edit personal question for the logedin user
     * @author Ahmed
     * @param int $questionId
     * @param string $answerText
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addEditPersonalQuestionAnswerAction($questionId, $answerText) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $personalQuestionAnswerRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestionAnswer');
        $PersonalQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestion');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //get the question
        $question = $PersonalQuestionRepo->find($questionId);

        //check if this user answer this question before
        $answerFlag = $personalQuestionAnswerRepo->findOneBy(array('user' => $user->getId(), 'question' => $questionId));
        if ($answerFlag) {
            $answerFlag->setAnswer($answerText);
        } else {
            //create new answer
            $newAnswer = new \Objects\InternJumpBundle\Entity\PersonalQuestionAnswer();
            $newAnswer->setAnswer($answerText);
            $newAnswer->setUser($user);
            $newAnswer->setQuestion($question);

            $em->persist($newAnswer);
        }

        $em->flush();

        return new Response('done');
    }

    /**
     * this action used for answer internjump personal quiz
     * @author Ahmed
     * @return type
     */
    public function personalQuestionsAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $PersonalQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestion');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //get all personal questiond
        $questions = $PersonalQuestionRepo->getUserPersonalQuestions($user->getId());


        return $this->render('ObjectsInternJumpBundle:InternjumpUser:personalQuestions.html.twig', array(
                    'questions' => $questions
        ));
    }

    /**
     * this action used for answer internjump personal quiz
     * @author Ahmed
     * @return type
     */
    public function fb_personalQuestionsAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $PersonalQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestion');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //get all personal questiond
        $questions = $PersonalQuestionRepo->getUserPersonalQuestions($user->getId());


        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_personalQuestions.html.twig', array(
                    'questions' => $questions
        ));
    }

    /**
     * this function will update the user internjump quiz score
     * @author Ahmed
     * @param int $score
     */
    public function updateUserQuizScoreAction($score) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$this->getRequest()->isXmlHttpRequest()) {
            return new Response('faild');
        }
        $em = $this->getDoctrine()->getEntityManager();
        $quizRepo = $em->getRepository('ObjectsInternJumpBundle:Quiz');
        $quizResultRepo = $em->getRepository('ObjectsInternJumpBundle:QuizResult');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //check if the user have score
        $user->setScore($score);
        $em->flush();


        //find all quiz results
        $quizResults = $quizResultRepo->findAll();
        $resultObject = null;
        foreach ($quizResults as $result) {
            if ($result->getScore() >= $score) {
                $resultObject = $result;
                break;
            }
            $resultObject = $result;
        }

        //check if user have social acounts
        $userSocialAccounts = $user->getSocialAccounts();
        $container = $this->container;
        if ($userSocialAccounts) {
            $status = 'I passed internJump personality quiz and my result is ' . $resultObject->getMessage();
            $picture = $this->generateUrl('site_homepage', array(), TRUE) . $resultObject->getTimThumbUrl(95, 95);
            $link = $this->generateUrl('site_fb_homepage', array(), TRUE);

            // check if have facebook
            if ($userSocialAccounts->isFacebookLinked()) {
                FacebookController::postOnUserWallAndFeedAction($userSocialAccounts->getFacebookId(), $userSocialAccounts->getAccessToken(), $status, null, null, $link, $picture);
            }

            // check if have twitter
            if ($userSocialAccounts->isTwitterLinked()) {
                TwitterController::twitt($status . ' ' . $link, $container->getParameter('consumer_key'), $container->getParameter('consumer_secret'), $userSocialAccounts->getOauthToken(), $userSocialAccounts->getOauthTokenSecret());
            }

            // check if have linkedin
            if ($userSocialAccounts->isLinkedInLinked()) {
                LinkedinController::linkedInShare($container->getParameter('linkedin_api_key'), $container->getParameter('linkedin_secret_key'), $userSocialAccounts->getLinkedinOauthToken(), $userSocialAccounts->getLinkedinOauthTokenSecret(), $status, $status, $status, $link, $picture);
            }
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:QuizResult.html.twig', array(
                    'resultObject' => $resultObject
        ));
    }

    /**
     * this function used to show quiz result
     * @author ahmed
     */
    public function internjumbQuizPageResultAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $quizResultRepo = $em->getRepository('ObjectsInternJumpBundle:QuizResult');
        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //check if user pass the quiz before
        $resultObject = null;
        if ($user->getScore()) {
            //find all quiz results
            $quizResults = $quizResultRepo->findAll();
            foreach ($quizResults as $result) {
                if ($result->getScore() >= $user->getScore()) {
                    $resultObject = $result;
                    break;
                }
                $resultObject = $result;
            }
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:internjumbQuizPageResult.html.twig', array(
                    'user' => $user, 'resultObject' => $resultObject
        ));
    }

    /**
     * this action for the logedin users to answer know you quiz link
     * @author Ahmed
     */
    public function internjumbQuizPageAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $quizRepo = $em->getRepository('ObjectsInternJumpBundle:Quiz');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        $quiz = $quizRepo->findAll();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:internjumbQuizPage.html.twig', array(
                    'quiz' => $quiz, 'user' => $user
        ));
    }

    /**
     * this function used to show quiz result
     * @author ahmed
     */
    public function fb_internjumbQuizPageResultAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $quizResultRepo = $em->getRepository('ObjectsInternJumpBundle:QuizResult');
        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        //check if user pass the quiz before
        $resultObject = null;
        if ($user->getScore()) {
            //find all quiz results
            $quizResults = $quizResultRepo->findAll();
            foreach ($quizResults as $result) {
                if ($result->getScore() >= $user->getScore()) {
                    $resultObject = $result;
                    break;
                }
                $resultObject = $result;
            }
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_internjumbQuizPageResult.html.twig', array(
                    'user' => $user, 'resultObject' => $resultObject
        ));
    }

    /**
     * this action for the logedin users to answer know you quiz link
     * @author Ahmed
     */
    public function fb_internjumbQuizPageAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $quizRepo = $em->getRepository('ObjectsInternJumpBundle:Quiz');

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();

        $quiz = $quizRepo->findAll();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_internjumbQuizPage.html.twig', array(
                    'quiz' => $quiz, 'user' => $user
        ));
    }

    /**
     * this function will show user notifications
     * @author Ahmed
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getUserNotificationsAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            return new Response('faild');
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');
        $companyMessageRepo = $em->getRepository('ObjectsInternJumpBundle:Message');

        //count all user notifications
        $allUserNotificationsCount = $userNotificationRepo->countAllUserNotifications($user->getId());
        //get the user new notifications by type
        $userNotificationsCountByType = $userNotificationRepo->countUserNotifications($user->getId());
        //count new company message
        $newMessagesCount = $companyMessageRepo->getUserNewMessagesCount($user->getId());

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userNotifications.html.twig', array(
                    'allUserNotificationsCount' => $allUserNotificationsCount,
                    'user' => $user,
                    'userNotificationsCountByType' => $userNotificationsCountByType,
                    'newMessagesCount' => $newMessagesCount
        ));
    }

    /**
     * this function will show user notifications
     * @author Ahmed
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fb_getUserNotificationsAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            return new Response('faild');
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');
        $companyMessageRepo = $em->getRepository('ObjectsInternJumpBundle:Message');

        //count all user notifications
        $allUserNotificationsCount = $userNotificationRepo->countAllUserNotifications($user->getId());
        //get the user new notifications by type
        $userNotificationsCountByType = $userNotificationRepo->countUserNotifications($user->getId());
        //count new company message
        $newMessagesCount = $companyMessageRepo->getUserNewMessagesCount($user->getId());

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_userNotifications.html.twig', array(
                    'allUserNotificationsCount' => $allUserNotificationsCount,
                    'user' => $user,
                    'userNotificationsCountByType' => $userNotificationsCountByType,
                    'newMessagesCount' => $newMessagesCount
        ));
    }

    /**
     * this function will used to accept/reject company hire from the owner logedin user
     * @author Ahmed
     * @param int $userInternshipId
     * @param string $status
     */
    public function userAcceptRejectHireAction($userInternshipId, $status) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');

        //get the userInternship object
        $userInternship = $userInternshipRepo->find($userInternshipId);

        if ($userInternship->getUser()->getId() == $user->getId() && $userInternship->getStatus() == 'pending') {
            $userInternship->setStatus($status);
            $userInternship->setCreatedAt(new \DateTime());

            //add company notification
            $companyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $companyNotification->setCompany($userInternship->getInternship()->getCompany());
            $companyNotification->setUser($user);
            if ($status == 'rejected') {
                $companyNotification->setType('user_reject_job');
                InternjumpController::companyNotificationMail($this->container, $user, $userInternship->getInternship()->getCompany(), 'user_reject_job', $userInternship->getInternship()->getId());
            } else {
                $companyNotification->setType('user_accept_job');

                //check if user have social acounts
                $userSocialAccounts = $user->getSocialAccounts();
                $container = $this->container;
                if ($userSocialAccounts) {
                    $status = 'I accept internjump hire request';
                    $link = $this->generateUrl('site_fb_homepage', array(), TRUE);

                    // check if have facebook
                    if ($userSocialAccounts->isFacebookLinked()) {
                        FacebookController::postOnUserWallAndFeedAction($userSocialAccounts->getFacebookId(), $userSocialAccounts->getAccessToken(), $status, null, null, $link, null);
                    }

                    // check if have twitter
                    if ($userSocialAccounts->isTwitterLinked()) {
                        TwitterController::twitt($status . ' ' . $link, $container->getParameter('consumer_key'), $container->getParameter('consumer_secret'), $userSocialAccounts->getOauthToken(), $userSocialAccounts->getOauthTokenSecret());
                    }

                    // check if have linkedin
                    if ($userSocialAccounts->isLinkedInLinked()) {
                        LinkedinController::linkedInShare($container->getParameter('linkedin_api_key'), $container->getParameter('linkedin_secret_key'), $userSocialAccounts->getLinkedinOauthToken(), $userSocialAccounts->getLinkedinOauthTokenSecret(), $status, $status, $status, $link, null);
                    }
                }

                InternjumpController::companyNotificationMail($this->container, $user, $userInternship->getInternship()->getCompany(), 'user_accept_job', $userInternship->getInternship()->getId());
            }

            $companyNotification->setTypeId($userInternship->getInternship()->getId());
            $em->persist($companyNotification);

            $em->flush();
        }

        return new Response('done');
    }

    /**
     * this function will show an job hire request for the logedin owner user
     * @author Ahmed
     * @param int $userInternshipId
     */
    public function userHireAction($userInternshipId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');

        //get the userInternship object
        $userInternship = $userInternshipRepo->find($userInternshipId);
        if (!$userInternship || $userInternship->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('request_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }


        //get the company
        $company = $userInternship->getInternship()->getCompany();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userHire.html.twig', array(
                    'user' => $user,
                    'userInternship' => $userInternship,
                    'company' => $company
        ));
    }

    /**
     * this function will show an job hire request for the logedin owner user
     * @author Ahmed
     * @param int $userInternshipId
     */
    public function fb_userHireAction($userInternshipId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');

        //get the userInternship object
        $userInternship = $userInternshipRepo->find($userInternshipId);
        if (!$userInternship || $userInternship->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('request_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }


        //get the company
        $company = $userInternship->getInternship()->getCompany();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_userHire.html.twig', array(
                    'user' => $user,
                    'userInternship' => $userInternship,
                    'company' => $company
        ));
    }

    /**
     * this function will used to accept/reject company interview from the owner logedin user
     * @author Ahmed
     * @param int $interviewId
     * @param string $status
     */
    public function userAcceptRejectInterviewAction($interviewId, $status) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        //get the interview object
        $interview = $interviewRepo->find($interviewId);

        if ($interview->getUser()->getId() == $user->getId() && $interview->getAccepted() == 'pending') {
            $interview->setAccepted($status);


            //add company notification
            $companyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $companyNotification->setCompany($interview->getCompany());
            $companyNotification->setUser($user);
            if ($status == 'rejected') {
                $companyNotification->setType('user_reject_interview');
                InternjumpController::companyNotificationMail($this->container, $user, $interview->getCompany(), 'user_reject_interview', $interviewId);
            } else {
                $companyNotification->setType('user_accept_interview');
                InternjumpController::companyNotificationMail($this->container, $user, $interview->getCompany(), 'user_accept_interview', $interviewId);
            }

            $companyNotification->setTypeId($interviewId);
            $em->persist($companyNotification);

            $em->flush();
        }

        return new Response('done');
    }

    /**
     * this function will show an interview for the logedin owner user
     * @author Ahmed
     * @param int $interviewId
     */
    public function userInterviewAction($interviewId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        //get the interview object
        $interview = $interviewRepo->find($interviewId);
        if (!$interview || $interview->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('interview_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get the company
        $company = $interview->getCompany();

        //check for the valid to date
        $validToFalg = 1;
        if ($interview->getInterviewDate() < new \DateTime('today')) {
            $validToFalg = 0;
        }


        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userInterview.html.twig', array(
                    'user' => $user,
                    'interview' => $interview,
                    'validToFalg' => $validToFalg,
                    'company' => $company
        ));
    }

    /**
     * this function will show an interview for the logedin owner user
     * @author Ahmed
     * @param int $interviewId
     */
    public function fb_userInterviewAction($interviewId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        //get the interview object
        $interview = $interviewRepo->find($interviewId);
        if (!$interview || $interview->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('interview_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }

        //get the company
        $company = $interview->getCompany();

        //check for the valid to date
        $validToFalg = 1;
        if ($interview->getInterviewDate() < new \DateTime('today')) {
            $validToFalg = 0;
        }


        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_userInterview.html.twig', array(
                    'user' => $user,
                    'interview' => $interview,
                    'validToFalg' => $validToFalg,
                    'company' => $company
        ));
    }

    /**
     * this function will used to accept/reject company interest from the owner logedin user
     * @author Ahmed
     * @param int $interestId
     * @param string $status
     */
    public function userAcceptRejectinterestAction($interestId, $status) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');

        //get the interest object
        $interest = $interestRepo->find($interestId);

        if ($interest->getUser()->getId() == $user->getId() && $interest->getAccepted() == 'pending') {
            $interest->setAccepted($status);
            $interest->setRespondedAt(new \DateTime());


            //add company notification
            $companyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $companyNotification->setCompany($interest->getCompany());
            $companyNotification->setUser($user);
            if ($status == 'rejected') {
                $companyNotification->setType('user_reject_interest');
                InternjumpController::companyNotificationMail($this->container, $user, $interest->getCompany(), 'user_reject_interest', $interest->getCvId());
            } else {
                $companyNotification->setType('user_accept_interest');
                InternjumpController::companyNotificationMail($this->container, $user, $interest->getCompany(), 'user_accept_interest', $interest->getCvId());
            }

            $companyNotification->setTypeId($interest->getCvId());
            $em->persist($companyNotification);

            $em->flush();
        }

        return new Response('done');
    }

    /**
     * this function will show company interest to the owner user
     * @author Ahmed
     * @param int $interestId
     */
    public function userInterestAction($interestId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');

        //get the interest object
        $interest = $interestRepo->find($interestId);
        if (!$interest || $interest->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('interest_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get the interest cv
        $cv = $cvRepo->find($interest->getCvId());

        //get the interest company
        $company = $interest->getCompany();

        //check for the valid to date
        $validToFalg = 1;
        if ($interest->getValidTo() < new \DateTime('today')) {
            $validToFalg = 0;
        }


        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userInterest.html.twig', array(
                    'user' => $user,
                    'interest' => $interest,
                    'validToFalg' => $validToFalg,
                    'cv' => $cv,
                    'company' => $company
        ));
    }

    /**
     * this function will show company interest to the owner user
     * @author Ahmed
     * @param int $interestId
     */
    public function fb_userInterestAction($interestId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');

        //get the interest object
        $interest = $interestRepo->find($interestId);
        if (!$interest || $interest->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('interest_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }

        //get the interest cv
        $cv = $cvRepo->find($interest->getCvId());

        //get the interest company
        $company = $interest->getCompany();

        //check for the valid to date
        $validToFalg = 1;
        if ($interest->getValidTo() < new \DateTime('today')) {
            $validToFalg = 0;
        }


        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_userInterest.html.twig', array(
                    'user' => $user,
                    'interest' => $interest,
                    'validToFalg' => $validToFalg,
                    'cv' => $cv,
                    'company' => $company
        ));
    }

    /**
     * this function will mark all notifications as read for the logedin user
     * @author Ahmed
     */
    public function userMarkAllNotificationsAsReadAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin company objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');

        //get all unread notifications
        $unReadNotifications = $userNotificationRepo->findBy(array('user' => $user->getId(), 'isNew' => TRUE));
        foreach ($unReadNotifications as $unReadNotification) {
            $unReadNotification->setIsNew(FALSE);
        }

        $em->flush();
        return new Response('done');
    }

    /**
     * this function will mark notification as read for the logedin user
     * @author Ahmed
     * @param int $notificationId
     */
    public function userNotificationMardAsReadAction($notificationId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin company objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');

        //get the notification
        $notification = $userNotificationRepo->find($notificationId);

        //check if this company is the owner
        if ($notification->getUser()->getId() == $user->getId()) {
            $notification->setIsNew(FALSE);
            $em->flush();
        }
        return new Response('done');
    }

    /**
     * this function will show all user notification
     * @author Ahmed
     */
    public function UserNotificationAction($type, $page, $itemsPerPage) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');

        //check if we do not have the items per page number
        if (!$itemsPerPage) {
            //get the items per page from cookie or the default value
            $itemsPerPage = $this->getRequest()->cookies->get('user_notifications_per_page_' . $user->getId(), 10);
        }

        //get all user notifications
        if ($type == 'all') {
            $userNotifications = $userNotificationRepo->getUserNotifications($user->getId(), null, $page, $itemsPerPage);
            $count = sizeof($userNotificationRepo->getUserNotifications($user->getId(), null, 1, null));

            //calculate the last page number
            $lastPageNumber = (int) ($count / $itemsPerPage);
            if (($count % $itemsPerPage) > 0) {
                $lastPageNumber++;
            }
        } else {
            $userNotifications = $userNotificationRepo->getUserNotifications($user->getId(), $type, $page, $itemsPerPage);
            $count = sizeof($userNotificationRepo->getUserNotifications($user->getId(), $type, 1, null));

            //calculate the last page number
            $lastPageNumber = (int) ($count / $itemsPerPage);
            if (($count % $itemsPerPage) > 0) {
                $lastPageNumber++;
            }
        }
        //check if there is unread notifications
        $unreadNotifications = $userNotificationRepo->findBy(array('user' => $user->getId(), 'isNew' => TRUE));

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userNotification.html.twig', array(
                    'user' => $user,
                    'type' => $type,
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'userNotifications' => $userNotifications,
                    'unreadNotifications' => $unreadNotifications
        ));
    }

    /**
     * this function will show all user notification
     * @author Ahmed
     */
    public function fb_UserNotificationAction($type, $page, $itemsPerPage) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        //get logedin user objects
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:UserNotification');

        //check if we do not have the items per page number
        if (!$itemsPerPage) {
            //get the items per page from cookie or the default value
            $itemsPerPage = $this->getRequest()->cookies->get('user_notifications_per_page_' . $user->getId(), 10);
        }

        //get all user notifications
        if ($type == 'all') {
            $userNotifications = $userNotificationRepo->getUserNotifications($user->getId(), null, $page, $itemsPerPage);
            $count = sizeof($userNotificationRepo->getUserNotifications($user->getId(), null, 1, null));

            //calculate the last page number
            $lastPageNumber = (int) ($count / $itemsPerPage);
            if (($count % $itemsPerPage) > 0) {
                $lastPageNumber++;
            }
        } else {
            $userNotifications = $userNotificationRepo->getUserNotifications($user->getId(), $type, $page, $itemsPerPage);
            $count = sizeof($userNotificationRepo->getUserNotifications($user->getId(), $type, 1, null));

            //calculate the last page number
            $lastPageNumber = (int) ($count / $itemsPerPage);
            if (($count % $itemsPerPage) > 0) {
                $lastPageNumber++;
            }
        }
        //check if there is unread notifications
        $unreadNotifications = $userNotificationRepo->findBy(array('user' => $user->getId(), 'isNew' => TRUE));

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_userNotification.html.twig', array(
                    'user' => $user,
                    'type' => $type,
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'userNotifications' => $userNotifications,
                    'unreadNotifications' => $unreadNotifications
        ));
    }

    /**
     * this function will add/edit company question answer for the logedin user
     * @author Ahmed
     * @param int $questionId
     */
    public function userAnswerCompanyQuestionAction($questionId, $answerText) {
        $request = $this->getRequest();
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$request->isXmlHttpRequest()) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');

        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //get the question
        $question = $companyQuestionRepo->find($questionId);

        //check if the logedin user is the owner
        if ($question->getUser()->getId() == $user->getId()) {
            $question->setAnswer($answerText);
            $em->flush();

            //add company notification
            $companyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $companyNotification->setCompany($question->getCompany());
            $companyNotification->setUser($question->getUser());
            $companyNotification->setType('user_answer_question');
            $companyNotification->setTypeId($question->getId());
            $em->persist($companyNotification);
            $em->flush();

            //send email to company
            InternjumpController::companyNotificationMail($this->container, $question->getUser(), $question->getCompany(), 'user_answer_question', $question->getId());

            return new Response('done');
        } else {
            return new Response('faild');
        }
    }

    /**
     * this function will used the question status for the loged in user
     * @author Ahmed
     * @param type $questionId
     * @param string $status
     */
    public function changeQuestionShowOnCvAction($questionId, $status) {
        $request = $this->getRequest();
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$request->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');

        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //get the question
        $question = $companyQuestionRepo->find($questionId);

        //check if the logedin user is the owner
        if ($question->getUser()->getId() == $user->getId()) {
            $question->setShowOnCV($status);
            $em->flush();
            return new Response('done');
        } else {
            return new Response('faild');
        }
    }

    /**
     * this function will used to change cv status for the loged in user
     * @author Ahmed
     * @param int $cvId
     * @param string $status
     */
    public function changeUserCvStatusAction($cvId, $status) {
        $request = $this->getRequest();
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$request->isXmlHttpRequest()) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');

        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //get user cv
        $userCv = $cvRepo->find($cvId);
        //check if the logedin user is the owner
        if ($userCv->getUser()->getId() == $user->getId()) {
            $userCv->setIsActive($status);
            $em->flush();
            return new Response('done');
        } else {
            return new Response('faild');
        }
    }

    /**
     * this function will show the user cv for the owner user
     * @author Ahmed
     * @param string $loginName
     * @param int $cvId
     */
    public function userPortalHomeAction($loginName, $cvId) {
        if (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //get the user
            $user = $this->get('security.context')->getToken()->getUser();
            if ($user->getLoginName() != $loginName) {
                return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            }
        } else {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');
        $personalQuestionAnswerRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestionAnswer');

        //get user cv
        $userCv = $cvRepo->find($cvId);

        if (!$userCv || $userCv->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('cv_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get user companies questions
        $companiesQuestions = $companyQuestionRepo->findBy(array('user' => $user->getId()), array('createdAt' => 'desc'));

        //calculate birth date
        $age = null;
        if ($user->getDateOfBirth()) {
            $date = $user->getDateOfBirth();
            $now = new \DateTime();
            $age = $now->diff($date);
            $age = $age->y;
        }


        //get user personal question answers
        $userPersonalQuestionAnswers = $personalQuestionAnswerRepo->findBy(array('user' => $user->getId()));

        $bannerRepo = $em->getRepository('ObjectsInternJumpBundle:Banner');
        //get page banner
        $pageBanners = $bannerRepo->findBy(array('position' => 'Student Pages'));
        if (sizeof($pageBanners) > 0) {
            $rand_key = array_rand($pageBanners, 1);
            $rand_banner = $pageBanners[$rand_key];
            //increment banner of views
            $rand_banner->setNumberOfViews($rand_banner->getNumberOfViews() + 1);
            $em->flush();
        } else {
            $rand_banner = NULL;
        }

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userPortalHome.html.twig', array(
                    'user' => $user,
                    'userCv' => $userCv,
                    'cvId' => $cvId,
                    'age' => $age,
                    'companiesQuestions' => $companiesQuestions,
                    'userPersonalQuestionAnswers' => $userPersonalQuestionAnswers,
                    'rand_banner' => $rand_banner
        ));
    }

    /**
     * this function will show the user cv for the owner user
     * @author Ahmed
     * @param string $loginName
     * @param int $cvId
     */
    public function fb_userPortalHomeAction($loginName, $cvId) {
        if (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //get the user
            $user = $this->get('security.context')->getToken()->getUser();
            if ($user->getLoginName() != $loginName) {
                return $this->redirect($this->generateUrl('site_fb_homepage', array(), TRUE));
            }
        } else {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');
        $personalQuestionAnswerRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestionAnswer');

        //get user cv
        $userCv = $cvRepo->find($cvId);

        if (!$userCv || $userCv->getUser()->getId() != $user->getId()) {
            $message = $this->container->getParameter('cv_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }

        //get user companies questions
        $companiesQuestions = $companyQuestionRepo->findBy(array('user' => $user->getId()), array('createdAt' => 'desc'));

        //calculate birth date
        $age = null;
        if ($user->getDateOfBirth()) {
            $date = $user->getDateOfBirth();
            $now = new \DateTime();
            $age = $now->diff($date);
            $age = $age->y;
        }


        //get user personal question answers
        $userPersonalQuestionAnswers = $personalQuestionAnswerRepo->findBy(array('user' => $user->getId()));

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_userPortalHome.html.twig', array(
                    'user' => $user,
                    'userCv' => $userCv,
                    'cvId' => $cvId,
                    'age' => $age,
                    'companiesQuestions' => $companiesQuestions,
                    'userPersonalQuestionAnswers' => $userPersonalQuestionAnswers
        ));
    }

    /**
     * this function will show user which apply for a job data for a company
     * @author Ahmed
     * @param string $userLoginName
     */
    public function companySeeUserDataAction($userLoginName, $cvId) {
        //check for loggedin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }


        $em = $this->getDoctrine()->getEntityManager();
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');
        $reportedUserRepo = $em->getRepository('ObjectsInternJumpBundle:ReportedUser');

        $request = $this->getRequest();


        //get user bject
        $userObject = $userRepo->findOneBy(array('loginName' => $userLoginName));

        if (!$userObject) {
            $message = $this->container->getParameter('user_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get user cv
        $userCv = $cvRepo->find($cvId);

        //get loggedin company
        $company = $this->get('security.context')->getToken()->getUser();
        //check if the user apply to job with this cv
        $userInternship = $userInternshipRepo->checkUserCompanyJobApply($userObject->getId(), $cvId, $company->getId());

        if (!$userCv || $userCv->getUser()->getId() != $userObject->getId() || ($userCv->getIsActive() == FALSE && !$userInternship)) {
            $message = $this->container->getParameter('cv_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //increment cv views number
        $userCv->setViewsCount($userCv->getViewsCount() + 1);
        $em->flush();

        //get user companies questions
        $companiesQuestions = $companyQuestionRepo->findBy(array('user' => $userObject->getId(), 'showOnCV' => TRUE), array('createdAt' => 'desc'));

        //check if this user accept company interest
        $companyUserInterestFlag = 0;
        $companyUserFavoriteFlag = FALSE;
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //get logedin company objects
            $company = $this->get('security.context')->getToken()->getUser();
            $interestObject = $interestRepo->findOneBy(array('company' => $company->getId(), 'user' => $userObject->getId()));
            if ($interestObject) {
                if ($interestObject->getAccepted() == 'accepted') {
                    //user accept interest
                    $companyUserInterestFlag = 1;
                    //check if company favorite this user
                    if ($company->getFavoriteUsers()->contains($userObject)) {
                        $companyUserFavoriteFlag = TRUE;
                    }
                } elseif ($interestObject->getAccepted() == 'rejected') {
                    $companyUserInterestFlag = 2;
                } elseif ($interestObject->getAccepted() == 'pending') {
                    //user didn't accept the company interest
                    if ($interestObject->getValidTo() > new \DateTime()) {
                        $companyUserInterestFlag = 3;
                    } else {
                        //the company interest invalide
                        $companyUserInterestFlag = 4;
                    }
                }
            }
        }

        //check if there is error message
        $errorMessage = $request->get('errorMessage');

        //calculate birth date
        $age = null;
        if ($userObject->getDateOfBirth()) {
            $date = $userObject->getDateOfBirth();
            $now = new \DateTime();
            $age = $now->diff($date);
            $age = $age->y;
        }

        //check ig company report this user before
        $reportUser = $reportedUserRepo->findOneBy(array('user' => $userObject->getId(), 'cv' => $cvId, 'company' => $company->getId()));

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:companySeeUserData.html.twig', array(
                    'user' => $userObject,
                    'reportUser' => $reportUser,
                    'userCv' => $userCv,
                    'cvId' => $cvId,
                    'age' => $age,
                    'errorMessage' => $errorMessage,
                    'companyUserInterestFlag' => $companyUserInterestFlag,
                    'companiesQuestions' => $companiesQuestions,
                    'companyUserFavoriteFlag' => $companyUserFavoriteFlag,
                    'user_interset_waiting_message' => $this->container->getParameter('company_user_interset_waiting_message_user_data_page'),
                    'company_user_interset_reject_message' => $this->container->getParameter('company_user_interset_reject_message_user_data_page'),
                    'company_user_hire_pending_message' => $this->container->getParameter('company_user_hire_pending_message_user_data_page'),
                    'company_user_hire_accept_message' => $this->container->getParameter('company_user_hire_accept_message_user_data_page'),
                    'company_user_hire_reject_message' => $this->container->getParameter('company_user_hire_reject_message_user_data_page'),
                    'company_user_interview_pending_message' => $this->container->getParameter('company_user_interview_pending_message_user_data_page'),
                    'company_user_interview_accept_message' => $this->container->getParameter('company_user_interview_accept_message_user_data_page'),
                    'company_user_interview_reject_message' => $this->container->getParameter('company_user_interview_reject_message_user_data_page')
        ));
    }

    /**
     * This function for displaying user search page and fill drop down lists with data
     * @author  Ola
     */
    public function StudentInternshipsSearchAction() {
        //check for loggedin user
//        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
//            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
//            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
//            return $this->redirect($this->generateUrl('login'));
//        }

        $em = $this->getDoctrine()->getEntityManager();

        //Check data in session In case User pressed back button
        //get request
        $request = $this->getRequest();
        $session = $request->getSession();
        $sessionData = $session->get('searchQuery');

        //Check to fill the form with parameters if been set in the session Used when users [back] to page
        $sessionCheck = false;

        //Inspect if the sessionData been set
        if (isset($sessionData)) {
            $sessionCheck = false;
        }

        //Check to fill the form with parameters if been sent with the request
        $check = false;

        /*         * ********************************************************** */
        /*         * ********Start Partial Search part*********** */


        //Get request's parameters
        $jobType = $request->get("jobType");
        $city = $request->get("city");
        $state = $request->get("state");
        $category = $request->get("industry");
        $keyword = $request->get("keyword");
        $company = $request->get("company");

        //Get city Repo
        $cityRepo = $em->getRepository('ObjectsInternJumpBundle:City');

        if ($jobType || $category || $city || $state || $keyword || $company) {
            //set the check to be true
            $check = true;

            //now make sure which parameters been sent and which not
            if (!$jobType) {
                $jobType = "empty";
            }
            if (!$city) {
                $city = "empty";
            }
            if (!$state) {
                $state = "empty";
            }
            if (!$category) {
                $category = "empty";
            }
            if (!$keyword) {
                $keyword = "empty";
            }
            if (!$company) {
                $company = "empty";
            }

            //get number of jobs per page
            $jobsPerPage = $this->container->getParameter('jobs_per_search_results_page');

            $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
            //get jobs search results array
            $userSearchResults = $internshipRepo->getJobsSearchResult($keyword, "empty", $city, $state, $category, $company, "empty", "empty", $jobType, 1, $jobsPerPage);

            //Limit the details to only 200 character
            foreach ($userSearchResults as &$job) {
                $jobDesc = strip_tags($job->getDescription());
                if (strlen($jobDesc) > 200) {
                    $job->setDescription(substr($jobDesc, 0, 200) . '...');
                } else {
                    $job->setDescription($jobDesc);
                }
            }
            /* pagenation part */
            //get count of all search result jobs
            $userSearchResultsCount = sizeof($internshipRepo->getJobsSearchResult($keyword, "empty", $city, $state, $category, $company, "empty", "empty", $jobType, 1, null));

            $lastPageNumber = (int) ($userSearchResultsCount / $jobsPerPage);
            if (($userSearchResultsCount % $jobsPerPage) > 0) {
                $lastPageNumber++;
            }
        } else {
            $userSearchResults = null;
            $jobsPerPage = null;
            $lastPageNumber = null;
            $city = null;
            $state = null;
            $category = null;
            $company = null;
            $jobType = null;
            $keyword = null;
        }
        /*         * ********End Partial Search part*********** */
        /*         * ********************************************************** */




        //Get country Repo
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray [$value['id']] = $value['name'];
        }

        //All categories
        $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');
        //get countries array
        $allCategories = $categoryRepo->getAllCategories();
        $allCategoriesArray = array();
        foreach ($allCategories as $value) {
            $allCategoriesArray [$value['id']] = $value['name'];
        }

        //All companys
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        //get countries array
        $allCompanys = $companyRepo->getAllCompanys();
        $allCompanysArray = array();
        foreach ($allCompanys as $value) {
            $allCompanysArray [$value['id']] = $value['name'];
        }

        //All Languages
        $allLanguagesArray = array('class' => 'ObjectsInternJumpBundle:Language', 'property' => 'name', 'empty_value' => '--- choose Language ---'); //, 'expanded' => true, 'multiple' => true, 'required' => false);
        //get the request object
        $request = $this->getRequest();


        $countryOptionsArr = array('choices' => $allCountriesArray, 'preferred_choices' => array('US'));
        $cityOptionsArr = array();
        $stateOptionsArr = array('empty_value' => '--- choose State ---');
        $categoryOptionsArr = array('empty_value' => '--- choose Industry ---', 'choices' => $allCategoriesArray);
        //$companyOptionsArr = array('empty_value' => '--- choose Company ---', 'choices' => $allCompanysArray);
        $companyOptionsArr = array('attr' => array( 'placeholder' => 'Type Company') );
        $jobTypeOptionsArr = array('choices' => array('Internship' => 'Internship', 'Entry Level' => 'Entry Level'), 'expanded'=> true ,'label' => 'Job type :' ); //, 'data' => 'Internship', 'empty_value' => '--- choose job type ---'
        //->add('spokenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Spoken :', 'attr' => array('class' => 'lngopt')))

        /*         * *************************************************************************** */
        //inspect if check been set to be true then set the defaults values of the form
        if ($sessionCheck) {
            if ($sessionData['title'] != "empty") {

            }
            if ($sessionData['country'] != "empty" && $sessionData['country'] != '') {
                $countryOptionsArr = array('choices' => $allCountriesArray, 'preferred_choices' => array($sessionData['country']));
            }
            if ($sessionData['city'] != "empty" && $sessionData['city'] != '') {
                //get the city name
                $theCity = "";
                if ($cityRepo->findOneBy(array('id' => $sessionData['city']))) {
                    $theCity = $cityRepo->findOneBy(array('id' => $sessionData['city']));
                    $cityOptionsArr = array('data' => $theCity);
                }
                else
                    $cityOptionsArr = array();
            }
            if ($sessionData['state'] != "empty" && $sessionData['state'] != '') {
                $stateOptionsArr = array('empty_value' => '--- choose State ---');
            }
            if ($sessionData['category'] != "empty" && $sessionData['category'] != '') {
                $categoryOptionsArr = array('choices' => $allCategoriesArray, 'preferred_choices' => array($sessionData['category']));

            }
            if ($sessionData['company'] != "empty" && $sessionData['company'] != '' && !$request->get("company")) {
                //$companyOptionsArr = array('choices' => $allCompanysArray, 'preferred_choices' => array($sessionData['company']));
                $companyOptionsArr = array('attr' => array( 'value' => $sessionData['company']) );
            }
            if ($sessionData['lang'] != "empty" && $sessionData['lang'] != '') {
                $allLanguagesArray = array('class' => 'ObjectsInternJumpBundle:Language', 'property' => 'name', 'preferred_choices' => array($sessionData['lang']));
            }
            if ($sessionData['jobt'] != "empty" && $sessionData['jobt'] != '') {
                $jobTypeOptionsArr = array('choices' => array('Internship' => 'Internship', 'Entry Level' => 'Entry Level'), 'data' => $sessionData['jobt'] , 'expanded'=> true ,'label' => 'Job type :', 'attr' => array('class' => 'lngopt') );
            }
        }

        /*         * *************************************************************************** */
        //inspect if check been set to be true then set the defaults values of the form
        if ($check) {
            //$countryOptionsArr = array('choices' => $allCountriesArray, 'empty_value' => '--- choose Country ---');
            if ($city != "empty") {
                //get the city name
                $theCity = "";
                if ($cityRepo->findOneBy(array('id' => $city))) {
                    $theCity = $cityRepo->findOneBy(array('id' => $city));
                    $cityOptionsArr = array('data' => $theCity);
                }
                else
                    $cityOptionsArr = array();
            }
            if ($state != "empty") {
                $stateOptionsArr = array('empty_value' => '--- choose State ---');
            }
            if ($category != "empty") {
                $categoryOptionsArr = array('choices' => $allCategoriesArray, 'preferred_choices' => array($category));
            }
            if ($jobType != "empty") {
                $jobTypeOptionsArr = array('choices' => array('Internship' => 'Internship', 'Entry Level' => 'Entry Level'), 'data' => $jobType,  'expanded'=> true ,'label' => 'Job type :', 'attr' => array('class' => 'lngopt') );
            }
            if ($company != "empty") {
                $companyOptionsArr = array('attr' => array( 'value' => $company) );
//                $companyObj = $companyRepo->findOneBy(array('loginName' => $company));
//                if ($companyObj) {
//                    $companyId = $companyObj->getId();
//                    $companyOptionsArr = array('choices' => $allCompanysArray, 'preferred_choices' => array($companyId));
//
//
//                }
            }
        }


        //create a search form
        $formBuilder = $this->createFormBuilder()
                ->add('country', 'choice', $countryOptionsArr)
                //->add('city', 'choice', array('empty_value' => '--- choose city ---'))
                ->add('city', 'text', $cityOptionsArr)
                ->add('state', 'choice', $stateOptionsArr)
                ->add('category', 'choice', $categoryOptionsArr)
                ->add('company','text', $companyOptionsArr)
                //->add('company', 'choice', $companyOptionsArr)
                ->add('language', 'entity', $allLanguagesArray)
                ->add('jobtype', 'choice', $jobTypeOptionsArr)
        ;
        //create the form
        $form = $formBuilder->getForm();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userSearchPage.html.twig', array(
                    'form' => $form->createView(),
                    'jobs' => $userSearchResults,
                    'page' => 1,
                    'jobsPerPage' => $jobsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'title' => "empty",
                    'country' => "empty",
                    'company' => $company,
                    'city' => $city,
                    'state' => $state, 'category' => $category, 'lang' => "empty", 'jobtype' => $jobType, 'keyword' => $keyword,
        ));
    }

    /**
     * This function for displaying user search page and fill drop down lists with data
     * @author  Ola
     */
    public function fb_StudentInternshipsSearchAction() {
        //check for loggedin user
//        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
//            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
//            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
//            return $this->redirect($this->generateUrl('login'));
//        }


        $em = $this->getDoctrine()->getEntityManager();

        //Check data in session In case User pressed back button
        //get request
        $request = $this->getRequest();
        $session = $request->getSession();
        $sessionData = $session->get('searchQuery');

        //Check to fill the form with parameters if been set in the session Used when users [back] to page
        $sessionCheck = false;

        //Inspect if the sessionData been set
        if (isset($sessionData)) {
            $sessionCheck = true;
        }

        //Check to fill the form with parameters if been sent with the request
        $check = false;

        /*         * ********************************************************** */
        /*         * ********Start Partial Search part*********** */


        //Get request's parameters
        $jobType = $request->get("jobType");
        $city = $request->get("city");
        $state = $request->get("state");
        $category = $request->get("industry");
        $keyword = $request->get("keyword");
        $company = $request->get("company");

        //Get city Repo
        $cityRepo = $em->getRepository('ObjectsInternJumpBundle:City');

        if ($jobType || $category || $city || $state || $keyword || $company) {
            //set the check to be true
            $check = true;

            //now make sure which parameters been sent and which not
            if (!$jobType) {
                $jobType = "empty";
            }
            if (!$city) {
                $city = "empty";
            }
            if (!$state) {
                $state = "empty";
            }
            if (!$category) {
                $category = "empty";
            }
            if (!$keyword) {
                $keyword = "empty";
            }
            if (!$company) {
                $company = "empty";
            }

            //get number of jobs per page
            $jobsPerPage = $this->container->getParameter('jobs_per_search_results_page');

            $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
            //get jobs search results array
            $userSearchResults = $internshipRepo->getJobsSearchResult($keyword, "empty", $city, $state, $category, $company, "empty", "empty", $jobType, 1, $jobsPerPage);

            //Limit the details to only 200 character
            foreach ($userSearchResults as &$job) {
                $jobDesc = strip_tags($job->getDescription());
                if (strlen($jobDesc) > 200) {
                    $job->setDescription(substr($jobDesc, 0, 200) . '...');
                } else {
                    $job->setDescription($jobDesc);
                }
            }
            /* pagenation part */
            //get count of all search result jobs
            $userSearchResultsCount = sizeof($internshipRepo->getJobsSearchResult($keyword, "empty", $city, $state, $category, $company, "empty", "empty", $jobType, 1, null));

            $lastPageNumber = (int) ($userSearchResultsCount / $jobsPerPage);
            if (($userSearchResultsCount % $jobsPerPage) > 0) {
                $lastPageNumber++;
            }
        } else {
            $userSearchResults = null;
            $jobsPerPage = null;
            $lastPageNumber = null;
            $city = null;
            $state = null;
            $category = null;
            $company = null;
            $jobType = null;
            $keyword = null;
        }
        /*         * ********End Partial Search part*********** */
        /*         * ********************************************************** */




        //Get country Repo
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray [$value['id']] = $value['name'];
        }

        //All categories
        $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');
        //get countries array
        $allCategories = $categoryRepo->getAllCategories();
        $allCategoriesArray = array();
        foreach ($allCategories as $value) {
            $allCategoriesArray [$value['id']] = $value['name'];
        }

        //All companys
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        //get countries array
        $allCompanys = $companyRepo->getAllCompanys();
        $allCompanysArray = array();
        foreach ($allCompanys as $value) {
            $allCompanysArray [$value['id']] = $value['name'];
        }

        //All Languages
        $allLanguagesArray = array('class' => 'ObjectsInternJumpBundle:Language', 'property' => 'name', 'empty_value' => '--- choose Language ---'); //, 'expanded' => true, 'multiple' => true, 'required' => false);
        //get the request object
        $request = $this->getRequest();


        $countryOptionsArr = array('choices' => $allCountriesArray, 'preferred_choices' => array('US'));
        $cityOptionsArr = array();
        $stateOptionsArr = array('empty_value' => '--- choose State ---');
        $categoryOptionsArr = array('empty_value' => '--- choose Industry ---', 'choices' => $allCategoriesArray);
        //$companyOptionsArr = array('empty_value' => '--- choose Company ---', 'choices' => $allCompanysArray);
        $companyOptionsArr = array('attr' => array( 'placeholder' => 'Type Company') );
        $jobTypeOptionsArr = array('choices' => array('Internship' => 'Internship', 'Entry Level' => 'Entry Level'), 'expanded'=> true ,'label' => 'Job type :' ); //, 'data' => 'Internship', 'empty_value' => '--- choose job type ---'
        //->add('spokenFluency', 'choice', array('choices' => array('None' => 'None', 'Novice' => 'Novice', 'Intermediate' => 'Intermediate', 'Advanced' => 'Advanced'), 'expanded' => true, 'label' => 'Spoken :', 'attr' => array('class' => 'lngopt')))

        /*         * *************************************************************************** */
        //inspect if check been set to be true then set the defaults values of the form
        if ($sessionCheck) {
            if ($sessionData['title'] != "empty") {

            }
            if ($sessionData['country'] != "empty" && $sessionData['country'] != '') {
                $countryOptionsArr = array('choices' => $allCountriesArray, 'preferred_choices' => array($sessionData['country']));
            }
            if ($sessionData['city'] != "empty" && $sessionData['city'] != '') {
                //get the city name
                $theCity = "";
                if ($cityRepo->findOneBy(array('id' => $sessionData['city']))) {
                    $theCity = $cityRepo->findOneBy(array('id' => $sessionData['city']));
                    $cityOptionsArr = array('data' => $theCity);
                }
                else
                    $cityOptionsArr = array();
            }
            if ($sessionData['state'] != "empty" && $sessionData['state'] != '') {
                $stateOptionsArr = array('empty_value' => '--- choose State ---');
            }
            if ($sessionData['category'] != "empty" && $sessionData['category'] != '') {
                $categoryOptionsArr = array('choices' => $allCategoriesArray, 'preferred_choices' => array($sessionData['category']));

            }
            if ($sessionData['company'] != "empty" && $sessionData['company'] != '' && !$request->get("company")) {
                //$companyOptionsArr = array('choices' => $allCompanysArray, 'preferred_choices' => array($sessionData['company']));
                $companyOptionsArr = array('attr' => array( 'value' => $sessionData['company']) );
            }
            if ($sessionData['lang'] != "empty" && $sessionData['lang'] != '') {
                $allLanguagesArray = array('class' => 'ObjectsInternJumpBundle:Language', 'property' => 'name', 'preferred_choices' => array($sessionData['lang']));
            }
            if ($sessionData['jobt'] != "empty" && $sessionData['jobt'] != '') {
                $jobTypeOptionsArr = array('choices' => array('Internship' => 'Internship', 'Entry Level' => 'Entry Level'), 'data' => $sessionData['jobt'] , 'expanded'=> true ,'label' => 'Job type :', 'attr' => array('class' => 'lngopt') );
            }
        }

        /*         * *************************************************************************** */
        //inspect if check been set to be true then set the defaults values of the form
        if ($check) {
            //$countryOptionsArr = array('choices' => $allCountriesArray, 'empty_value' => '--- choose Country ---');
            if ($city != "empty") {
                //get the city name
                $theCity = "";
                if ($cityRepo->findOneBy(array('id' => $city))) {
                    $theCity = $cityRepo->findOneBy(array('id' => $city));
                    $cityOptionsArr = array('data' => $theCity);
                }
                else
                    $cityOptionsArr = array();
            }
            if ($state != "empty") {
                $stateOptionsArr = array('empty_value' => '--- choose State ---');
            }
            if ($category != "empty") {
                $categoryOptionsArr = array('choices' => $allCategoriesArray, 'preferred_choices' => array($category));
            }
            if ($jobType != "empty") {
                $jobTypeOptionsArr = array('choices' => array('Internship' => 'Internship', 'Entry Level' => 'Entry Level'), 'data' => $jobType,  'expanded'=> true ,'label' => 'Job type :', 'attr' => array('class' => 'lngopt') );
            }
            if ($company != "empty") {
                $companyOptionsArr = array('attr' => array( 'value' => $company) );
//                $companyObj = $companyRepo->findOneBy(array('loginName' => $company));
//                if ($companyObj) {
//                    $companyId = $companyObj->getId();
//                    $companyOptionsArr = array('choices' => $allCompanysArray, 'preferred_choices' => array($companyId));
//
//
//                }
            }
        }


        //create a search form
        $formBuilder = $this->createFormBuilder()
                ->add('country', 'choice', $countryOptionsArr)
                //->add('city', 'choice', array('empty_value' => '--- choose city ---'))
                ->add('city', 'text', $cityOptionsArr)
                ->add('state', 'choice', $stateOptionsArr)
                ->add('category', 'choice', $categoryOptionsArr)
                ->add('company','text', $companyOptionsArr)
                //->add('company', 'choice', $companyOptionsArr)
                ->add('language', 'entity', $allLanguagesArray)
                ->add('jobtype', 'choice', $jobTypeOptionsArr)
        ;
        //create the form
        $form = $formBuilder->getForm();

        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_userSearchPage.html.twig', array(
                    'form' => $form->createView(),
                    'jobs' => $userSearchResults,
                    'page' => 1,
                    'jobsPerPage' => $jobsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'title' => "empty",
                    'country' => "empty",
                    'company' => $company,
                    'city' => $city,
                    'state' => $state, 'category' => $category, 'jobtype' => $jobType, 'lang' => "empty", 'jobtype' => $jobType, 'keyword' => $keyword,
        ));
    }

    /**
     * This function for search ajax action
     * @author Ola
     */
    public function searchAction($title, $country, $city, $state, $category, $company, $lang, $jobt, $page) {
        $request = $this->getRequest();


        $session = $request->getSession();
        $searchQuery = array('title' => $title, 'country' => $country, 'city' => $city, 'state' => $state, 'category' => $category, 'company' => $company, 'lang' => $lang, 'jobt' => $jobt);
        $session->set('searchQuery', $searchQuery);

        $session->set('page', $page);
        //to check if Ajax Request
//        if (!$request->isXmlHttpRequest()) {
//            return new Response("Faild");
//        }

        /*         * **to get array of keywords from search text *** */
        $keywordsArray = explode(" ", $title);

//        print_r($keywordsArray);exit;
        //get number of jobs per page
        $jobsPerPage = $this->container->getParameter('jobs_per_search_results_page');

        $em = $this->getDoctrine()->getEntityManager();
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');

        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        $companyId = $company;
        $companyObj = $companyRepo->findOneBy(array('loginName' => $company));
                if ($companyObj) {
                    $companyId = $companyObj->getId();
                }
        //get jobs search results array
        $userSearchResults = $internshipRepo->getJobsSearchResult($title, $country, $city, $state, $category, $companyId, $lang, $keywordsArray, $jobt, $page, $jobsPerPage);

        //Limit the details to only 200 character
        foreach ($userSearchResults as $job) {
            $jobDesc = strip_tags($job->getDescription());
            if (strlen($jobDesc) > 200) {
                $job->setDescription(substr($jobDesc, 0, 200) . '...');
            } else {
                $job->setDescription($jobDesc);
            }
        }
        /* pagenation part */
        //get count of all search result jobs
        $userSearchResultsCount = sizeof($internshipRepo->getJobsSearchResult($title, $country, $city, $state, $category, $companyId, $lang, $keywordsArray, $jobt, 1, null));

        $lastPageNumber = (int) ($userSearchResultsCount / $jobsPerPage);
        if (($userSearchResultsCount % $jobsPerPage) > 0) {
            $lastPageNumber++;
        }


        /*         * ***************[ Start API Search Results ]********************** */
        /*         * *********************************************** */
        $apiJobsArr = array();
        if (sizeof($userSearchResults) < 24) {
            $title1 = $title;
            if ($title == "empty") {
                if ($category != "empty") {
                    $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');
                    //get countries array
                    $categoryObj = $categoryRepo->findOneBy(array('id' => $category));
                    $title1 = $categoryObj->getSlug();

                }
            }
            if ($city != "empty" && $state != "empty") {
                $jobLocation = $city . ', ' . $state;
            } else {
                if ($city != "empty") {
                    $jobLocation = $city;
                } elseif ($state != "empty") {
                    $jobLocation = $state;
                } else {
                    $jobLocation = null;
                }
            }

            //get company name if Exist
            $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
            $companyObj = $companyRepo->findOneBy(array('loginName' => $company));
            if ($companyObj) {
                $companyName = $companyObj->getName();
            } else {
                $companyName = $company;
            }
            $apiJobsArr = $this->searchForIndeedJobs($searchString = $title1, $start = $page * $jobsPerPage, $limit = $jobsPerPage, $jobLocation, $jobt, $companyName);

            foreach ($apiJobsArr['results'] as $key => $job) {
                if ($job['state'] == '' || $job['city'] == '') {
                    unset($apiJobsArr['results'][$key]);
                    $apiJobsArr['count'] = $apiJobsArr['count'] - 1;
                }
            }
            $lastPageNumber = (int) ($apiJobsArr['count'] / $jobsPerPage);
            if (($apiJobsArr['count'] % $jobsPerPage) > 0) {
                $lastPageNumber++;
            }
        }

        /*         * *************** [ End API Search Results ]****************** */
        /*         * *********************************************** */

//        print_r($userSearchResults);
        //return new Response("Done");
        return $this->render('ObjectsInternJumpBundle:InternjumpUser:userSearchResultPage.html.twig', array(
                    'jobs' => $userSearchResults,
                    'apiJobs' => $apiJobsArr,
                    'page' => $page,
                    'jobsPerPage' => $jobsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'title' => $title,
                    'country' => $country,
                    'city' => $city,
                    'state' => $state, 'category' => $category, 'company' => $company, 'lang' => $lang, 'jobtype' => $jobt,
        ));
    }

    /**
     * This function for search ajax action
     * @author Ola
     */
    public function fb_searchAction($title, $country, $city, $state, $category, $company, $lang, $jobt, $page) {
        $request = $this->getRequest();


        $session = $request->getSession();
        $searchQuery = array('title' => $title, 'country' => $country, 'city' => $city, 'state' => $state, 'category' => $category, 'company' => $company, 'lang' => $lang, 'jobt' => $jobt);
        $session->set('searchQuery', $searchQuery);

        $session->set('page', $page);
        //to check if Ajax Request
//        if (!$request->isXmlHttpRequest()) {
//            return new Response("Faild");
//        }

        /*         * **to get array of keywords from search text *** */
        $keywordsArray = explode(" ", $title);

//        print_r($keywordsArray);exit;
        //get number of jobs per page
        $jobsPerPage = $this->container->getParameter('jobs_per_search_results_page');

        $em = $this->getDoctrine()->getEntityManager();
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');

        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        $companyId = $company;
        $companyObj = $companyRepo->findOneBy(array('loginName' => $company));
                if ($companyObj) {
                    $companyId = $companyObj->getId();
                }
        //get jobs search results array
        $userSearchResults = $internshipRepo->getJobsSearchResult($title, $country, $city, $state, $category, $companyId, $lang, $keywordsArray, $jobt, $page, $jobsPerPage);

        //Limit the details to only 200 character
        foreach ($userSearchResults as $job) {
            $jobDesc = strip_tags($job->getDescription());
            if (strlen($jobDesc) > 200) {
                $job->setDescription(substr($jobDesc, 0, 200) . '...');
            } else {
                $job->setDescription($jobDesc);
            }
        }
        /* pagenation part */
        //get count of all search result jobs
        $userSearchResultsCount = sizeof($internshipRepo->getJobsSearchResult($title, $country, $city, $state, $category, $companyId, $lang, $keywordsArray, $jobt, 1, null));

        $lastPageNumber = (int) ($userSearchResultsCount / $jobsPerPage);
        if (($userSearchResultsCount % $jobsPerPage) > 0) {
            $lastPageNumber++;
        }


        /*         * ***************[ Start API Search Results ]********************** */
        /*         * *********************************************** */
        $apiJobsArr = array();
        if (sizeof($userSearchResults) < 24) {
            $title1 = $title;
            if ($title == "empty") {
                if ($category != "empty") {
                    $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');
                    //get countries array
                    $categoryObj = $categoryRepo->findOneBy(array('id' => $category));
                    $title1 = $categoryObj->getSlug();

                }
            }
            if ($city != "empty" && $state != "empty") {
                $jobLocation = $city . ', ' . $state;
            } else {
                if ($city != "empty") {
                    $jobLocation = $city;
                } elseif ($state != "empty") {
                    $jobLocation = $state;
                } else {
                    $jobLocation = null;
                }
            }

            //get company name if Exist
            $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
            $companyObj = $companyRepo->findOneBy(array('loginName' => $company));
            if ($companyObj) {
                $companyName = $companyObj->getName();
            } else {
                $companyName = $company;
            }
            $apiJobsArr = $this->searchForIndeedJobs($searchString = $title1, $start = $page * $jobsPerPage, $limit = $jobsPerPage, $jobLocation, $jobt, $companyName);

            foreach ($apiJobsArr['results'] as $key => $job) {
                if ($job['state'] == '' || $job['city'] == '') {
                    unset($apiJobsArr['results'][$key]);
                    $apiJobsArr['count'] = $apiJobsArr['count'] - 1;
                }
            }
            $lastPageNumber = (int) ($apiJobsArr['count'] / $jobsPerPage);
            if (($apiJobsArr['count'] % $jobsPerPage) > 0) {
                $lastPageNumber++;
            }
        }

        /*         * *************** [ End API Search Results ]****************** */
        /*         * *********************************************** */

//        print_r($userSearchResults);
        //return new Response("Done");
        return $this->render('ObjectsInternJumpBundle:InternjumpUser:fb_userSearchResultPage.html.twig', array(
                    'jobs' => $userSearchResults,
                    'apiJobs' => $apiJobsArr,
                    'page' => $page,
                    'jobsPerPage' => $jobsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'title' => $title,
                    'country' => $country,
                    'city' => $city,
                    'state' => $state, 'category' => $category, 'company' => $company, 'lang' => $lang, 'jobtype' => $jobt,
        ));
    }

    /**
     * @author Ola
     * to get all categories which start match with first 3 letters of entered text
     * @return json array of jobs
     */
    public function autocompleteAction() {

        $request = $this->getRequest();
        $text = $request->get('term');
        $em = $this->getDoctrine()->getEntityManager();

        //get cv repo
        $cvCategoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');
        //get autocomplete results
        $autoresult = $cvCategoryRepo->autocompletecategories($text);

        $response = new Response(json_encode($autoresult));
        return $response;
    }

    /**
     * @author Ola
     * to get all cities which start match with first 3 letters of entered text
     * @return json array of cities
     */
    public function citiesAutocompleteAction() {

        $request = $this->getRequest();
        $text = $request->get('city');
        $country = $request->get('country');
        $em = $this->getDoctrine()->getEntityManager();

        //get City repo
        $cvCityRepo = $em->getRepository('ObjectsInternJumpBundle:City');
        //get autocomplete results
        $autoresult = $cvCityRepo->autocompletecities($text, $country);

        $response = new Response(json_encode($autoresult));
        return $response;
    }

    /**
     * this function will used to ChangeStatus/Delete one or more CV (published/unpublished/delete)
     * @author Ola
     * @param Array of int $cvsId
     * @param string $status
     * @param string $method  define the action to perform delete/publish/unpublish
     */
    public function changeCvsStatusAction($cvsId, $status, $method) {

        $request = $this->getRequest();
        if (FALSE === $this->get('security.context')->isGranted('ROLE_USER') || !$request->isXmlHttpRequest()) {
            //return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');

        //get loggedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //user cv(s) ids
        $cvsIdArr = explode(",", $cvsId);

        if ($cvsIdArr) {
            $Cvs = $cvRepo->PerformActionsOnUserCvs($cvsIdArr);
        }

        //if cvs found
        if ($Cvs) {
            //check if the loggedin user is owner for each cv
            foreach ($Cvs as $cv) {
                if ($cv->getUser()->getId() == $user->getId()) {
                    //in case publish/unpublish Buttons
                    if ($method == "pubUnpub") {
                        $cv->setIsActive($status);
                        $em->flush();
                    }
                    //in case Delete Button
                    elseif ($method == "delete") {
                        $em->remove($cv);
                        $em->flush();
                    }
                }
            }
        } else { //no cvs
            return new Response('faild');
        }
        //Action succeed
        return new Response('done');
    }

}

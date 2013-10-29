<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Objects\InternJumpBundle\Entity\Company;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Objects\InternJumpBundle\Entity\City;
use Objects\InternJumpBundle\Entity\UserLanguage;

class CompanyController extends Controller {

    /**
     * this function used to return company widget
     * @author ahmed
     * @param int $id
     * @param int $jobs
     */
    public function showCompanyWidgetAction($id, $jobs) {
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');

        $company = $companyRepo->find($id);

        //get latest company jobs
        $latestCompanyJobs = $companyRepo->getLatestjobs($id, $jobs);

        return $this->render('ObjectsInternJumpBundle:Company:showCompanyWidget.html.twig', array(
                    'company' => $company,
                    'latestCompanyJobs' => $latestCompanyJobs
        ));
    }

    /**
     * this function used to create company widget
     * @author ahmed
     * @param integer $id
     */
    public function createCompanyWidgetAction($id) {
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');

        $company = $companyRepo->find($id);

        if (!$company) {
            $message = $this->container->getParameter('company_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        $request = $this->getRequest();
        $width = $request->get('width', 250);
        $height = $request->get('height', 250);
        $jobs = $request->get('jobs', 2);
        $border = $request->get('border', 0);

        return $this->render('ObjectsInternJumpBundle:Company:createCompanyWidget.html.twig', array(
                    'company' => $company,
                    'width' => $width,
                    'height' => $height,
                    'jobs' => $jobs,
                    'border' => $border
        ));
    }

    /**
     * this function used to add user to favorite list for company
     * @author ahmed
     * @param integer $userId
     * @param string $userId
     */
    public function addUserToFavoriteAction($userId, $status) {
        //check if the company is already active
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return new Response('failed');
        }
        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        //get the user
        $user = $userRepo->find($userId);

        if (!$user) {
            return new Response('failed');
        }

        if ($status == 'add') {
            //add the user to company favorite list
            $company->addUser($user);
        } else {
            $company->getFavoriteUsers()->removeElement($user);
        }
        $em->flush();

        return new Response('done');
    }

    /**
     * this function used to resend activation mail to not actice company
     * @author ahmed
     */
    public function reActivationAction() {
        //get the container object
        $container = $this->container;
        //get the translator object
        $translator = $this->get('translator');
        //get the session object
        $session = $this->getRequest()->getSession();
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //check if we have a logged company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE_COMPANY')) {
            $session->setFlash('note', $translator->trans('You need to Login first.'));
            return $this->redirect($this->generateUrl('login'));
        }

        //check if the user is already active
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //set a notice flag
            $session->setFlash('notice', $translator->trans('Your acount is active.'));
            return $this->redirect($this->generateUrl('company_edit'));
        }

        //get the logedin user
        $company = $this->get('security.context')->getToken()->getUser();

        //get the not active role object
        $role = $em->getRepository('ObjectsUserBundle:Role')->findOneByName('ROLE_NOTACTIVE_COMPANY');
        //check if the user already has the role
        if (!$company->getCompanyRoles()->contains($role)) {
            //add the role to the user
            $company->addRole($role);
        }
        //prepare the body of the email
        $body = $this->renderView('ObjectsInternJumpBundle:Company:welcome_to_site.txt.twig', array(
            'company' => $company,
            'password' => $company->getUserPassword(),
            'Email' => $this->container->getParameter('contact_us_email'),
        ));
        //prepare the message object
        $message = \Swift_Message::newInstance()
                ->setSubject($translator->trans('activate your account'))
                ->setFrom($container->getParameter('mailer_user'))
                ->setTo($company->getEmail())
                ->setBody($body)
        ;
        //send the activation mail to the user
        $this->get('mailer')->send($message);
        //set the success flag in the session
        $session->setFlash('success', $this->get('translator')->trans('A new activation email has been sent. Please check your email, be patient as it may take 5 to 10 minutes. Be sure to check your spam folder and if possible add noreply@internjump.com to your address book. If you do not receive the activation email, please feel free to contact us via email so we activate your account'));
        //redirect the user to portal
        return $this->redirect($this->generateUrl('internship', array('loginName' => $company->getLoginName())));
    }

    /**
     * the search for company action
     * @author Mahmoud
     * @param string $orderBy
     * @param string $orderDirection
     * @param integer $page
     * @return Response
     */
    public function searchForCompanyAction($page, $orderBy, $orderDirection) {
        $itemsPerPage = 10;
        $searchString = $this->getRequest()->get('searchString');
        if (!isset($orderBy)) {
            $orderBy = 'name';
        }
        if (!isset($orderDirection)) {
            $orderDirection = 'asc';
        }
        //get the user messages
        $data = $this->getDoctrine()->getEntityManager()->getRepository('ObjectsInternJumpBundle:Company')->searchForCompany($searchString, $orderBy, $orderDirection, $page, $itemsPerPage);
        $entities = $data['entities'];
        $count = $data['count'];
        //calculate the last page number
        $lastPageNumber = (int) ($count / $itemsPerPage);
        if (($count % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }
        $paginationParameters = array(
            'searchString' => $searchString,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDirection
        );
        return $this->render('ObjectsInternJumpBundle:Company:searchForCompany.html.twig', array(
                    'searchString' => $searchString,
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'count' => $count,
                    'lastPageNumber' => $lastPageNumber,
                    'paginationParameters' => $paginationParameters,
                    'entities' => $entities
        ));
    }

    /**
     * the search for company action
     * @author Mahmoud
     * @param string $orderBy
     * @param string $orderDirection
     * @param integer $page
     * @return Response
     */
    public function facebookSearchForCompanyAction($page, $orderBy, $orderDirection) {
        $itemsPerPage = 10;
        $searchString = $this->getRequest()->get('searchString');
        if (!isset($orderBy)) {
            $orderBy = 'name';
        }
        if (!isset($orderDirection)) {
            $orderDirection = 'asc';
        }
        //get the user messages
        $data = $this->getDoctrine()->getEntityManager()->getRepository('ObjectsInternJumpBundle:Company')->searchForCompany($searchString, $orderBy, $orderDirection, $page, $itemsPerPage);
        $entities = $data['entities'];
        $count = $data['count'];
        //calculate the last page number
        $lastPageNumber = (int) ($count / $itemsPerPage);
        if (($count % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }
        $paginationParameters = array(
            'searchString' => $searchString,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDirection
        );
        return $this->render('ObjectsInternJumpBundle:Company:fb_searchForCompany.html.twig', array(
                    'searchString' => $searchString,
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'count' => $count,
                    'lastPageNumber' => $lastPageNumber,
                    'paginationParameters' => $paginationParameters,
                    'entities' => $entities
        ));
    }

    /**
     * list the cv categories (industries)
     * @author Mahmoud
     * @return Response
     */
    public function employersAction() {
        $entities = $this->getDoctrine()->getEntityManager()->getRepository('ObjectsInternJumpBundle:CVCategory')->findAll();
        return $this->render('ObjectsInternJumpBundle:Company:industries.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     * list the cv categories (industries)
     * @author Mahmoud
     * @return Response
     */
    public function facebookEmployersAction() {
        $entities = $this->getDoctrine()->getEntityManager()->getRepository('ObjectsInternJumpBundle:CVCategory')->findAll();
        return $this->render('ObjectsInternJumpBundle:Company:fb_industries.html.twig', array(
                    'entities' => $entities
        ));
    }

    /**
     * the industries companies list page
     * @author Mahmoud
     * @param string $industrySlug
     * @param string $orderBy
     * @param string $orderDirection
     * @param integer $page
     * @return Response
     * @throws 404
     */
    public function industryCompaniesAction($industrySlug, $page, $orderBy, $orderDirection) {
        $em = $this->getDoctrine()->getEntityManager();
        $industry = $em->getRepository('ObjectsInternJumpBundle:CVCategory')->findOneBySlug($industrySlug);
        if (!isset($industry)) {
            throw $this->createNotFoundException('Can not find the requested industry.');
        }
        if (!isset($orderBy)) {
            $orderBy = 'name';
        }
        if (!isset($orderDirection)) {
            $orderDirection = 'asc';
        }
        $itemsPerPage = 10;
        //get the user messages
        $data = $em->getRepository('ObjectsInternJumpBundle:Company')->getIndustryCompanies($industry->getId(), $orderBy, $orderDirection, $page, $itemsPerPage);
        $entities = $data['entities'];
        $count = $data['count'];
        //calculate the last page number
        $lastPageNumber = (int) ($count / $itemsPerPage);
        if (($count % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }
        $paginationParameters = array(
            'industrySlug' => $industrySlug,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDirection
        );
        return $this->render('ObjectsInternJumpBundle:Company:industryCompanies.html.twig', array(
                    'industry' => $industry,
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'count' => $count,
                    'lastPageNumber' => $lastPageNumber,
                    'paginationParameters' => $paginationParameters,
                    'entities' => $entities
        ));
    }

    /**
     * the industries companies list page
     * @author Mahmoud
     * @param string $industrySlug
     * @param string $orderBy
     * @param string $orderDirection
     * @param integer $page
     * @return Response
     * @throws 404
     */
    public function facebookIndustryCompaniesAction($industrySlug, $page, $orderBy, $orderDirection) {
        $em = $this->getDoctrine()->getEntityManager();
        $industry = $em->getRepository('ObjectsInternJumpBundle:CVCategory')->findOneBySlug($industrySlug);
        if (!isset($industry)) {
            throw $this->createNotFoundException('Can not find the requested industry.');
        }
        if (!isset($orderBy)) {
            $orderBy = 'name';
        }
        if (!isset($orderDirection)) {
            $orderDirection = 'asc';
        }
        $itemsPerPage = 10;
        //get the user messages
        $data = $em->getRepository('ObjectsInternJumpBundle:Company')->getIndustryCompanies($industry->getId(), $orderBy, $orderDirection, $page, $itemsPerPage);
        $entities = $data['entities'];
        $count = $data['count'];
        //calculate the last page number
        $lastPageNumber = (int) ($count / $itemsPerPage);
        if (($count % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }
        $paginationParameters = array(
            'industrySlug' => $industrySlug,
            'orderBy' => $orderBy,
            'orderDirection' => $orderDirection
        );
        return $this->render('ObjectsInternJumpBundle:Company:fb_industryCompanies.html.twig', array(
                    'industry' => $industry,
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'count' => $count,
                    'lastPageNumber' => $lastPageNumber,
                    'paginationParameters' => $paginationParameters,
                    'entities' => $entities
        ));
    }

    /**
     * the company public profile page
     * @author Mahmoud
     * @param string $industrySlug
     * @param string $loginName
     * @return Response
     * @throws 404
     */
    public function companyPublicProfileAction($industrySlug, $loginName) {
        $em = $this->getDoctrine()->getEntityManager();
        $company = $em->getRepository('ObjectsInternJumpBundle:Company')->findOneByLoginName($loginName);
        if (!isset($company)) {
            throw $this->createNotFoundException('Can not find the requested company.');
        }
        $industry = $em->getRepository('ObjectsInternJumpBundle:CVCategory')->findOneBySlug($industrySlug);
        if (!isset($industry)) {
            throw $this->createNotFoundException('Can not find the requested industry.');
        }
        $companyJobsCount = $em->getRepository('ObjectsInternJumpBundle:Internship')->countCompanyJobs($company->getId(), false);
        $viewJobsLink = false;
        if ($companyJobsCount[0]['jobsCount'] > 0) {
            $viewJobsLink = true;
        }
        return $this->render('ObjectsInternJumpBundle:Company:publicProfile.html.twig', array(
                    'company' => $company,
                    'industry' => $industry,
                    'viewJobsLink' => $viewJobsLink
        ));
    }

    /**
     * the company public profile page
     * @author Mahmoud
     * @param string $industrySlug
     * @param string $loginName
     * @return Response
     * @throws 404
     */
    public function facebookCompanyPublicProfileAction($industrySlug, $loginName) {
        $em = $this->getDoctrine()->getEntityManager();
        $company = $em->getRepository('ObjectsInternJumpBundle:Company')->findOneByLoginName($loginName);
        if (!isset($company)) {
            throw $this->createNotFoundException('Can not find the requested company.');
        }
        $industry = $em->getRepository('ObjectsInternJumpBundle:CVCategory')->findOneBySlug($industrySlug);
        if (!isset($industry)) {
            throw $this->createNotFoundException('Can not find the requested industry.');
        }
        $companyJobsCount = $em->getRepository('ObjectsInternJumpBundle:Internship')->countCompanyJobs($company->getId(), false);
        $viewJobsLink = false;
        if ($companyJobsCount[0]['jobsCount'] > 0) {
            $viewJobsLink = true;
        }
        return $this->render('ObjectsInternJumpBundle:Company:fb_publicProfile.html.twig', array(
                    'company' => $company,
                    'industry' => $industry,
                    'viewJobsLink' => $viewJobsLink
        ));
    }

    /**
     * this function used to add inerest to user
     * @author Ahmed
     * @param int $loginName
     * @param int $cvId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addInterestAction($loginName, $cvId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return new Response('failed');
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        $InterestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $request = $this->getRequest();
        //get user object
        $userObject = $userRepo->findOneBy(array('loginName' => $loginName));

        //create new interest object
        $interest = new \Objects\InternJumpBundle\Entity\Interest();
        $interest->setValidTo(new \DateTime('+1 year'));
        $interest->setCvId($cvId);

        //check if the there is an invalide interest
        $interestObject = $InterestRepo->findOneBy(array('company' => $company->getId(), 'user' => $userObject->getId()));
        if ($interestObject) {
            $em->remove($interestObject);
        }


        $interest->setCompany($company);
        $interest->setUser($userObject);
        $em->persist($interest);
        $em->flush();

        //add user notifications
        $notifications = new \Objects\InternJumpBundle\Entity\UserNotification();
        $notifications->setCompany($company);
        $notifications->setUser($userObject);
        $notifications->setType('company_interest');
        $notifications->setTypeId($interest->getId());
        $em->persist($notifications);
        $em->flush();

        //send user email
        InternjumpController::userNotificationMail($this->container, $userObject, $company, 'company_interest', $interest->getId());


        return new Response('done');
    }

    /**
     * the company search for cv page
     * @author Mahmoud
     */
    public function searchForCVAction() {
        //get the request object
        $request = $this->getRequest();
        //get the current company object
        $company = $this->get('security.context')->getToken()->getUser();
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the page number
        $page = $request->get('page', 1);
        //get the cvs per page count
        $itemsPerPage = $this->getRequest()->cookies->get('company_cvs_per_page_' . $company->getId(), 10);
        //get the search data
        $cvSearchFormSubmited = $request->get('cv-search-form-submited');
        $countryId = $request->get('country-id', $company->getCountry());
        $cityId = $request->get('city');
        $stateId = $request->get('state');
        $languageId = $request->get('language-id');
        $languageReadLevel = $request->get('language-read-option');
        $languageWrittenLevel = $request->get('language-written-option');
        $languageSpokenLevel = $request->get('language-spoken-option');
        $selectedSkillsIds = $request->get('skills-ids', array());
        $experienceYears = $request->get('experience-years', array());
        $selectedCategories = $request->get('selected-categories');
        if (!$cvSearchFormSubmited) {
            if (!$selectedCategories) {
                $selectedCategories = $em->getRepository('ObjectsInternJumpBundle:Internship')->getCompanyCategoriesIds($company->getId());
            }
            if (!$cityId) {
                $cityId = $company->getCity();
            }
            if (!$stateId) {
                $stateId = $company->getState();
            }
        }
        $searchString = $request->get('search-string');
        //get the filters data
        $countries = $em->getRepository('ObjectsInternJumpBundle:Country')->findAll();
        $skills = $em->getRepository('ObjectsInternJumpBundle:Skill')->findAll();
        $languages = $em->getRepository('ObjectsInternJumpBundle:Language')->findAll();
        $parentCategories = $em->getRepository('ObjectsInternJumpBundle:CVCategory')->getAllParentCategories();
        //search for cvs
        $data = $em->getRepository('ObjectsInternJumpBundle:CV')->searchForCVs($searchString, $countryId, $cityId, $stateId, $languageId, $languageReadLevel, $languageWrittenLevel, $languageSpokenLevel, $selectedSkillsIds, $selectedCategories, $experienceYears, $page, $itemsPerPage);
        $entities = $data['entities'];
        $count = $data['count'];
        //calculate the last page number
        $lastPageNumber = (int) ($count / $itemsPerPage);
        if (($count % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }
        $userLanguage = new UserLanguage();
        $paginationParameters = array(
            'cv-search-form-submited' => $cvSearchFormSubmited,
            'search-string' => $searchString,
            'country-id' => $countryId,
            'city' => $cityId,
            'state' => $stateId,
            'language-id' => $languageId,
            'language-read-option' => $languageReadLevel,
            'language-written-option' => $languageWrittenLevel,
            'language-spoken-option' => $languageSpokenLevel,
            'skills-ids' => $selectedSkillsIds,
            'experience-years' => $experienceYears,
            'selected-categories' => $selectedCategories
        );
        $twigParameters = array(
            'paginationParameters' => $paginationParameters,
            'page' => $page,
            'lastPageNumber' => $lastPageNumber,
            'itemsPerPage' => $itemsPerPage,
            'count' => $count,
            'searchString' => $searchString,
            'countries' => $countries,
            'countryId' => $countryId,
            'cityId' => $cityId,
            'stateId' => $stateId,
            'skills' => $skills,
            'selectedSkillsIds' => $selectedSkillsIds,
            'experienceYears' => $experienceYears,
            'parentCategories' => $parentCategories,
            'selectedCategories' => $selectedCategories,
            'cvs' => $entities,
            'languages' => $languages,
            'languageId' => $languageId,
            'languageReadLevel' => $languageReadLevel,
            'languageSpokenLevel' => $languageSpokenLevel,
            'languageWrittenLevel' => $languageWrittenLevel,
            'languagesOptions' => $userLanguage->getValidLanguagesOptions()
        );
        if ($request->isXmlHttpRequest()) {
            return $this->render('ObjectsInternJumpBundle:CV:company_search_ajax.html.twig', $twigParameters);
        }
        return $this->render('ObjectsInternJumpBundle:CV:company_search.html.twig', $twigParameters);
    }

    /**
     * this function will show company notifications
     * @author Ahmed
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getCompanyNotificationsAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return new Response('faild');
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $companyNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyNotification');
        $companyMessageRepo = $em->getRepository('ObjectsInternJumpBundle:Message');

        //count all company notifications
        $allCompanyNotificationsCount = $companyNotificationRepo->countAllCompanyNotifications($company->getId());
        //get the company new notifications by type
        $companyNotificationsCountByType = $companyNotificationRepo->countCompanyNotifications($company->getId());
        //count new company message
        $newMessagesCount = $companyMessageRepo->getCompanyNewMessagesCount($company->getId());
        return $this->render('ObjectsInternJumpBundle:Company:companyNotifications.html.twig', array(
                    'allCompanyNotificationsCount' => $allCompanyNotificationsCount,
                    'companyNotificationsCountByType' => $companyNotificationsCountByType,
                    'newMessagesCount' => $newMessagesCount
        ));
    }

    /**
     * the change of password page
     * @author Ahmed
     * @param string $confirmationCode the token sent to the user email
     * @param string $email the company email
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function companyChangePasswordAction($confirmationCode, $email) {
        //get the request object
        $request = $this->getRequest();
        //get the session object
        $session = $request->getSession();
        //get the translator object
        $translator = $this->get('translator');
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //try to get the company from the database
        $company = $em->getRepository('ObjectsInternJumpBundle:Company')->findoneBy(array('email' => $email, 'confirmationCode' => $confirmationCode));
        //check if we found the user
        if (!$company) {
            //set an error flag
            $session->setFlash('error', $translator->trans('invalid email or confirmation code'));
            //go to the login page
            return $this->redirect($this->generateUrl('login'));
        }
        //create a password form
        $form = $this->createFormBuilder($company, array(
                    'validation_groups' => array('password')
                ))
                ->add('userPassword', 'repeated', array(
                    'type' => 'password',
                    'first_name' => "Password",
                    'second_name' => "RePassword",
                    'invalid_message' => "The passwords don't match",
                ))
                ->getForm();
        //check if form is posted
        if ($request->getMethod() == 'POST') {
            //bind the user data to the form
            $form->bindRequest($request);
            //check if form is valid
            if ($form->isValid()) {
                //set the password for the user
                $company->setValidPassword();
                //save the new hashed password
                $em->flush();
                //try to login the user
                try {
                    // create the authentication token
                    $token = new UsernamePasswordToken($company, null, 'main', $company->getRoles());
                    // give it to the security context
                    $this->get('security.context')->setToken($token);
                } catch (\Exception $e) {
                    //can not reload the user object log out the user
                    $this->get('security.context')->setToken(null);
                    //invalidate the current user session
                    $session->invalidate();
                    //set the success flag
                    $session->setFlash('success', $translator->trans('password changed'));
                    //redirect to the login page
                    return $this->redirect($this->generateUrl('login'));
                }
                //check if the company is active
                if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
                    //activate the user if not active
                    $this->activationAction($confirmationCode);
                    //clear the flashes set by the activation action
                    $session->clearFlashes();
                }
                //set the success flag
                $session->setFlash('success', $translator->trans('password changed'));
                //go to the edit profile page
                return $this->redirect($this->generateUrl('company_edit'));
            }
        }
        return $this->render('ObjectsInternJumpBundle:Company:change_password.html.twig', array(
                    'form' => $form->createView(),
                    'company' => $company
        ));
    }

    /**
     * company forgot password action
     * this function gets the company email and sends him email to let him change his password
     * @author Ahmed
     */
    public function companyForgotPasswordAction() {
        //get the request object
        $request = $this->getRequest();
        //prepare the form validation constrains
        $collectionConstraint = new Collection(array(
            'email' => new Email()
        ));
        //create the form
        $form = $this->createFormBuilder(null, array(
                    'validation_constraint' => $collectionConstraint,
                ))->add('email', 'email')
                ->getForm();
        //initialze the error string
        $error = FALSE;
        //initialze the success string
        $success = FALSE;
        //check if form is posted
        if ($request->getMethod() == 'POST') {
            //bind the user data to the form
            $form->bindRequest($request);
            //check if form is valid
            if ($form->isValid()) {
                //get the translator object
                $translator = $this->get('translator');
                //get the form data
                $data = $form->getData();
                //get the email
                $email = $data['email'];
                //search for the company with the entered email
                $company = $this->getDoctrine()->getRepository('ObjectsInternJumpBundle:Company')->findOneBy(array('email' => $email));
                //check if we found the user
                if ($company) {
                    //set a new token for the user
                    $company->setConfirmationCode(md5(uniqid(rand())));
                    //save the new user token into database
                    $this->getDoctrine()->getEntityManager()->flush();
                    //prepare the body of the email
                    $body = $this->renderView('ObjectsInternJumpBundle:Company:forgot_your_password.txt.twig', array('company' => $company));
                    //prepare the message object
                    $message = \Swift_Message::newInstance()
                            ->setSubject($this->get('translator')->trans('forgot your password'))
                            ->setFrom($this->container->getParameter('contact_us_email'))
                            ->setTo($company->getEmail())
                            ->setBody($body)
                    ;
                    //send the email
                    $this->get('mailer')->send($message);
                    //set the success message
                    $success = $translator->trans('done please check your email');
                } else {
                    //set the error message
                    $error = $translator->trans('the entered email was not found');
                }
            }
        }
        return $this->render('ObjectsInternJumpBundle:Company:forgot_password.html.twig', array(
                    'form' => $form->createView(),
                    'error' => $error,
                    'success' => $success
        ));
    }

    /**
     * this function will used to create new company
     * @author Ahmed
     */
    public function companyEditAction() {
        //check if we have a logged in user or company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        $cityRepo = $em->getRepository('ObjectsInternJumpBundle:City');
        $stateRepo = $em->getRepository('ObjectsInternJumpBundle:State');
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');


        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray [$value['id']] = $value['name'];
        }

        //get the request object
        $request = $this->getRequest();
        //get the session object
        $session = $request->getSession();
        //get the translator object
        $translator = $this->get('translator');
        //get the container object
        $container = $this->container;
        //initialize the success message
        $message = FALSE;
        //initialize the old password to not required
        $oldPassword = FALSE;
        //initialize the change user name to false
        $changeUserName = FALSE;
        //initialize the redirect flag
        $redirect = FALSE;
        //check if the user is logged in from the login form
        if (FALSE === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            //mark the old password as required
            $oldPassword = TRUE;
        }
        //check if the user can change the login name
        if ($container->getParameter('login_name_required') && TRUE === $this->get('security.context')->isGranted('ROLE_UPDATABLE_USERNAME')) {
            //make the user able to change his user name
            $changeUserName = TRUE;
        }
        //add validation groups array
        $formValidationGroups = array();
        //check if the old password is required
        if ($oldPassword) {
            //add the old password group to the form validation array
            $formValidationGroups [] = 'oldPassword';
        }
        //check if the user can change his user name
        if ($changeUserName) {
            //add the login name group to the form validation array
            $formValidationGroups [] = 'loginName';
        }
        //get the company object from the firewall
        $company = $this->get('security.context')->getToken()->getUser();

        //get the old company email
        $oldEmail = $company->getEmail();
        //get the old company name
        $oldLoginName = $company->getLoginName();
        $formValidationGroups [] = 'edit';
        //create a signup form
        $formBuilder = $this->createFormBuilder($company, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('name')
                ->add('userPassword', 'repeated', array(
                    'type' => 'password',
                    'first_name' => 'Password',
                    'second_name' => 'RePassword',
                    'invalid_message' => "The passwords don't match",
                    'required' => false))
                ->add('country', 'choice', array('choices' => $allCountriesArray,
                    'preferred_choices' => array($company->getCountry()), 'attr' => array('class' => 'chzn-select')))
                ->add('city')
                ->add('state', 'choice', array('required' => FALSE, 'attr' => array('class' => 'chzn-select')))
                ->add('address', 'text')
                //->add('establishmentDate', 'date', array('attr' => array('class' => 'establishmentDate'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->add('email')
                ->add('telephone')
                ->add('fax')
                ->add('url')
                ->add('facebookUrl')
                ->add('twitterUrl')
                ->add('googlePlusUrl')
                ->add('linkedInUrl')
                ->add('youtubeUrl')
                ->add('zipcode')
                ->add('notification', null, array('required' => FALSE))
                ->add('Latitude', 'hidden')
                ->add('Longitude', 'hidden')
                ->add('file', 'file', array('required' => false, 'label' => 'Logo', 'attr' => array('onchange' => 'readURL(this);')))
                ->add('professions', null, array('required' => FALSE))
        ;
        //check if the old password is required
        if ($oldPassword) {
            //add the old password field
            $formBuilder->add('oldPassword', 'password');
        }
        //check if the user can change his user name
        if ($changeUserName) {
            //add the login name field
            $formBuilder->add('loginName');
        }
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //get the company
                $company = $form->getData();

                if ($company->getEmail() != $oldEmail && !$container->getParameter('auto_active')) {
                    //remove the role company
                    foreach ($company->getCompanyRoles() as $key => $roleObject) {
                        //check if this is the wanted role
                        if ($roleObject->getName() == 'ROLE_COMPANY') {
                            //remove the role from the user
                            $company->getCompanyRoles()->remove($key);
                            //stop the search
                            break;
                        }
                    }

                    //get the not active company role object
                    $role = $em->getRepository('ObjectsUserBundle:Role')->findOneByName('ROLE_NOTACTIVE_COMPANY');
                    //check if the company already has the role
                    if (!$company->getCompanyRoles()->contains($role)) {
                        //add the role to the user
                        $company->addRole($role);
                    }
                    //prepare the body of the email
                    $body = $this->renderView('ObjectsInternJumpBundle:Company:activate_email.txt.twig', array('company' => $company));
                    //prepare the message object
                    $message = \Swift_Message::newInstance()
                            ->setSubject($translator->trans('activate your account'))
                            ->setFrom($container->getParameter('mailer_user'))
                            ->setTo($company->getEmail())
                            ->setBody($body)
                    ;
                    //send the activation mail to the user
                    $this->get('mailer')->send($message);
                }
                //check if the company changed his login name
                if ($changeUserName && $oldLoginName != $company->getLoginName()) {
                    //remove the update company name role
                    foreach ($company->getCompanyRoles() as $key => $roleObject) {
                        //check if this is the wanted role
                        if ($roleObject->getName() == 'ROLE_UPDATABLE_USERNAME') {
                            //remove the role from the user
                            $company->getCompanyRoles()->remove($key);
                            //stop the search
                            break;
                        }
                    }
                    //redirect the user to remove the login name from the form and refresh his roles
                    $redirect = TRUE;
                }
                //set the password for the user if changed
                $company->setValidPassword();
                $company->preUpload();


                //save the data
                $em->flush();

                $company->upload();
                //check if the user set a valid old password
                if ($oldPassword) {
                    //redirect the user to remove the old password from the form
                    $redirect = TRUE;
                }
                //check if we need to redirect the user
                if ($redirect) {
                    //set the success flash
                    $session->setFlash('success', $translator->trans('Done'));
                    //make the user fully authenticated and refresh his roles
                    try {
                        // create the authentication token
                        $token = new UsernamePasswordToken($company, null, 'main', $company->getRoles());
                        // give it to the security context
                        $this->get('security.context')->setToken($token);
                    } catch (\Exception $e) {
                        //can not reload the user object log out the user
                        $this->get('security.context')->setToken(null);
                        //invalidate the current user session
                        $this->getRequest()->getSession()->invalidate();
                        //redirect to the login page
                        return $this->redirect($this->generateUrl('login', array(), TRUE));
                    }
                    //redirect the user
                    return $this->redirect($this->generateUrl('company_edit'));
                }
                //set the success message
                $message = $translator->trans('Done');
            }
        }

        return $this->render('ObjectsInternJumpBundle:Company:companyEdit.html.twig', array(
                    'form' => $form->createView(),
                    'company' => $company,
                    'message' => $message,
                    'oldPassword' => $oldPassword,
                    'changeUserName' => $changeUserName,
                    'no_zipcode_message_new_job_page' => $this->container->getParameter('no_zipcode_message_new_job_page'),
                    'map_change_location_message' => $this->container->getParameter('map_change_location_message_new_job_page'),
                    'default_latitude' => $this->container->getParameter('default_latitude_company_signup_page'),
                    'default_longitude' => $this->container->getParameter('default_longitude_company_signup_page')
        ));
    }

    /**
     * this action will activate the company account and redirect him to the home page
     * @author Ahmed
     * @param string $confirmationCode
     */
    public function activationAction($confirmationCode) {

        //get the company object from the firewall
        $company = $this->get('security.context')->getToken()->getUser();
        //get the session object
        $session = $this->getRequest()->getSession();
        //get the translator object
        $translator = $this->get('translator');
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();

        //check if we have a logged in user or company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE_COMPANY')) {
            $session->setFlash('note', $translator->trans('You need to Login first'));
            return $this->redirect($this->generateUrl('login'));
        }

        //get a company role object
        $roleCompany = $em->getRepository('ObjectsUserBundle:Role')->findOneByName('ROLE_COMPANY');
        //check if the company is already active (the company might visit the link twice)
        if ($company->getCompanyRoles()->contains($roleCompany)) {
            //set a notice flag
            $session->setFlash('notice', $translator->trans('nothing to do'));
        } else {
            //check if the confirmation code is correct
            if ($company->getConfirmationCode() == $confirmationCode) {
                //get the current company roles
                $companyRoles = $company->getCompanyRoles();
                //try to get the not active role
                foreach ($companyRoles as $key => $role) {
                    //check if this role is the not active role
                    if ($role->getName() == 'ROLE_NOTACTIVE_COMPANY') {
                        //remove the not active role
                        $companyRoles->remove($key);
                        //end the search
                        break;
                    }
                }
                //add the company role
                $company->addRole($roleCompany);
                $company->setActivatedAt(new \DateTime());
                //save the new role for the company
                $em->flush();
                //set a success flag
                $session->setFlash('success', $translator->trans('your account is now active'));
                //try to refresh the user object roles in the firewall session
                try {
                    // create the authentication token
                    $token = new UsernamePasswordToken($company, null, 'main', $company->getRoles());
                    // give it to the security context
                    $this->get('security.context')->setToken($token);
                } catch (\Exception $e) {
                    //can not reload the user object log out the user
                    $this->get('security.context')->setToken(null);
                    //invalidate the current user session
                    $this->getRequest()->getSession()->invalidate();
                    //redirect to the login page
                    return $this->redirect($this->generateUrl('login'));
                }
            } else {
                //set an error flag
                $session->setFlash('error', $translator->trans('invalid confirmation code'));
            }
        }
        //go to the edit profile page
        return $this->redirect($this->generateUrl('company_edit'));
    }

    /**
     * this function is used to insert a new city or update the city priority if the city was found
     * @author Mahmoud
     * @param string $cityName
     * @param string $countryName
     * @param boolean $updateCity
     */
    private function insertCityIfNotFound($cityName, $countryName, $updateCity = false) {
        $em = $this->getDoctrine()->getEntityManager();
        $city = $em->getRepository('ObjectsInternJumpBundle:City')->findOneBy(array('name' => $cityName, 'country' => $countryName));
        if (!$city) {
            $country = $em->getRepository('ObjectsInternJumpBundle:Country')->find($countryName);
            if ($country) {
                $city = new City();
                $city->setCountry($country);
                $city->setName($cityName);
                $city->setSlug($cityName);
                $em->persist($city);
            }
        } else {
            if ($updateCity) {
                $em->getRepository('ObjectsInternJumpBundle:City')->increasePriority($city->getId());
            }
        }
        try {
            $em->flush();
        } catch (\Exception $e) {

        }
    }

    /**
     * this function will used to create new company
     * @author Ahmed
     */
    public function companySignupAction() {
        //check if we have a logged in user or company
        if (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE_COMPANY') || TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray [$value['id']] = $value['name'];
        }
        //Get State Repo
        $stateRepo = $em->getRepository('ObjectsInternJumpBundle:State');
        //Get The NewYork State which is decided to be set as a default state
        $defaultState = $stateRepo->findOneBy(array('slug' => 'new_york'));
        //Get the default state id to set it in the script chesen
        $defaultStateID = $defaultState->getId();
        //Get default state name
        $defaultStateName = $defaultState->getName();

        //get the request object
        $request = $this->getRequest();
        //create an emtpy user object
        $company = new Company();
        //add validation groups array
        $formValidationGroups = array();
        $formValidationGroups [] = 'signup';
        //create a signup form
        $formBuilder = $this->createFormBuilder($company, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('name')
                ->add('userPassword', 'repeated', array(
                    'type' => 'password',
                    'first_name' => 'Password',
                    'second_name' => 'RePassword',
                    'invalid_message' => "The passwords don't match"))
                //->add('establishmentDate', 'date', array('attr' => array('class' => 'establishmentDate'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->add('email', 'repeated', array(
                    'type' => 'email',
                    'first_name' => 'Email',
                    'second_name' => 'ReEmail',
                    'invalid_message' => "The emails don't match"
                ))
        ;
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //get the user object from the form
                $company = $form->getData();
                $company->setLoginName($company->getEmail());

                //finish company signup
                //add the new user to the entity manager
                $em->persist($company);
                //prepare the body of the email
                $body = $this->renderView('ObjectsInternJumpBundle:Company:welcome_to_site.txt.twig', array(
                    'company' => $company,
                    'password' => $company->getUserPassword(),
                    'Email' => $this->container->getParameter('contact_us_email'),
                ));
                //get the role repo
                $roleRepository = $em->getRepository('ObjectsUserBundle:Role');
                //get a ROLE_NOTACTIVE_COMPANY
                $roleActiveCompany = $roleRepository->findOneByName('ROLE_COMPANY');
                //get a update userName role object
                $roleUpdateUserName = $roleRepository->findOneByName('ROLE_UPDATABLE_USERNAME');
                //set company not active role
                $company->addRole($roleActiveCompany);
                $company->addRole($roleUpdateUserName);
                //store the object in the database
                $em->flush();
                //prepare the message object
                $message = \Swift_Message::newInstance()
                        ->setSubject($this->get('translator')->trans('Welcome to InternJump'))
                        ->setFrom($this->container->getParameter('mailer_user'))
                        ->setTo($company->getEmail())
                        ->setBody($body)
                ;
                //send the email
                $this->get('mailer')->send($message);
                //set the success flag in the session
                $this->getRequest()->getSession()->setFlash('success', $this->container->getParameter('company_signup_welcome_message'));

                //try to login the company
                try {
                    // create the authentication token
                    $token = new UsernamePasswordToken($company, null, 'main', $company->getRoles());
                    // give it to the security context
                    $this->get('security.context')->setToken($token);
                } catch (\Exception $e) {
                    //can not reload the user object log out the user
                    $this->get('security.context')->setToken(null);
                    //invalidate the current user session
                    $this->getRequest()->getSession()->invalidate();
                    //redirect to the login page
                    return $this->redirect($this->generateUrl('login'));
                }


                //go to the company home page
                return $this->redirect($this->generateUrl('internship', array('loginName' => $company->getLoginName()), TRUE));
            }
        }

        return $this->render('ObjectsInternJumpBundle:Company:companySignup.html.twig', array(
                    'form' => $form->createView(),
                    'no_zipcode_message_new_job_page' => $this->container->getParameter('no_zipcode_message_new_job_page'),
                    'map_change_location_message' => $this->container->getParameter('map_change_location_message_new_job_page'),
                    'default_latitude' => $this->container->getParameter('default_latitude_company_signup_page'),
                    'default_longitude' => $this->container->getParameter('default_longitude_company_signup_page'),
                    'formName' => $this->container->getParameter('companySignUp_FormName'),
                    'formDesc' => $this->container->getParameter('companySignUp_FormDesc'),
                    'stateId' => $defaultStateID,
                    'defaultStateName' => $defaultStateName,
        ));
    }

    /**
     * this function will show an interview for the logedin company user
     * @author Ahmed
     * @param int $interviewId
     */
    public function interviewShowAction($interviewId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        //get the interview object
        $interview = $interviewRepo->find($interviewId);
        if (!$interview || $interview->getCompany()->getId() != $company->getId()) {
            $message = $this->container->getParameter('interview_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        return $this->render('ObjectsInternJumpBundle:Company:interviewShow.html.twig', array(
                    'company' => $company,
                    'interview' => $interview
        ));
    }

    /**
     * this function will show single question for the owner company and user
     * @author Ahmed
     * @param int $questionId
     */
    public function questionShowAction($questionId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY') && FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');

        //get the question
        $question = $companyQuestionRepo->find($questionId);
        if (!$question) {
            $message = $this->container->getParameter('question_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get the company
        $questionCompany = $question->getCompany();

        $variables = array();
        $variables ['question'] = $question;
        $variables ['questionCompany'] = $questionCompany;

        //check if company view or user view
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //get logedin company objects
            $company = $this->get('security.context')->getToken()->getUser();
            //check if not this company is the owner
            if ($question->getCompany()->getId() != $company->getId()) {
                return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            }
            $variables ['company'] = $company;
        }if (TRUE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user objects
            $user = $this->get('security.context')->getToken()->getUser();
            //check if not this user is the owner
            if ($question->getUser()->getId() != $user->getId()) {
                return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            }
            $variables ['user'] = $user;
        }



        return $this->render('ObjectsInternJumpBundle:Company:questionShow.html.twig', $variables);
    }

    /**
     * this function will show single question for the owner company and user
     * @author Ahmed
     * @param int $questionId
     */
    public function fb_questionShowAction($questionId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY') && FALSE === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_fb_homepage', array(), TRUE));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $companyQuestionRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyQuestion');

        //get the question
        $question = $companyQuestionRepo->find($questionId);
        if (!$question) {
            $message = $this->container->getParameter('question_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }

        //get the company
        $questionCompany = $question->getCompany();

        $variables = array();
        $variables ['question'] = $question;
        $variables ['questionCompany'] = $questionCompany;

        //check if company view or user view
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //get logedin company objects
            $company = $this->get('security.context')->getToken()->getUser();
            //check if not this company is the owner
            if ($question->getCompany()->getId() != $company->getId()) {
                return $this->redirect($this->generateUrl('site_fb_homepage', array(), TRUE));
            }
            $variables ['company'] = $company;
        }if (TRUE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user objects
            $user = $this->get('security.context')->getToken()->getUser();
            //check if not this user is the owner
            if ($question->getUser()->getId() != $user->getId()) {
                return $this->redirect($this->generateUrl('site_fb_homepage', array(), TRUE));
            }
            $variables ['user'] = $user;
        }



        return $this->render('ObjectsInternJumpBundle:Company:fb_questionShow.html.twig', $variables);
    }

    /**
     * this function will mark all notifications as read for the logedin company
     * @author Ahmed
     */
    public function companyMarkAllNotificationsAsReadAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $companyNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyNotification');

        //get all unread notifications
        $unReadNotifications = $companyNotificationRepo->findBy(array('company' => $company->getId(), 'isNew' => TRUE));
        foreach ($unReadNotifications as $unReadNotification) {
            $unReadNotification->setIsNew(FALSE);
        }

        $em->flush();
        return new Response('done');
    }

    /**
     * this function will mark notification as read for the logedin company
     * @author Ahmed
     * @param int $notificationId
     */
    public function companyNotificationMardAsReadAction($notificationId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $companyNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyNotification');

        //get the notification
        $notification = $companyNotificationRepo->find($notificationId);

        //check if this company is the owner
        if ($notification->getCompany()->getId() == $company->getId()) {
            $notification->setIsNew(FALSE);
            $em->flush();
        }
        return new Response('done');
    }

    /**
     * this function will show all company notification
     * @author Ahmed
     */
    public function companyNotificationAction($type, $page, $itemsPerPage) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $companyNotificationRepo = $em->getRepository('ObjectsInternJumpBundle:CompanyNotification');

        //check if we do not have the items per page number
        if (!$itemsPerPage) {
            //get the items per page from cookie or the default value
            $itemsPerPage = $this->getRequest()->cookies->get('company_notifications_per_page_' . $company->getId(), 10);
        }

        //get all company notifications
        if ($type == 'all') {
            $companyNotifications = $companyNotificationRepo->getCompanyNotifications($company->getId(), null, $page, $itemsPerPage);
            $count = sizeof($companyNotificationRepo->getCompanyNotifications($company->getId(), null, 1, null));

            //calculate the last page number
            $lastPageNumber = (int) ($count / $itemsPerPage);
            if (($count % $itemsPerPage) > 0) {
                $lastPageNumber++;
            }
        } else {
            $companyNotifications = $companyNotificationRepo->getCompanyNotifications($company->getId(), $type, $page, $itemsPerPage);
            $count = sizeof($companyNotificationRepo->getCompanyNotifications($company->getId(), $type, 1, null));

            //calculate the last page number
            $lastPageNumber = (int) ($count / $itemsPerPage);
            if (($count % $itemsPerPage) > 0) {
                $lastPageNumber++;
            }
        }

        //check if there is unread notifications
        $unreadNotifications = $companyNotificationRepo->findBy(array('company' => $company->getId(), 'isNew' => TRUE));

        return $this->render('ObjectsInternJumpBundle:Company:companyNotification.html.twig', array(
                    'company' => $company,
                    'type' => $type,
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'companyNotifications' => $companyNotifications,
                    'unreadNotifications' => $unreadNotifications
        ));
    }

    /**
     * this action will add interview request from company to user
     * @author Ahmed
     * @param int $userId
     */
    public function interviewRequestAction($userId, $cvId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        $InterviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');

        $request = $this->getRequest();
        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray [$value['id']] = $value['name'];
        }

        //get company jobs
        $companyJobs = $internshipRepo->getAllCompanyJobs($company->getId());

        //get the user
        $userObjct = $userRepo->find($userId);
        //create interview object
        $interview = new \Objects\InternJumpBundle\Entity\Interview();
        $interview->setAddress($company->getAddress());
        $interview->setCountry($company->getCountry());
        $interview->setCompany($company);
        $interview->setLatitude($company->getLatitude());
        $interview->setLongitude($company->getLongitude());
        $interview->setZipcode($company->getZipcode());
        $interview->setUser($userObjct);
        $interview->setInterviewDate(new \DateTime('today'));

        $formValidationGroups [] = 'interview';
        $form = $this->createFormBuilder($interview, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('internship', 'entity', array(
                    'class' => 'ObjectsInternJumpBundle:Internship',
                    'choices' => $companyJobs
                ))
                ->add('interviewDate', 'date', array('attr' => array('class' => 'interviewDate'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd H:mm'))
                ->add('country', 'choice', array(
                    'choices' => $allCountriesArray
                ))
                ->add('city')
                ->add('state', 'choice', array('empty_value' => '--- choose state ---', 'required' => false))
                ->add('address')
                ->add('details')
                ->add('zipcode')
                ->add('Latitude', 'hidden')
                ->add('Longitude', 'hidden')
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                //get the user object from the form
                $interview = $form->getData();

                //check if the there is an invalide interview request
                $interviewObject = $InterviewRepo->findOneBy(array('company' => $company->getId(), 'user' => $userId, 'internship' => $form->getData()->getInternship()->getId()));
                if ($interviewObject) {
                    if ($interviewObject->getAccepted() == 'accepted') {
//                        return new Response($this->container->getParameter('company_user_interview_accept_message_user_data_page'));
                        return $this->render('ObjectsInternJumpBundle:Company:interviewRequest.html.twig', array(
                                    'message' => $this->container->getParameter('company_user_interview_accept_message_user_data_page'),
                                    'userId' => $userId,
                                    'company' => $company,
                                    'cvId' => $cvId,
                                    'no_zipcode_message_new_job_page' => $this->container->getParameter('no_zipcode_message_new_job_page'),
                                    'map_change_location_message' => $this->container->getParameter('map_change_location_message_new_job_page')
                        ));
                    } elseif ($interviewObject->getAccepted() == 'rejected') {
//                        return new Response($this->container->getParameter('company_user_interview_reject_message_user_data_page'));
                        return $this->render('ObjectsInternJumpBundle:Company:interviewRequest.html.twig', array(
                                    'message' => $this->container->getParameter('company_user_interview_reject_message_user_data_page'),
                                    'userId' => $userId,
                                    'company' => $company,
                                    'cvId' => $cvId,
                                    'no_zipcode_message_new_job_page' => $this->container->getParameter('no_zipcode_message_new_job_page'),
                                    'map_change_location_message' => $this->container->getParameter('map_change_location_message_new_job_page')
                        ));
                    } elseif ($interviewObject->getAccepted() == 'pending') {
                        //check if invalid
                        if ($interviewObject->getInterviewDate() >= new \DateTime('today')) {
//                            return new Response($this->container->getParameter('company_user_interview_pending_message_user_data_page'));
                            return $this->render('ObjectsInternJumpBundle:Company:interviewRequest.html.twig', array(
                                        'message' => $this->container->getParameter('company_user_interview_pending_message_user_data_page'),
                                        'userId' => $userId,
                                        'company' => $company,
                                        'cvId' => $cvId,
                                        'no_zipcode_message_new_job_page' => $this->container->getParameter('no_zipcode_message_new_job_page'),
                                        'map_change_location_message' => $this->container->getParameter('map_change_location_message_new_job_page')
                            ));
                        } else {
                            $em->remove($interviewObject);
                        }
                    }
                }

                $em->persist($interview);
                $em->flush();

                //add user notification
                $userNotification = new \Objects\InternJumpBundle\Entity\UserNotification();
                $userNotification->setCompany($company);
                $userNotification->setUser($userObjct);
                $userNotification->setType('company_interview');
                $userNotification->setTypeId($interview->getId());
                $em->persist($userNotification);
                $em->flush();

                //send user email
                InternjumpController::userNotificationMail($this->container, $userObjct, $company, 'company_interview', $interview->getId());

//                return new Response($this->container->getParameter('create_interview_request_success_message_user_data_page'));
                return $this->render('ObjectsInternJumpBundle:Company:interviewRequest.html.twig', array(
                            'message' => $this->container->getParameter('create_interview_request_success_message_user_data_page'),
                            'userId' => $userId,
                            'company' => $company,
                            'cvId' => $cvId,
                            'no_zipcode_message_new_job_page' => $this->container->getParameter('no_zipcode_message_new_job_page'),
                            'map_change_location_message' => $this->container->getParameter('map_change_location_message_new_job_page')
                ));
            }
        }

        return $this->render('ObjectsInternJumpBundle:Company:interviewRequest.html.twig', array(
                    'form' => $form->createView(),
                    'userId' => $userId,
                    'company' => $company,
                    'cvId' => $cvId,
                    'no_zipcode_message_new_job_page' => $this->container->getParameter('no_zipcode_message_new_job_page'),
                    'map_change_location_message' => $this->container->getParameter('map_change_location_message_new_job_page')
        ));
    }

    /**
     * this function will add hire request from company to user
     * @author Ahmed
     * @param int $jobId
     * @param int $userId
     * @param int $cvId
     */
    public function companyHireUserAction($userId, $cvId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');
        $request = $this->getRequest();

        //get user bject
        $userObject = $userRepo->find($userId);
        //get the cv
        $userCv = $cvRepo->find($cvId);

        //create new User Internship object
        $newUserInternship = new \Objects\InternJumpBundle\Entity\UserInternship();
        $newUserInternship->setCv($userCv);
        $newUserInternship->setStatus('pending');
        $newUserInternship->setUser($userObject);

        $formValidationGroups [] = 'interview';

        //get company jobs
        $companyJobs = $internshipRepo->getAllCompanyJobs($company->getId());

        $form = $this->createFormBuilder($newUserInternship, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('internship', 'entity', array(
                    'class' => 'ObjectsInternJumpBundle:Internship',
                    'choices' => $companyJobs
                ))
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                //check if the there is an internship
                $userInternship = $userInternshipRepo->findOneBy(array('cv' => $cvId, 'internship' => $form->getData()->getInternship()->getId(), 'user' => $userId));

                if ($userInternship) {
                    if ($userInternship->getStatus() == 'accepted') {
                        return new Response($this->container->getParameter('company_user_hire_accept_message_user_data_page'));
                        return $this->render('ObjectsInternJumpBundle:Company:hireRequest.html.twig', array(
                                    'message' => $this->container->getParameter('company_user_hire_accept_message_user_data_page')
                        ));
                    } elseif ($userInternship->getStatus() == 'rejected') {
//                        return new Response($this->container->getParameter('company_user_hire_reject_message_user_data_page'));
                        return $this->render('ObjectsInternJumpBundle:Company:hireRequest.html.twig', array(
                                    'message' => $this->container->getParameter('company_user_hire_reject_message_user_data_page')
                        ));
                    } elseif ($userInternship->getStatus() == 'pending') {
//                        return new Response($this->container->getParameter('company_user_hire_pending_message_user_data_page'));
                        return $this->render('ObjectsInternJumpBundle:Company:hireRequest.html.twig', array(
                                    'message' => $this->container->getParameter('company_user_hire_pending_message_user_data_page')
                        ));
                    } elseif ($userInternship->getStatus() == 'apply') {
                        $userInternship->setStatus('pending');
                        $userInternship->setCreatedAt(new \DateTime());


                        //add user notification
                        $userNotification = new \Objects\InternJumpBundle\Entity\UserNotification();
                        $userNotification->setCompany($company);
                        $userNotification->setUser($userObject);
                        $userNotification->setType('company_job_hire');
                        $userNotification->setTypeId($userInternship->getId());
                        $em->persist($userNotification);
                        $em->flush();

                        //send user email
                        InternjumpController::userNotificationMail($this->container, $userObject, $company, 'company_job_hire', $newUserInternship->getId());

                        return $this->render('ObjectsInternJumpBundle:Company:hireRequest.html.twig', array(
                                    'message' => $this->container->getParameter('company_user_hire_pending_message_user_data_page')
                        ));
//                        return new Response($this->container->getParameter('company_user_hire_pending_message_user_data_page'));
                    }
                }

                $em->persist($newUserInternship);
                $em->flush();

                //add user notification
                $userNotification = new \Objects\InternJumpBundle\Entity\UserNotification();
                $userNotification->setCompany($company);
                $userNotification->setUser($userObject);
                $userNotification->setType('company_job_hire');
                $userNotification->setTypeId($newUserInternship->getId());
                $em->persist($userNotification);
                $em->flush();

                //send user email
                InternjumpController::userNotificationMail($this->container, $userObject, $company, 'company_job_hire', $newUserInternship->getId());


//                return new Response($this->container->getParameter('create_hire_request_success_message_user_data_page'));

                return $this->render('ObjectsInternJumpBundle:Company:hireRequest.html.twig', array(
                            'message' => $this->container->getParameter('create_hire_request_success_message_user_data_page')
                ));
            }
        }

        return $this->render('ObjectsInternJumpBundle:Company:hireRequest.html.twig', array(
                    'form' => $form->createView(),
                    'userId' => $userId,
                    'cvId' => $cvId
        ));
    }

    /**
     * this function will add interest from company to user\
     * @author Ahmed
     * @param int $userId
     */
    public function addUserInterestAction($loginName, $cvId) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        $InterestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $request = $this->getRequest();
        //get user object
        $userObject = $userRepo->findOneBy(array('loginName' => $loginName));

        //create new interest object
        $interest = new \Objects\InternJumpBundle\Entity\Interest();
        $interest->setValidTo(new \DateTime('today'));
        $interest->setCvId($cvId);
        $form = $this->createFormBuilder($interest)
                ->add('validTo', 'date', array('attr' => array('class' => 'validTo'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                //check if the there is an invalide interest
                $interestObject = $InterestRepo->findOneBy(array('company' => $company->getId(), 'user' => $userObject->getId()));
                if ($interestObject) {
                    $em->remove($interestObject);
                }


                $interest->setCompany($company);
                $interest->setUser($userObject);
                $em->persist($interest);
                $em->flush();

                //add user notifications
                $notifications = new \Objects\InternJumpBundle\Entity\UserNotification();
                $notifications->setCompany($company);
                $notifications->setUser($userObject);
                $notifications->setType('company_interest');
                $notifications->setTypeId($interest->getId());
                $em->persist($notifications);
                $em->flush();

                //send user email
                InternjumpController::userNotificationMail($this->container, $userObject, $company, 'company_interest', $interest->getId());


                return $this->redirect($this->generateUrl('company_see_user_data', array('userLoginName' => $loginName, 'cvId' => $cvId)));
            } else {
                foreach ($form->getErrors() as $error) {
                    $formError = $error->getMessageTemplate();
                }
                return $this->redirect($this->generateUrl('company_see_user_data', array(
                                    'userLoginName' => $loginName,
                                    'cvId' => $cvId,
                                    'errorMessage' => $formError
                )));
            }
        }
        return $this->render('ObjectsInternJumpBundle:Company:addUserInterest.html.twig', array(
                    'form' => $form->createView(),
                    'loginName' => $loginName,
                    'cvId' => $cvId
        ));
    }

    /**
     * this function will used by companies to add question for a user
     * @author Ahmed
     * @param int $userId
     */
    public function addUserQuestionAction($userLoginName, $question) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        //get logedin company objects
        $company = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getEntityManager();
        $userRepo = $em->getRepository('ObjectsUserBundle:User');

        //get user object
        $userObject = $userRepo->findOneBy(array('loginName' => $userLoginName));
        if (!$userObject) {
            $message = $this->container->getParameter('user_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //create new question object
        $newQuestionObject = new \Objects\InternJumpBundle\Entity\CompanyQuestion();
        $newQuestionObject->setCompany($company);
        $newQuestionObject->setUser($userObject);
        $newQuestionObject->setQuestion($question);
        $em->persist($newQuestionObject);
        $em->flush();

        //add user notification
        $userNotification = new \Objects\InternJumpBundle\Entity\UserNotification();
        $userNotification->setCompany($company);
        $userNotification->setUser($userObject);
        $userNotification->setType('company_question');
        $userNotification->setTypeId($newQuestionObject->getId());
        $em->persist($userNotification);
        $em->flush();

        //send user email
        InternjumpController::userNotificationMail($this->container, $userObject, $company, 'company_question', $newQuestionObject->getId());



        return new Response('done');
    }

}

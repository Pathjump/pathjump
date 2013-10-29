<?php

namespace Objects\InternJumpBundle\Controller;

use Objects\InternJumpBundle\Entity\Task;
use Objects\InternJumpBundle\Form\TaskType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Task controller.
 *
 */
class TaskController extends Controller {

    /**
     * Lists Student's all Task entities.
     *
     */
    public function studentAllTasksAction($loginName, $itemsPerPage, $status, $page) {

        //array that holds cvs categories to get latest jobs of the same categories
        $categ = array();
        //Number of cvs for status part in portal page
        $numOfCVs = 0;

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) { //if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();

        $cityRepo = $em->getRepository('ObjectsInternJumpBundle:City');
        $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');

        //$tasksPerPage = $this->container->getParameter('tasks_per_show_page'); //for pagenation
        //$entities = $em->getRepository('ObjectsInternJumpBundle:Task')->findAll();
        //Get current user
        $user = $this->get('security.context')->getToken()->getUser();
        $uid = $user->getId();

        //check if we do not have the items per page number
        if (!$itemsPerPage) {
            //get the items per page from cookie or the default value
            $itemsPerPage = $this->getRequest()->cookies->get('tasks_per_show_page_' . $uid, 3);
        }


        $tasks = $em->getRepository('ObjectsInternJumpBundle:Task')->getStudentAllTasks($uid, $page, $itemsPerPage, $status);

        $cvs = $em->getRepository('ObjectsInternJumpBundle:CV')->getAllCvs($uid);
        //print_r($cvs);
        if ($cvs) {//if student Has CVs
            //echo "found cvs <br>";
            foreach ($cvs as $cv) {
                //echo $cv->getName();
                $numOfCVs+=1;
                foreach ($cv->getCategories() as $cat)
                //echo $cat->getName();
                    $categ[] = $cat->getId();
                //echo "category num".$cat->getId()." found<br>";}
            }
        }
        $limit = 4;
        if (sizeof($categ) > 0) { //found array of categories
            $LatestJobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getLatestJobs($categ, $limit);
            // echo "after exec found jobs array of size ".sizeof($LatestJobs);exit;
        } else { /* don't call the dql */
            $LatestJobs = "";
        }



        //for pagenation
        $tasksCount = $em->getRepository('ObjectsInternJumpBundle:Task')->countTasks($uid, "user", $status);

        $lastPageNumber = (int) ($tasksCount / $itemsPerPage);
        if (($tasksCount % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }


        $userJob = $em->getRepository('ObjectsInternJumpBundle:UserInternship')->findOneBy(array('user' => $uid, 'status' => "accepted"));

        //get User score
        $score = $user->getScore();
        //get quizResult Repo
        $quizResultRepo = $em->getRepository('ObjectsInternJumpBundle:QuizResult');

        //find all quiz results
        $quizResults = $quizResultRepo->findAll();
        $resultObject = null;
        if ($score) {
            foreach ($quizResults as $result) {
                if ($result->getScore() >= $score) {
                    $resultObject = $result;
                    break;
                }
                $resultObject = $result;
            }
        }

        //get interviews Repo
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');
        //get upcoming Interviews
        $upComingInterviews = $interviewRepo->getUpComingInterviews($uid);
//        print_r($upComingInterviews);exit;
        $upComingInterviewsCount = sizeof($upComingInterviews);

        //get jobs that user applied
        $appliedJobs = $em->getRepository('ObjectsInternJumpBundle:UserInternship')->getAppliedJobs($uid);


        //get latest 3 notifications
        $latestNotifications = $em->getRepository('ObjectsInternJumpBundle:UserNotification')->getLatestThree($uid);
        //get latest 3 messages
        $latestMessages = $em->getRepository('ObjectsInternJumpBundle:Message')->getLatestThree($uid);


        //For search form
        //all cities
        $allCities = $cityRepo->findBy(array('country' => 'US'), array('name' => 'asc'));
        //all category
        $allCategory = $categoryRepo->findBy(array(), array('name' => 'asc'));
        //Get users State for the hidden Feild
        $uState = $user->getState();


        //Get the user's worth result
        $worth = $user->getNetWorth();

        //get favorite companies
        $favCompanies = $user->getFavoriteComapnies();

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

        return $this->render('ObjectsInternJumpBundle:Task:studentTasks.html.twig', array(
                    'entities' => $tasks,
                    'user' => $user,
                    'userjob' => $userJob,
                    'status' => $status,
                    'flag' => $flag,
                    'cvCategoris' => $categ,
                    'latestJobs' => $LatestJobs,
                    'page' => $page,
                    'tasksPerPage' => $itemsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'quizResult' => $resultObject,
                    'cvCount' => $numOfCVs,
                    'interviews' => $upComingInterviews,
                    'interviewsCount' => $upComingInterviewsCount,
                    'appliedJobs' => $appliedJobs,
                    'latestNotifications' => $latestNotifications,
                    'latestMessages' => $latestMessages,
                    'allCities' => $allCities,
                    'allCategory' => $allCategory,
                    'state' => $uState,
                    'worth' => $worth,
                    'rand_banner' => $rand_banner,
                    'favCompanies' => $favCompanies
        ));
    }

    /**
     * For Facebook Version
     * Lists Student's all Task entities.
     *
     */
    public function fb_studentAllTasksAction($loginName, $itemsPerPage, $status, $page) {

        //array that holds cvs categories to get latest jobs of the same categories
        $categ = array();
        //Number of cvs for status part in portal page
        $numOfCVs = 0;

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) { //if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();

        $cityRepo = $em->getRepository('ObjectsInternJumpBundle:City');
        $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');

        //$tasksPerPage = $this->container->getParameter('tasks_per_show_page'); //for pagenation
        //$entities = $em->getRepository('ObjectsInternJumpBundle:Task')->findAll();
        //Get current user
        $user = $this->get('security.context')->getToken()->getUser();
        $uid = $user->getId();

        //check if we do not have the items per page number
        if (!$itemsPerPage) {
            //get the items per page from cookie or the default value
            $itemsPerPage = $this->getRequest()->cookies->get('tasks_per_show_page_' . $uid, 3);
        }


        $tasks = $em->getRepository('ObjectsInternJumpBundle:Task')->getStudentAllTasks($uid, $page, $itemsPerPage, $status);

        $cvs = $em->getRepository('ObjectsInternJumpBundle:CV')->getAllCvs($uid);
        //print_r($cvs);
        if ($cvs) {//if student Has CVs
            //echo "found cvs <br>";
            foreach ($cvs as $cv) {
                //echo $cv->getName();
                $numOfCVs+=1;
                foreach ($cv->getCategories() as $cat)
                //echo $cat->getName();
                    $categ[] = $cat->getId();
                //echo "category num".$cat->getId()." found<br>";}
            }
        }
        $limit = 4;
        if (sizeof($categ) > 0) { //found array of categories
            $LatestJobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getLatestJobs($categ, $limit);
            // echo "after exec found jobs array of size ".sizeof($LatestJobs);exit;
        } else { /* don't call the dql */
            $LatestJobs = "";
        }



        //for pagenation
        $tasksCount = $em->getRepository('ObjectsInternJumpBundle:Task')->countTasks($uid, "user", $status);

        $lastPageNumber = (int) ($tasksCount / $itemsPerPage);
        if (($tasksCount % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }


        $userJob = $em->getRepository('ObjectsInternJumpBundle:UserInternship')->findOneBy(array('user' => $uid, 'status' => "accepted"));

        //get User score
        $score = $user->getScore();
        //get quizResult Repo
        $quizResultRepo = $em->getRepository('ObjectsInternJumpBundle:QuizResult');

        //find all quiz results
        $quizResults = $quizResultRepo->findAll();
        $resultObject = null;
        if ($score) {
            foreach ($quizResults as $result) {
                if ($result->getScore() >= $score) {
                    $resultObject = $result;
                    break;
                }
                $resultObject = $result;
            }
        }

        //get interviews Repo
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');
        //get upcoming Interviews
        $upComingInterviews = $interviewRepo->getUpComingInterviews($uid);
//        print_r($upComingInterviews);exit;
        $upComingInterviewsCount = sizeof($upComingInterviews);

        //get jobs that user applied
        $appliedJobs = $em->getRepository('ObjectsInternJumpBundle:UserInternship')->getAppliedJobs($uid);


        //get latest 3 notifications
        $latestNotifications = $em->getRepository('ObjectsInternJumpBundle:UserNotification')->getLatestThree($uid);
        //get latest 3 messages
        $latestMessages = $em->getRepository('ObjectsInternJumpBundle:Message')->getLatestThree($uid);


        //For search form
        //all cities
        $allCities = $cityRepo->findBy(array('country' => 'US'), array('name' => 'asc'));
        //all category
        $allCategory = $categoryRepo->findBy(array(), array('name' => 'asc'));

        //Get the user's worth result
        $worth = $user->getNetWorth();

        //get favorite companies
        $favCompanies = $user->getFavoriteComapnies();

        return $this->render('ObjectsInternJumpBundle:Task:fb_studentTasks.html.twig', array(
                    'entities' => $tasks,
                    'user' => $user,
                    'userjob' => $userJob,
                    'status' => $status,
                    'flag' => $flag,
                    'cvCategoris' => $categ,
                    'latestJobs' => $LatestJobs,
                    'page' => $page,
                    'tasksPerPage' => $itemsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'quizResult' => $resultObject,
                    'cvCount' => $numOfCVs,
                    'interviews' => $upComingInterviews,
                    'interviewsCount' => $upComingInterviewsCount,
                    'appliedJobs' => $appliedJobs,
                    'latestNotifications' => $latestNotifications,
                    'latestMessages' => $latestMessages,
                    'allCities' => $allCities,
                    'allCategory' => $allCategory,
                    'worth' => $worth,
                    'favCompanies' => $favCompanies
        ));
    }

    /**
     * Lists Company's all Task entities.
     *
     */
    public function companyAllTasksAction($tasksPerPage, $status, $page) {

        if (false === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            if (false === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }


        $em = $this->getDoctrine()->getEntityManager();


        //Get current Company user
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }
        $cid = $company->getId();

        //check if we do not have the items per page number
        if (!$tasksPerPage) {
            //get the items per page from cookie or the default value
            $tasksPerPage = $this->getRequest()->cookies->get('tasks_per_show_page_' . $cid, 3);
        }

        $entities = $em->getRepository('ObjectsInternJumpBundle:Task')->getCompanyAllTasks($cid, $page, $tasksPerPage, $status);

        $companyUsers = $em->getRepository('ObjectsInternJumpBundle:Internship')->getAllCompanyUsers($cid);

        //for pagenation
        $tasksCount = $em->getRepository('ObjectsInternJumpBundle:Task')->countTasks($cid, "company", $status);

        $lastPageNumber = (int) ($tasksCount / $tasksPerPage);
        if (($tasksCount % $tasksPerPage) > 0) {
            $lastPageNumber++;
        }
        //print_r($companyUsers);
        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        return $this->render('ObjectsInternJumpBundle:Task:companyTasks.html.twig', array(
                    'entities' => $entities,
                    'companyUsers' => $companyUsers,
                    'flag' => $flag,
                    'page' => $page,
                    'tasksPerPage' => $tasksPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'status' => $status,
        ));
    }

    /**
     * Lists  Company user's all Task entities.
     *
     */
    public function companyUserAllTasksAction($userName, $itemsPerPage, $status, $page) {

        if (false === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            if (false === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }

        $em = $this->getDoctrine()->getEntityManager();
        //$tasksPerPage = $this->container->getParameter('tasks_per_show_page'); //for pagenation
        //$entities = $em->getRepository('ObjectsInternJumpBundle:Task')->findAll();
        //Get current Company user
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }
        $cid = $company->getId();

        //check if we do not have the items per page number
        if (!$itemsPerPage) {
            //get the items per page from cookie or the default value
            $itemsPerPage = $this->getRequest()->cookies->get('tasks_per_comp_show_page_' . $cid, 3);
        }

        //$entities = $em->getRepository('ObjectsInternJumpBundle:Task')->getCompanyAllTasks($cid);
        $entities = $em->getRepository('ObjectsInternJumpBundle:Task')->getCompanyUserAllTasks($cid, $userName, $status, $page, $itemsPerPage);
        $user = $em->getRepository('ObjectsUserBundle:User')->findOneBy(array('loginName' => $userName));

        //get usere's Hiring Date
        $hireDate = $em->getRepository('ObjectsInternJumpBundle:UserInternship')->findOneBy(array('user' => $user));

        //for pagenation
        $tasksCount = $em->getRepository('ObjectsInternJumpBundle:Task')->countcuTasks($cid, $user->getId(), $status);

        //get user's Total number of tasks
        $numberOfUserTasks = $em->getRepository('ObjectsInternJumpBundle:Task')->countTasksStandard($user->getId());

        $lastPageNumber = (int) ($tasksCount / $itemsPerPage);
        if (($tasksCount % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }


        return $this->render('ObjectsInternJumpBundle:Task:companyUserTasks.html.twig', array(
                    'entities' => $entities,
                    'user' => $user,
                    'page' => $page,
                    'tasksPerPage' => $itemsPerPage,
                    'lastPageNumber' => $lastPageNumber,
                    'company' => $company,
                    'numOfTasks' => $numberOfUserTasks,
                    'status' => $status,
                    'hireDate' => $hireDate,
        ));
    }

    /**
     * Finds and displays a Student Task
     *
     */
    public function studentShowAction($id) {


        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $task = $em->getRepository('ObjectsInternJumpBundle:Task')->find($id);

        //If no Task Found
        if (!$task) {
            $message = $this->container->getParameter('task_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //If Task Found then check if the Current User is the Task Owner
        //Get current user
        $loggedinUser = $this->get('security.context')->getToken()->getUser();
        //Get Task Owner
        $taskUser = $task->getUser();
        //check if the same user
        if ($taskUser->getId() != $loggedinUser->getId()) {
            //if not equal redirect to Home Page
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        //Get task notes
        $notes = $task->getNotes();

        return $this->render('ObjectsInternJumpBundle:Task:studentShowTask.html.twig', array(
                    'entity' => $task,
                    'user' => $taskUser,
                    'notes' => $notes,
        ));

        //if not equal redirect to Home Page

        return $this->redirect($this->generateUrl('site_homepage'));
    }

    /**
     * Finds and displays a Student Task Facebook Version
     *
     */
    public function fb_studentShowAction($id) {


        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $task = $em->getRepository('ObjectsInternJumpBundle:Task')->find($id);

        //If no Task Found
        if (!$task) {
            $message = $this->container->getParameter('task_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }

        //If Task Found then check if the Current User is the Task Owner
        //Get current user
        $loggedinUser = $this->get('security.context')->getToken()->getUser();
        //Get Task Owner
        $taskUser = $task->getUser();
        //check if the same user
        if ($taskUser->getId() != $loggedinUser->getId()) {
            //if not equal redirect to Home Page
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        //Get task notes
        $notes = $task->getNotes();

        return $this->render('ObjectsInternJumpBundle:Task:fb_studentShowTask.html.twig', array(
                    'entity' => $task,
                    'user' => $taskUser,
                    'notes' => $notes,
        ));

        //if not equal redirect to Home Page

        return $this->redirect($this->generateUrl('site_fb_homepage'));
    }

    /**
     * Finds and displays a Company Task
     *
     */
    public function companyShowAction($id) {
        if (false === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            if (false === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }

        $em = $this->getDoctrine()->getEntityManager();
        $task = $em->getRepository('ObjectsInternJumpBundle:Task')->getTaskById($id);
        if (!$task) {
            $message = $this->container->getParameter('task_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //If Task Found then check if the Current User is the Task Owner
        //Get current user
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $loggedinCompany = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $loggedinCompany = $manager->getCompany();
        }

        //Get Task Owner company
        $taskCompany = $task->getCompany();
        //check if the same user
        if ($taskCompany->getId() != $loggedinCompany->getId()) {
            //if not equal redirect to Home Page
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        $deleteForm = $this->createDeleteForm($id);

        //get all this task notes between this company and users
        $notes = $task->getNotes();

        //get all company employeers to show in side bar in accordion
        $companyUsers = $em->getRepository('ObjectsInternJumpBundle:Internship')->getAllCompanyUsers($loggedinCompany->getId());

        //get task student
        $taskUser = $task->getUser();

        //get usere's Hiring Date
        $hireDate = $em->getRepository('ObjectsInternJumpBundle:UserInternship')->findOneBy(array('user' => $taskUser));

        //get user's Total number of tasks
        $numberOfUserTasks = $em->getRepository('ObjectsInternJumpBundle:Task')->countTasksStandard($taskUser->getId());

        return $this->render('ObjectsInternJumpBundle:Task:companyShowTask.html.twig', array(
                    'entity' => $task,
                    'notes' => $notes,
                    'delete_form' => $deleteForm->createView(),
                    'companyUsers' => $companyUsers,
                    'user' => $taskUser,
                    'hireDate' => $hireDate,
                    'numOfTasks' => $numberOfUserTasks,
        ));
    }

    /**
     * Displays a form to create a new Task entity.
     *
     */
    public function newAction($uid) {

        if (!$uid)
            $uid = -1;
        $em = $this->getDoctrine()->getEntityManager();

        if (false === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            if (false === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }


        //Get current Company user
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }


        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');

        $companyHiredUsers = array();
        $hiredUsersJobsArray = array();
        //get compaqny hired users
        $companyJobsIds = $internshipRepo->getCompanyJobsIds($company->getId());
        $companyJobsIdsArray = array();
        foreach ($companyJobsIds as $job) {
            $companyJobsIdsArray [] = $job['id'];
        }

        if (sizeof($companyJobsIdsArray) > 0) {
            $companyHiredUsers = $internshipRepo->getCompanyHiredUsers($companyJobsIdsArray);
        }

        $companyUsers = array();
        $hiredUsersJobsArray = array();

        if (sizeof($companyHiredUsers) > 0) {
            $hiredUsersIdsArray = array();
            $hiredUsers = array();
            foreach ($companyHiredUsers as $companyHiredUser) {
                if (!in_array($companyHiredUser->getInternship()->getTitle(), $hiredUsersJobsArray)) {
                    array_push($hiredUsersJobsArray, $companyHiredUser->getInternship()->getTitle());
                }
                if (!in_array($companyHiredUser->getUser()->getId(), $hiredUsersIdsArray)) {
                    array_push($hiredUsersIdsArray, $companyHiredUser->getUser()->getId());
                    $companyUsers[] = $companyHiredUser->getUser();
                }
            }
        }

        $cid = $company->getId();
        $request = $this->getRequest();

        //get the company jobs
        $jobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getAllCompanyJobs($cid);

        $companyJobs = array();
        foreach ($jobs as $job) {
            $companyJobs[$job->getId()] = $job->getTitle();
        }


        $task = new Task();
        $task->setEndedAt(new \DateTime());
        $task->setStartedAt(new \DateTime());
        //$form   = $this->createForm(new TaskType(), $entity);
        $formValidationGroups [] = 'new';
        $form = $this->createFormBuilder($task, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('title')
                ->add('description')
                ->add('status', 'choice', array('attr' => array('style' => 'width:120px;'), 'choices' => array('new' => 'new', 'inprogress' => 'inprogress', 'done' => 'done')))
                ->add('startedAt', 'date', array('attr' => array('class' => 'startedAt'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd H:mm'))
                ->add('endedAt', 'date', array('attr' => array('class' => 'endedAt'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd H:mm'))
                //->add('internship')
                ->add('internship', 'entity', array('attr' => array('style' => 'width:120px;'), 'class' => 'ObjectsInternJumpBundle:Internship', 'choices' => $jobs))
                ->add('user', 'entity', array('attr' => array('style' => 'width:120px;'), 'class' => 'ObjectsUserBundle:User', 'choices' => $companyUsers, 'preferred_choices' => array($uid)))
//                ->add('user', 'choice', array('attr' => array('style' => 'width:120px;'), 'choices' => $hiredUsers, 'preferred_choices' => array($uid)))
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $task->setCompany($company);
                $em->persist($task);
                $em->flush();

                //set the success flag in the session
                $this->getRequest()->getSession()->setFlash('success', $this->container->getParameter('new_task_success_message'));


                //*******Notification part*******//
                //Get Task's user
                $user = $task->getUser();
                //add user notification
                $userNotification = new \Objects\InternJumpBundle\Entity\UserNotification();
                $userNotification->setCompany($company);
                $userNotification->setUser($user);
                $userNotification->setType('company_assign_task');
                $userNotification->setTypeId($task->getId());
                $em->persist($userNotification);
                $em->flush();

                //send email to user
                InternjumpController::userNotificationMail($this->container, $user, $company, 'company_assign_task', $task->getId());

                return $this->redirect($this->generateUrl('company_task_show', array('id' => $task->getId())));
            }
        }

        return $this->render('ObjectsInternJumpBundle:Task:new.html.twig', array(
                    'entity' => $task,
                    'form' => $form->createView(),
                    'companyHiredUsers' => $companyHiredUsers,
                    'hiredUsersJobsArray' => $hiredUsersJobsArray,
                    'formName' => $this->container->getParameter('companyAddTask_FormName'),
                    'formName1' => $this->container->getParameter('companyAddTask_FormName1'),
                    'formDesc' => $this->container->getParameter('companyAddTask_FormDesc'),
                    'userId' => $uid,
        ));
    }

    /**
     * @author Ola edited by Ahmed
     * to get users for a company's certain job
     * @param int $id //job Id
     * return array of users
     */
    public function getJobUsersAction($id) {
        $em = $this->getDoctrine()->getEntityManager();
        $jobs = $em->getRepository('ObjectsInternJumpBundle:UserInternship')->getAllJobsUsers($id);
        $request = $this->getRequest();

        if (!$jobs || !$request->isXmlHttpRequest()) {
            return new Response("faild");
        }
        $usersArray = array();
        foreach ($jobs as $job) {
            $usersArray [$job->getUser()->getId()] = $job->getUser()->__toString();
        }

        return new Response(json_encode($usersArray));
    }

    /**
     * @author Ola
     * to get Jobs for a company's certain user
     * @param int $id //user Id
     * return array of jobs
     */
    public function getUserJobsAction($uid) {
        $em = $this->getDoctrine()->getEntityManager();

        $userRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');
        //Get current Company user
        $company = $this->get('security.context')->getToken()->getUser();
        $cid = $company->getId();

        $userJobs = $userRepo->getAllUserJobs($uid, $cid);
        $request = $this->getRequest();
        if (!$userJobs || !$request->isXmlHttpRequest()) {
            return new Response("faild");
        }
        $jobs = array();
        foreach ($userJobs as $job) {
            $jobs[] = $job->getInternship();
        }
        $jobsArray = array();
        foreach ($jobs as $job) {
            $jobsArray [$job->getId()] = $job->getTitle();
        }

        return new Response(json_encode($jobsArray));
    }

    /**
     * -[[ Student ]]- Displays a form to edit an existing Task entity and perform edits.
     *
     */
    public function studentEditAction($id) {
        //****Check if inside facebook or Not****//
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $task = $em->getRepository('ObjectsInternJumpBundle:Task')->find($id);

        if (!$task) {
            $message = $this->container->getParameter('task_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //If Task Found then check if the Current User is the Task Owner
        //Get current user
        $loggedinUser = $this->get('security.context')->getToken()->getUser();
        //Get Task Owner
        $taskUser = $task->getUser();
        //check if the same user
        if ($taskUser->getId() != $loggedinUser->getId()) {
            //if not equal redirect to Home Page
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        $editForm = $this->createFormBuilder($task)
                ->add('status', 'choice', array('choices' => array('new' => 'new', 'inprogress' => 'inprogress', 'done' => 'done')))
                ->getForm();

        if ($request->getMethod() == 'POST') {

            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                $em->persist($task);
                $em->flush();

                //*******Notification part*******//
                //Get Task's Company
                $company = $task->getCompany();
                //add company notification
                $companyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
                $companyNotification->setCompany($company);
                $companyNotification->setUser($taskUser);
                $companyNotification->setType('user_edit_task');
                $companyNotification->setTypeId($task->getId());
                $em->persist($companyNotification);
                $em->flush();

                //send email to company
                InternjumpController::companyNotificationMail($this->container, $taskUser, $company, 'user_edit_task', $task->getId());

                if ($flag == "facebook") {
                    return $this->redirect($this->generateUrl('student_task_edit', array('id' => $id)));
                }
            }
        }

        return $this->render('ObjectsInternJumpBundle:Task:studentEditTask.html.twig', array(
                    'entity' => $task,
                    'edit_form' => $editForm->createView(),
                    'flag' => $flag,
                    'user' => $taskUser,
        ));
    }

    /**
     * -[[ Company ]]- Action displays a form to edit an existing Task entity and perform edits.
     *
     */
    public function companyEditAction($id) {

        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        if (false === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $task = $em->getRepository('ObjectsInternJumpBundle:Task')->find($id);

        if (!$task) {
            $message = $this->container->getParameter('task_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }


        //If Task Found then check if the Current Company is the Task Owner
        //Get current company
        $loggedinCompany = $this->get('security.context')->getToken()->getUser();
        //Get Task Owner
        $taskCompany = $task->getCompany();

        //get the company jobs
        $jobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getAllCompanyJobs($taskCompany->getId());

        //check if the same company
        if ($taskCompany->getId() != $loggedinCompany->getId()) {
            //if not equal redirect to Home Page
            return $this->redirect($this->generateUrl('site_homepage'));
        }

        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');

        $companyHiredUsers = array();
        $hiredUsersJobsArray = array();
        //get compaqny hired users
        $companyJobsIds = $internshipRepo->getCompanyJobsIds($taskCompany->getId());
        $companyJobsIdsArray = array();
        foreach ($companyJobsIds as $job) {
            $companyJobsIdsArray [] = $job['id'];
        }

        if (sizeof($companyJobsIdsArray) > 0) {
            $companyHiredUsers = $internshipRepo->getCompanyHiredUsers($companyJobsIdsArray);
        }

        if (sizeof($companyHiredUsers) > 0) {
            $hiredUsersJobsArray = array();
            foreach ($companyHiredUsers as $companyHiredUser) {
                if (!in_array($companyHiredUser->getInternship()->getTitle(), $hiredUsersJobsArray)) {
                    array_push($hiredUsersJobsArray, $companyHiredUser->getInternship()->getTitle());
                }
            }
        }


        $formValidationGroups [] = 'new';
        $editForm = $this->createFormBuilder($task, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('title')
                ->add('description')
                ->add('status', 'choice', array('attr' => array('style' => 'width:120px;'), 'choices' => array('new' => 'new', 'inprogress' => 'inprogress', 'done' => 'done')))
                ->add('startedAt', 'date', array('attr' => array('class' => 'startedAt'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd H:mm'))
                ->add('endedAt', 'date', array('attr' => array('class' => 'endedAt'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd H:mm'))
                //->add('internship')
                ->add('internship', 'entity', array('class' => 'ObjectsInternJumpBundle:Internship', 'choices' => $jobs))
                ->add('user', 'entity', array('attr' => array('style' => 'width:120px;'), 'class' => 'ObjectsUserBundle:User', 'choices' => array(
            )))
                ->getForm();
//        $editForm = $this->createForm(new TaskType(), $task);
        $deleteForm = $this->createDeleteForm($id);

        if ($request->getMethod() == 'POST') {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                $em->persist($task);
                $em->flush();

                //*******Notification part*******//
                //Get Task's User
                $user = $task->getUser();
                //add user notification
                $userNotification = new \Objects\InternJumpBundle\Entity\UserNotification();
                $userNotification->setCompany($taskCompany);
                $userNotification->setUser($user);
                $userNotification->setType('company_edit_task');
                $userNotification->setTypeId($task->getId());
                $em->persist($userNotification);
                $em->flush();

                //send email to user
                InternjumpController::userNotificationMail($this->container, $user, $taskCompany, 'company_edit_task', $task->getId());

                return $this->redirect($this->generateUrl('company_task_edit', array('id' => $id)));
            }
        }

        return $this->render('ObjectsInternJumpBundle:Task:companyEditTask.html.twig', array(
                    'entity' => $task,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'flag' => $flag,
                    'companyHiredUsers' => $companyHiredUsers,
                    'hiredUsersJobsArray' => $hiredUsersJobsArray,
                    'formName' => $this->container->getParameter('companyEditTask_FormName'),
                    'formName1' => $this->container->getParameter('companyEditTask_FormName1'),
                    'formDesc' => $this->container->getParameter('companyEditTask_FormDesc'),
        ));
    }

    /**
     * Deletes a Task entity.
     *
     */
    public function deleteAction($id) {
        //Check if inside facebook or Not
        $url = $this->getRequest()->get('access_method');
        $flag = $this->checkWhere($url);

        if (false === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            if (false === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                return $this->redirect($this->generateUrl('site_homepage'));
            }
        }

        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $task = $em->getRepository('ObjectsInternJumpBundle:Task')->find($id);

            $taskUser = $task->getUser();
            if (!$task) {
                $message = $this->container->getParameter('task_not_found_error_msg');
                return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                            'message' => $message,));
            }

            //If Task Found then check if the Current Company is the Task Owner
            //Get current Company
            if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
                $loggedinCompany = $this->get('security.context')->getToken()->getUser();
            } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                $manager = $this->get('security.context')->getToken()->getUser();
                $loggedinCompany = $manager->getCompany();
            }
            //Get Task Owner
            $taskCompany = $task->getCompany();
            //check if the same company
            if ($taskCompany->getId() != $loggedinCompany->getId()) {
                //if not equal redirect to Home Page
                return $this->redirect($this->generateUrl('site_homepage'));
            }

            $em->remove($task);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('company_user_task', array('userName' => $taskUser->getLoginName())));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

    /**
     * Function that takes the request url and check for a parameter if it is set with value or not
     * To check if inside facebook or not
     * @author Ola
     * @param url $url
     * @return string
     */
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

    /**
     * Add Note Action company and user can add note on a certain task
     * @author Ola
     * @param type $text
     * @param type $taskId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addNoteAction($text, $taskId) {
        $em = $this->getDoctrine()->getEntityManager();
        $task = $em->getRepository('ObjectsInternJumpBundle:Task')->find($taskId);
        $request = $request = $this->getRequest();

        //just a boolean to define if the one adding the note if [user =0] or [company =1]
        $type = 0;

        if (!$task || !$request->isXmlHttpRequest()) {
            return new Response("faild");
        }
        if (true === $this->get('security.context')->isGranted('ROLE_COMPANY') || true === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            //it is a company
            $type = 1;
            //Check if the current company is the task owner
            if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
                $loggedinCompany = $this->get('security.context')->getToken()->getUser();
            } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                $manager = $this->get('security.context')->getToken()->getUser();
                $loggedinCompany = $manager->getCompany();
            }

            $taskCompany = $task->getCompany();
            if ($taskCompany->getId() != $loggedinCompany->getId()) {
                //if not response faild
                return new Response("faild");
            }
            $taskUser = $task->getUser();
        } elseif (true === $this->get('security.context')->isGranted('ROLE_USER')) {
            //it is a user
            $type = 0;
            //Check if the current User is the task owner
            $loggedinUser = $this->get('security.context')->getToken()->getUser();
            $taskUser = $task->getUser();
            if ($taskUser->getId() != $loggedinUser->getId()) {
                //if not response faild
                return new Response("faild");
            }
            $taskCompany = $task->getCompany();
        }
        //Not loggedIn Or other Un-auth Roles
        else {
            return new Response("faild");
        }

        //**** Passed all the checks and Continue creating the Note ****//
        $note = new \Objects\InternJumpBundle\Entity\Note();
        $note->setNote($text);
        $note->setTask($task);
        $note->setType($type);
        $em->persist($note);
        $em->flush();

        //*******Notification part*******//
        //add notification
        //if Company
        if ($type == 1) {
            $notification = new \Objects\InternJumpBundle\Entity\UserNotification();
            $notification->setType('company_add_note');
            //send email to user
            InternjumpController::userNotificationMail($this->container, $taskUser, $taskCompany, 'company_add_note', $task->getId());
        }
        //if User
        elseif ($type == 0) {
            $notification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $notification->setType('user_add_note');
            //send email to company
            InternjumpController::companyNotificationMail($this->container, $taskUser, $taskCompany, 'user_add_note', $task->getId());
        }

        $notification->setCompany($taskCompany);
        $notification->setUser($taskUser);


        $notification->setTypeId($task->getId());
        $em->persist($notification);
        $em->flush();

        return $this->render('ObjectsInternJumpBundle:Task:note.html.twig', array(
                    'note' => $note,
                    'entity' => $task,
        ));
    }

    /**
     * Edit Note Action company and user can add note on a certain task.
     * @author Ola
     * @param text $text
     * @param int $taskId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editNoteAction($text, $taskId, $noteId) {
        $em = $this->getDoctrine()->getEntityManager();
        $task = $em->getRepository('ObjectsInternJumpBundle:Task')->find($taskId);
        $note = $em->getRepository('ObjectsInternJumpBundle:Note')->find($noteId);
        $request = $request = $this->getRequest();

        //just a boolean to define if the one adding the note if [user =0] or [company =1]
        $type = 0;


        if (!$task || !$note || !$request->isXmlHttpRequest()) {
            return new Response("faild");
        }
        if (true === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //it is a company
            $type = 1;
            //Check if the current company is the task owner
            $loggedinCompany = $this->get('security.context')->getToken()->getUser();
            $taskCompany = $task->getCompany();
            if ($taskCompany->getId() != $loggedinCompany->getId()) {
                //if not response faild
                return new Response("faild");
            }
            $taskUser = $task->getUser();
        } elseif (true === $this->get('security.context')->isGranted('ROLE_USER')) {
            //it is a user
            $type = 0;
            //Check if the current User is the task owner
            $loggedinUser = $this->get('security.context')->getToken()->getUser();
            $taskUser = $task->getUser();
            if ($taskUser->getId() != $loggedinUser->getId()) {
                //if not response faild
                return new Response("faild");
            }
            $taskCompany = $task->getCompany();
        }
        //Not loggedIn Or other Un-auth Roles
        else {
            return new Response("faild");
        }

        //**** Passed all the checks and Continue editing the Note ****//
        $note->setNote($text);
        $em->flush();

        //*******Notification part*******//
        //add notification
        //if Company
        if ($type == 1) {
            $notification = new \Objects\InternJumpBundle\Entity\UserNotification();
            $notification->setType('company_edit_note');
            //send email to user
            InternjumpController::userNotificationMail($this->container, $taskUser, $taskCompany, 'company_edit_note', $task->getId());
        }
        //if User
        elseif ($type == 0) {
            $notification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $notification->setType('user_edit_note');
            //send email to user
            InternjumpController::companyNotificationMail($this->container, $taskUser, $taskCompany, 'user_edit_note', $task->getId());
        }

        $notification->setCompany($taskCompany);
        $notification->setUser($taskUser);


        $notification->setTypeId($task->getId());
        $em->persist($notification);
        $em->flush();

        return new Response("Done");
    }

    /**
     * that Action for ajax request changing task status
     * @author Ola
     * @param string $status
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeTaskStatusAction($taskId, $status) {
        $em = $this->getDoctrine()->getEntityManager();
        $task = $em->getRepository('ObjectsInternJumpBundle:Task')->find($taskId);
        $request = $request = $this->getRequest();
        $type = 0;
        //task not found or NOT AJAX Request
        if (!$task || !$request->isXmlHttpRequest()) {
            return new Response("faild");
        }

        if (true === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //it is a company
            $type = 1;
            //Check if the current company is the task owner
            $loggedinCompany = $this->get('security.context')->getToken()->getUser();
            $taskCompany = $task->getCompany();
            if ($taskCompany->getId() != $loggedinCompany->getId()) {
                //if not response faild
                return new Response("faild");
            }
            $taskUser = $task->getUser();
        } elseif (true === $this->get('security.context')->isGranted('ROLE_USER')) {
            //it is a user
            $type = 0;
            //Check if the current User is the task owner
            $loggedinUser = $this->get('security.context')->getToken()->getUser();
            $taskUser = $task->getUser();
            if ($taskUser->getId() != $loggedinUser->getId()) {
                //if not response faild
                return new Response("faild");
            }
            $taskCompany = $task->getCompany();
        }
        //not logged in
        else {
            return new Response("faild");
        }

        //**** Passed all the checks and Continue editing task's status ****//
        $task->setStatus($status);
        $em->flush();
        //*******Notification part*******//
        //add notification
        //if Company
        if ($type == 1) {
            $notification = new \Objects\InternJumpBundle\Entity\UserNotification();
            $notification->setType('company_edit_task');
            //send email to user
            InternjumpController::userNotificationMail($this->container, $taskUser, $taskCompany, 'company_edit_task', $task->getId());
        }
        //if User
        elseif ($type == 0) {
            $notification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $notification->setType('user_edit_task');
            //send email to company
            InternjumpController::companyNotificationMail($this->container, $taskUser, $taskCompany, 'user_edit_task', $task->getId());
        }

        $notification->setCompany($taskCompany);
        $notification->setUser($taskUser);


        $notification->setTypeId($task->getId());
        $em->persist($notification);
        $em->flush();



        return new Response('Done');
    }

    /**
     * This action is to check and calculate values to measure how much user completed his CV
     * @author Ola
     */
    public function userMeterAction() {
//        if (true === $this->get('security.context')->isGranted('ROLE_USER')) {
        //total percent of CV Completion
        $percent = 1;
        //flags distinguishing between if entity/feild found or not
        $flag1 = ""; //to check if user created at least/activate one cv
        $flag2 = ""; //to check if user added eduacation or not
        $flag3 = ""; //to check if user added employment history or not
        $flag4 = ""; //to check if user added at least one skill or not
        $flag5 = ""; //to check if user answered the "know you" quiz
        $flag6 = ""; //to check if user answered ALL Personal Questions
        $uDataFlag_fn = ""; //to check if user data contains firstname
        $uDataFlag_ln = ""; //to check if user data contains lastname
        $uDataFlag_ab = ""; //to check if user data contains about
        $uDataFlag_dob = ""; //to check if user data contains date of birth
        $uDataFlag_url = ""; //to check if user data contains url
        $cvDescFlag = ""; // to check if each cv contains describe yourself or not
        $allSocialLinksFlag = ""; // to check if has social or not at all
        $fbLinkFlag = ""; // to check if user linked his profile to facebook
        $twitterLinkFlag = ""; // to check if user linked his profile to twitter
        $linkedLinkFlag = ""; // to check if user linked his profile to LinkedIn

        $em = $this->getDoctrine()->getEntityManager();

        //get current user
        $user = $this->get('security.context')->getToken()->getUser();
        $uId = $user->getId();

        //Get Repos
        $eduacationRepo = $em->getRepository("ObjectsInternJumpBundle:Education");
        $experienceRepo = $em->getRepository("ObjectsInternJumpBundle:EmploymentHistory");
        $cvRepo = $em->getRepository("ObjectsInternJumpBundle:CV");
        $skillRepo = $em->getRepository("ObjectsInternJumpBundle:Skill");

        //Check if User has cv(s)
        $cvs = $cvRepo->getAllCvs($uId);
        $cvArray = array();
        if ($cvs) {
            $percent+=20;
            /*
              //Check if User added Describe yourself to his cv(s)
              foreach ($cvs as $cv) {
              //describe yourself not found in cv
              if (!$cv->getDescribeYourself()) {
              $cvDescFlag = "desc";
              $cvdesc['id'] = $cv->getId();
              $cvdesc['name'] = $cv->getName();
              $cvArray[] = $cvdesc;
              }
              }
              //in case all Cv(s) have describe yourself
              if (!isset($cvdesc)) {
              $percent+=10;
              $cvdesc[] = "";
              } */
        } else {
            $flag1 = "cvs";
        }


        //Check if User has education
        $edu = $eduacationRepo->getAllEducation($uId);
        if ($edu) {
            $percent+=10;
        } else {
            $flag2 = "edu";
        }

        //Check if User has Experience
        $exp = $experienceRepo->getAllExperince($uId);
        if ($exp) {
            $percent+=10;
        } else {
            $flag3 = "exp";
        }

        //Check if User has Skills
        $skill = $skillRepo->getStudentAllSkills($uId);
        if ($skill) {
            $percent+=10;
        } else {
            $flag4 = "skil";
        }

        //Check if User answered Know you Quiz
        if ($user->getScore() == null) {
            $flag5 = "quiz";
        } else {
            $percent+=10;
        }

        //Check if User Completed His personal data
        if ($user->getFirstName() == null) {
            $uDataFlag_fn = "fn";
        }
        if ($user->getLastName() == null) {
            $uDataFlag_ln = "ln";
        }
        if ($user->getAbout() == null) {
            $uDataFlag_ab = "ab";
        }
        if ($user->getDateOfBirth() == null) {
            $uDataFlag_dob = "dob";
        }
        if ($user->getUrl() == null) {
            $uDataFlag_url = "url";
        } else {
            $percent+=20;
        }

        /* Check if user answered personal Questions to add more 10 % */
        //get personal question Repo
        $personalQuestionsRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestion');
        //get count of questions
        $numberOfQuestions = sizeof($personalQuestionsRepo->findAll());
        //get PersonalQuestionAnswers Repo
        $personalQuestionsAnswerRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestionAnswer');
        //get count of answer by this user
        $numberOfUserQuestions = sizeof($personalQuestionsAnswerRepo->findBy(array('user' => $uId)));
        //if user answered
        if ($numberOfQuestions == $numberOfUserQuestions) {
            $percent+=10;
        } else {//if didn't answer
            $flag6 = "noAns";
        }

        /* Check if user has social links to add more 1% for fb, 1% for twitter, 1% for linked in */
        //get social accounts Repo
        $socialAccountsRepo = $em->getRepository('ObjectsUserBundle:SocialAccounts');
        //get user social account
        $userSocialAccount = $socialAccountsRepo->findOneBy(array('user' => $uId));
        if (isset($userSocialAccount)) {
            //found social Link
            //check, has facebook link?
            if ($userSocialAccount->isFacebookLinked()) {
                $percent+=3;
            } else {
                $fbLinkFlag = "notlinked";
            }
            //check, has twitter link?
            if ($userSocialAccount->isTwitterLinked()) {
                $percent+=3;
            } else {
                $twitterLinkFlag = "notlinked";
            }
            //check, has Linkedin link?
            if ($userSocialAccount->isLinkedInLinked()) {
                $percent+=3;
            } else {
                $linkedLinkFlag = "notlinked";
            }
        } else {
            $allSocialLinksFlag = "notlinked";
        }

        return $this->render('ObjectsInternJumpBundle:Task:meter.html.twig', array(
                    'user' => $user,
                    'percent' => $percent,
                    'cvsFlag' => $flag1,
                    'eduFlag' => $flag2,
                    'expFlag' => $flag3,
                    'skilFlag' => $flag4,
                    'quizFlag' => $flag5,
                    'personalQuestionFlag' => $flag6,
                    'fNameFlag' => $uDataFlag_fn,
                    'lNameFlag' => $uDataFlag_ln,
                    'aboutFlag' => $uDataFlag_ab,
                    'dobFlag' => $uDataFlag_dob,
                    'urlFlag' => $uDataFlag_url,
                    'cvDescFlag' => $cvDescFlag,
                    'cvNamesArray' => $cvArray,
                    'allSocial' => $allSocialLinksFlag,
                    'linked' => $linkedLinkFlag,
                    'facebook' => $fbLinkFlag,
                    'twitter' => $twitterLinkFlag,
        ));
//        }
    }

    /**
     * This action is to check and calculate values to measure how much user completed his CV
     * @author Ola
     */
    public function fb_userMeterAction() {
//        if (true === $this->get('security.context')->isGranted('ROLE_USER')) {
        //total percent of CV Completion
        $percent = 1;
        //flags distinguishing between if entity/feild found or not
        $flag1 = ""; //to check if user created at least/activate one cv
        $flag2 = ""; //to check if user added eduacation or not
        $flag3 = ""; //to check if user added employment history or not
        $flag4 = ""; //to check if user added at least one skill or not
        $flag5 = ""; //to check if user answered the "know you" quiz
        $flag6 = ""; //to check if user answered ALL Personal Questions
        $uDataFlag_fn = ""; //to check if user data contains firstname
        $uDataFlag_ln = ""; //to check if user data contains lastname
        $uDataFlag_ab = ""; //to check if user data contains about
        $uDataFlag_dob = ""; //to check if user data contains date of birth
        $uDataFlag_url = ""; //to check if user data contains url
        $cvDescFlag = ""; // to check if each cv contains describe yourself or not
        $allSocialLinksFlag = ""; // to check if has social or not at all
        $fbLinkFlag = ""; // to check if user linked his profile to facebook
        $twitterLinkFlag = ""; // to check if user linked his profile to twitter
        $linkedLinkFlag = ""; // to check if user linked his profile to LinkedIn

        $em = $this->getDoctrine()->getEntityManager();

        //get current user
        $user = $this->get('security.context')->getToken()->getUser();
        $uId = $user->getId();

        //Get Repos
        $eduacationRepo = $em->getRepository("ObjectsInternJumpBundle:Education");
        $experienceRepo = $em->getRepository("ObjectsInternJumpBundle:EmploymentHistory");
        $cvRepo = $em->getRepository("ObjectsInternJumpBundle:CV");
        $skillRepo = $em->getRepository("ObjectsInternJumpBundle:Skill");

        //Check if User has cv(s)
        $cvs = $cvRepo->getAllCvs($uId);
        $cvArray = array();
        if ($cvs) {
            $percent+=20;
            /*
              //Check if User added Describe yourself to his cv(s)
              foreach ($cvs as $cv) {
              //describe yourself not found in cv
              if (!$cv->getDescribeYourself()) {
              $cvDescFlag = "desc";
              $cvdesc['id'] = $cv->getId();
              $cvdesc['name'] = $cv->getName();
              $cvArray[] = $cvdesc;
              }
              }
              //in case all Cv(s) have describe yourself
              if (!isset($cvdesc)) {
              $percent+=10;
              $cvdesc[] = "";
              } */
        } else {
            $flag1 = "cvs";
        }


        //Check if User has education
        $edu = $eduacationRepo->getAllEducation($uId);
        if ($edu) {
            $percent+=10;
        } else {
            $flag2 = "edu";
        }

        //Check if User has Experience
        $exp = $experienceRepo->getAllExperince($uId);
        if ($exp) {
            $percent+=10;
        } else {
            $flag3 = "exp";
        }

        //Check if User has Skills
        $skill = $skillRepo->getStudentAllSkills($uId);
        if ($skill) {
            $percent+=10;
        } else {
            $flag4 = "skil";
        }

        //Check if User answered Know you Quiz
        if ($user->getScore() == null) {
            $flag5 = "quiz";
        } else {
            $percent+=10;
        }

        //Check if User Completed His personal data
        if ($user->getFirstName() == null) {
            $uDataFlag_fn = "fn";
        }
        if ($user->getLastName() == null) {
            $uDataFlag_ln = "ln";
        }
        if ($user->getAbout() == null) {
            $uDataFlag_ab = "ab";
        }
        if ($user->getDateOfBirth() == null) {
            $uDataFlag_dob = "dob";
        }
        if ($user->getUrl() == null) {
            $uDataFlag_url = "url";
        } else {
            $percent+=20;
        }

        /* Check if user answered personal Questions to add more 10 % */
        //get personal question Repo
        $personalQuestionsRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestion');
        //get count of questions
        $numberOfQuestions = sizeof($personalQuestionsRepo->findAll());
        //get PersonalQuestionAnswers Repo
        $personalQuestionsAnswerRepo = $em->getRepository('ObjectsInternJumpBundle:PersonalQuestionAnswer');
        //get count of answer by this user
        $numberOfUserQuestions = sizeof($personalQuestionsAnswerRepo->findBy(array('user' => $uId)));
        //if user answered
        if ($numberOfQuestions == $numberOfUserQuestions) {
            $percent+=10;
        } else {//if didn't answer
            $flag6 = "noAns";
        }

        /* Check if user has social links to add more 1% for fb, 1% for twitter, 1% for linked in */
        //get social accounts Repo
        $socialAccountsRepo = $em->getRepository('ObjectsUserBundle:SocialAccounts');
        //get user social account
        $userSocialAccount = $socialAccountsRepo->findOneBy(array('user' => $uId));
        if (isset($userSocialAccount)) {
            //found social Link
            //check, has facebook link?
            if ($userSocialAccount->isFacebookLinked()) {
                $percent+=3;
            } else {
                $fbLinkFlag = "notlinked";
            }
            //check, has twitter link?
            if ($userSocialAccount->isTwitterLinked()) {
                $percent+=3;
            } else {
                $twitterLinkFlag = "notlinked";
            }
            //check, has Linkedin link?
            if ($userSocialAccount->isLinkedInLinked()) {
                $percent+=3;
            } else {
                $linkedLinkFlag = "notlinked";
            }
        } else {
            $allSocialLinksFlag = "notlinked";
        }

        return $this->render('ObjectsInternJumpBundle:Task:fb_meter.html.twig', array(
                    'user' => $user,
                    'percent' => $percent,
                    'cvsFlag' => $flag1,
                    'eduFlag' => $flag2,
                    'expFlag' => $flag3,
                    'skilFlag' => $flag4,
                    'quizFlag' => $flag5,
                    'personalQuestionFlag' => $flag6,
                    'fNameFlag' => $uDataFlag_fn,
                    'lNameFlag' => $uDataFlag_ln,
                    'aboutFlag' => $uDataFlag_ab,
                    'dobFlag' => $uDataFlag_dob,
                    'urlFlag' => $uDataFlag_url,
                    'cvDescFlag' => $cvDescFlag,
                    'cvNamesArray' => $cvArray,
                    'allSocial' => $allSocialLinksFlag,
                    'linked' => $linkedLinkFlag,
                    'facebook' => $fbLinkFlag,
                    'twitter' => $twitterLinkFlag,
        ));
//        }
    }

}

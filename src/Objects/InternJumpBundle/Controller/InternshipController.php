<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Objects\InternJumpBundle\Entity\Internship;
use Objects\InternJumpBundle\Form\InternshipType;
use Symfony\Component\HttpFoundation\Response;
use Objects\InternJumpBundle\Entity\City;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Objects\InternJumpBundle\Entity\Skill;

/**
 * Internship controller.
 *
 */
class InternshipController extends Controller {

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
     * this function used to apply on indeed job
     * @author ahmed
     * @param type $jobkey
     */
    public function indeedJobApplyAction($jobkey) {
        //get the job details
        $jobDetails = $this->getIndeedJob($jobkey);
        if (!$jobDetails['jobtitle']) {
            $message = $this->container->getParameter('internship_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        return $this->render('ObjectsInternJumpBundle:Internship:indeedJobApply.html.twig', array(
                    'jobDetails' => $jobDetails
        ));
    }

    /**
     * show indeed job
     * @author ahmed
     * @param type $jobkey
     */
    public function showIndeedJobAction($jobkey) {
        $em = $this->getDoctrine()->getEntityManager();
        $cityRepo = $em->getRepository('ObjectsInternJumpBundle:City');
        $stateRepo = $em->getRepository('ObjectsInternJumpBundle:State');
        $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');

        //get the job details
        $jobDetails = $this->getIndeedJob($jobkey);
        if (!$jobDetails['jobtitle']) {
            $message = $this->container->getParameter('internship_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get latest jobs
        $LatestJobs = array();
        if (true === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user object
            $user = $this->get('security.context')->getToken()->getUser();

            $cvs = $em->getRepository('ObjectsInternJumpBundle:CV')->getAllCvs($user->getId());
            //print_r($cvs);
            $categ = array();
            if ($cvs) {//if student Has CVs
                //echo "found cvs <br>";
                foreach ($cvs as $cv) {
                    foreach ($cv->getCategories() as $cat)
                        $categ[] = $cat->getId();
                }
            }

            if (sizeof($categ) > 0) { //found array of categories
                $LatestJobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getLatestJobs($categ, 5);
            }
        } else {
            $LatestJobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getRecentJobs(5);
        }

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

        return $this->render('ObjectsInternJumpBundle:Internship:showIndeedJob.html.twig', array(
                    'LatestJobs' => $LatestJobs,
                    'jobDetails' => $jobDetails,
                    'rand_banner' => $rand_banner
        ));
    }

    /**
     * show indeed job
     * @author ahmed
     * @param type $jobkey
     */
    public function fb_showIndeedJobAction($jobkey) {
        $em = $this->getDoctrine()->getEntityManager();
        $cityRepo = $em->getRepository('ObjectsInternJumpBundle:City');
        $stateRepo = $em->getRepository('ObjectsInternJumpBundle:State');
        $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');

        //get the job details
        $jobDetails = $this->getIndeedJob($jobkey);
        if (!$jobDetails['jobtitle']) {
            $message = $this->container->getParameter('internship_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }

        //get latest jobs
        $LatestJobs = array();
        if (true === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user object
            $user = $this->get('security.context')->getToken()->getUser();

            $cvs = $em->getRepository('ObjectsInternJumpBundle:CV')->getAllCvs($user->getId());
            //print_r($cvs);
            if ($cvs) {//if student Has CVs
                //echo "found cvs <br>";
                foreach ($cvs as $cv) {
                    foreach ($cv->getCategories() as $cat)
                        $categ[] = $cat->getId();
                }
            }

            if (sizeof($categ) > 0) { //found array of categories
                $LatestJobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getLatestJobs($categ, 5);
            }
        } else {
            $LatestJobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getRecentJobs(5);
        }

        return $this->render('ObjectsInternJumpBundle:Internship:fb_showIndeedJob.html.twig', array(
                    'LatestJobs' => $LatestJobs,
                    'jobDetails' => $jobDetails
        ));
    }

    /**
     * this function will get position from google map by zipcode
     * @author Ahmed
     * @param int $zipcode
     */
    public function getPositionAction($zipcode) {
        $url = "http://maps.googleapis.com/maps/api/geocode/json?address=$zipcode&sensor=false";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $output = json_decode($output, true);
        if (isset($output['status']) && strtolower($output['status']) == 'ok') {
            return new Response($output['results']['0']['geometry']['location']['lat'] . '|' . $output['results']['0']['geometry']['location']['lng'] . '|' . $output['results']['0']['formatted_address']);
        } else {
            return new Response('faild');
        }
    }

    /**
     * this function will list all company jobs
     * @author Ahmed
     * @param string $loginName
     * @param int $page
     */
    public function indexAction($loginName, $page) {
        $em = $this->getDoctrine()->getEntityManager();
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $InterviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        //get company object
        $company = $companyRepo->findOneBy(array('loginName' => $loginName));


        if (!$company) {
            $message = $this->container->getParameter('company_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //the results per page number
        $itemsPerPage = $this->container->getParameter('jobs_per_page_index_jobs_page');

        //get company jobs
        //check if the owner company
        $comanyFavoriteusersArray = array();
        if (True === $this->get('security.context')->isGranted('ROLE_COMPANY') && $this->get('security.context')->getToken()->getUser()->getLoginName() == $loginName) {
            $companyJobs = $internshipRepo->getCompanyJobs($company->getId(), $page, $itemsPerPage, TRUE);
            //get favorite users
            $comanyFavoriteusers = $company->getFavoriteUsers();
            //get intersted user cv id
            foreach ($comanyFavoriteusers as $comanyFavoriteuser) {
                $comanyFavoriteuserArray = array();
                $comanyFavoriteuserArray['user'] = $comanyFavoriteuser;
                $interestObject = $interestRepo->findOneBy(array('accepted' => 'accepted', 'company' => $company->getId(), 'user' => $comanyFavoriteuser->getId()));
                $comanyFavoriteuserArray['cvId'] = $interestObject->getCvId();
                $comanyFavoriteusersArray [] = $comanyFavoriteuserArray;
            }
        } else {
            $companyJobs = $internshipRepo->getCompanyJobs($company->getId(), $page, $itemsPerPage, FALSE);
        }

        //all questionsCount
        //check if the owner company
        $companyHiredUsers = array();
        $hiredUsersJobsArray = array();
        $companyInterests = array();
        $companyInterviews = array();
        $ownerCompanyFlag = 0;

        if (True === $this->get('security.context')->isGranted('ROLE_COMPANY') && $this->get('security.context')->getToken()->getUser()->getLoginName() == $loginName) {
            $ownerCompanyFlag = 1;
            $allcompanyJobsCount = $internshipRepo->countCompanyJobs($company->getId(), TRUE);
            //get compaqny hired users
            $companyJobsIds = $internshipRepo->getCompanyJobsIds($company->getId());
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

            //get company interests requests
            $companyInterests = $interestRepo->findBy(array('company' => $company->getId()), array('createdAt' => 'desc'));

            //get company interviews
            $companyInterviews = $InterviewRepo->getCompanyInterviews($company->getId());
        } else {
            $allcompanyJobsCount = $internshipRepo->countCompanyJobs($company->getId(), FALSE);
        }
        $allcompanyJobsCount = $allcompanyJobsCount['0']['jobsCount'];

        //calculate the last page number
        $lastPageNumber = (int) ($allcompanyJobsCount / $itemsPerPage);
        if (($allcompanyJobsCount % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }


        //check for loggedin user if add company to fav
        $userCompanyFavoriteFlag = FALSE;
        if (TRUE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user objects
            $user = $this->get('security.context')->getToken()->getUser();
            if ($user->getFavoriteComapnies()->contains($company)) {
                $userCompanyFavoriteFlag = 'yes';
            } else {
                $userCompanyFavoriteFlag = 'no';
            }
        }

        return $this->render('ObjectsInternJumpBundle:Internship:index.html.twig', array(
                    'companyJobs' => $companyJobs,
                    'page' => $page,
                    'lastPageNumber' => $lastPageNumber,
                    'loginName' => $loginName,
                    'company' => $company,
                    'hiredUsersJobsArray' => $hiredUsersJobsArray,
                    'companyHiredUsers' => $companyHiredUsers,
                    'companyInterests' => $companyInterests,
                    'companyInterviews' => $companyInterviews,
                    'ownerCompanyFlag' => $ownerCompanyFlag,
                    'comanyFavoriteusers' => $comanyFavoriteusersArray,
                    'userCompanyFavoriteFlag' => $userCompanyFavoriteFlag
        ));
    }

    /**
     * this function will list all company jobs
     * @author Ahmed
     * @param string $loginName
     * @param int $page
     */
    public function fb_indexAction($loginName, $page) {
        $em = $this->getDoctrine()->getEntityManager();
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');
        $InterviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');

        //get company object
        $company = $companyRepo->findOneBy(array('loginName' => $loginName));


        if (!$company) {
            $message = $this->container->getParameter('company_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }

        //the results per page number
        $itemsPerPage = $this->container->getParameter('jobs_per_page_index_jobs_page');

        //get company jobs
        //check if the owner company
        if (True === $this->get('security.context')->isGranted('ROLE_COMPANY') && $this->get('security.context')->getToken()->getUser()->getLoginName() == $loginName) {
            $companyJobs = $internshipRepo->getCompanyJobs($company->getId(), $page, $itemsPerPage, TRUE);
        } else {
            $companyJobs = $internshipRepo->getCompanyJobs($company->getId(), $page, $itemsPerPage, FALSE);
        }

        //all questionsCount
        //check if the owner company
        $companyHiredUsers = array();
        $hiredUsersJobsArray = array();
        $companyInterests = array();
        $companyInterviews = array();
        $ownerCompanyFlag = 0;

        if (True === $this->get('security.context')->isGranted('ROLE_COMPANY') && $this->get('security.context')->getToken()->getUser()->getLoginName() == $loginName) {
            $ownerCompanyFlag = 1;
            $allcompanyJobsCount = $internshipRepo->countCompanyJobs($company->getId(), TRUE);
            //get compaqny hired users
            $companyJobsIds = $internshipRepo->getCompanyJobsIds($company->getId());
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

            //get company interests requests
            $companyInterests = $interestRepo->findBy(array('company' => $company->getId()), array('createdAt' => 'desc'));

            //get company interviews
            $companyInterviews = $InterviewRepo->getCompanyInterviews($company->getId());
        } else {
            $allcompanyJobsCount = $internshipRepo->countCompanyJobs($company->getId(), FALSE);
        }
        $allcompanyJobsCount = $allcompanyJobsCount['0']['jobsCount'];

        //calculate the last page number
        $lastPageNumber = (int) ($allcompanyJobsCount / $itemsPerPage);
        if (($allcompanyJobsCount % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }



        //check for loggedin user if add company to fav
        $userCompanyFavoriteFlag = FALSE;
        if (TRUE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user objects
            $user = $this->get('security.context')->getToken()->getUser();
            if ($user->getFavoriteComapnies()->contains($company)) {
                $userCompanyFavoriteFlag = 'yes';
            } else {
                $userCompanyFavoriteFlag = 'no';
            }
        }

        return $this->render('ObjectsInternJumpBundle:Internship:fb_index.html.twig', array(
                    'companyJobs' => $companyJobs,
                    'page' => $page,
                    'lastPageNumber' => $lastPageNumber,
                    'loginName' => $loginName,
                    'company' => $company,
                    'hiredUsersJobsArray' => $hiredUsersJobsArray,
                    'companyHiredUsers' => $companyHiredUsers,
                    'companyInterests' => $companyInterests,
                    'companyInterviews' => $companyInterviews,
                    'ownerCompanyFlag' => $ownerCompanyFlag,
                    'userCompanyFavoriteFlag' => $userCompanyFavoriteFlag
        ));
    }

    /**
     * Finds and displays a Internship entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ObjectsInternJumpBundle:Internship')->find($id);
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        $cityRepo = $em->getRepository('ObjectsInternJumpBundle:City');
        $stateRepo = $em->getRepository('ObjectsInternJumpBundle:State');
        $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');

        $categ = array();
        if (!$entity) {
            $message = $this->container->getParameter('internship_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        } else {
            //check if not the owner company
            //so will check for job active or not
            if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY') || $this->get('security.context')->getToken()->getUser()->getId() != $entity->getCompany()->getId()) {
                //check for manager
                if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER') || $this->get('security.context')->getToken()->getUser()->getCompany()->getId() != $entity->getCompany()->getId()) {
                    $todayDate = new \DateTime('today');
                    if (!$entity->getActive() || $entity->getActiveTo() < $todayDate || $entity->getActiveFrom() > $todayDate) {
                        $message = $this->container->getParameter('internship_not_found_error_msg');
                        return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                                    'message' => $message,));
                    }
                }
            }
        }


        //get this job company
        $company = $entity->getCompany();

        //check if this job available
        $nowDate = date('Y-m-d');
        $jobAvailabilityFlag = 0;
        if (strtotime($nowDate) <= strtotime($entity->getActiveTo()->format('Y-m-d')) && strtotime($entity->getActiveFrom()->format('Y-m-d')) <= strtotime($nowDate)) {
            $jobAvailabilityFlag = 1;
        }

        //check if the logein user add this job before
        $addedBeforeFlag = 1;
        if (TRUE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user object
            $user = $this->get('security.context')->getToken()->getUser();
            $userInternshipObject = $userInternshipRepo->findOneBy(array('user' => $user->getId(), 'internship' => $id));
            if ($userInternshipObject) {
                $addedBeforeFlag = 0;
            }
        }

        //get applyed users
        $applyedUsers = array();
        $otherJobs = array();
        $interviewsUsers = array();
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //get applyed users
            $applyedUsers = $userInternshipRepo->getJobApplyedUsers($id);
            //get others company jobs
            $otherJobs = $internshipRepo->getOtherJobs($id, $company->getId());
            //get interviews user
            $interviewsUsers = $interviewRepo->findBy(array('company' => $company->getId(), 'internship' => $id), array('interviewDate' => 'desc'));
        }

        //get job categories
        $jobCategories = array();
        foreach ($entity->getCategories() as $category) {
            $jobCategories [] = $category->getId();
        }

        //get latest jobs
        $LatestJobs = array();
        if (true === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user object
            $user = $this->get('security.context')->getToken()->getUser();

            $cvs = $em->getRepository('ObjectsInternJumpBundle:CV')->getAllCvs($user->getId());
            //print_r($cvs);
            if ($cvs) {//if student Has CVs
                //echo "found cvs <br>";
                foreach ($cvs as $cv) {
                    foreach ($cv->getCategories() as $cat)
                        $categ[] = $cat->getId();
                }
            }

            if (sizeof($categ) > 0) { //found array of categories
                $LatestJobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getLatestJobs($categ, 5);
            }
        }

        //get related jobs
        $jobCatIds = $internshipRepo->getJobCatIds($id);
        $relatedJobs = array();
        if (sizeof($jobCatIds) > 0) {
            $relatedJobs = $internshipRepo->getRelatedJobs($id, $jobCatIds);
        }

        //get job skills
        $jobSkills = null;
        foreach ($entity->getSkills() as $skill) {
            $jobSkills .= '&skills-ids[]=' . $skill->getId();
        }

        //get job categories
        $jobCategroies = null;
        foreach ($entity->getCategories() as $category) {
            $jobCategroies .= '&selected-categories[]=' . $category->getId();
        }

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

        return $this->render('ObjectsInternJumpBundle:Internship:show.html.twig', array(
                    'entity' => $entity,
                    'company' => $company,
                    'otherJobs' => $otherJobs,
                    'interviewsUsers' => $interviewsUsers,
                    'jobAvailabilityFlag' => $jobAvailabilityFlag,
                    'addedBeforeFlag' => $addedBeforeFlag,
                    'applyedUsers' => $applyedUsers,
                    'jobCategories' => $jobCategories,
                    'job_added_before_message' => $this->container->getParameter('job_added_before_message_show_job_page'),
                    'job_apply_success_message' => $this->container->getParameter('job_apply_success_message_show_job_page'),
                    'jobSkills' => $jobSkills,
                    'jobCategroies' => $jobCategroies,
                    'relatedJobs' => $relatedJobs,
                    'LatestJobs' => $LatestJobs,
                    'rand_banner' => $rand_banner
        ));
    }

    /**
     * Finds and displays a Internship entity.
     *
     */
    public function fb_showAction($id) {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('ObjectsInternJumpBundle:Internship')->find($id);
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        $cityRepo = $em->getRepository('ObjectsInternJumpBundle:City');
        $stateRepo = $em->getRepository('ObjectsInternJumpBundle:State');
        $categoryRepo = $em->getRepository('ObjectsInternJumpBundle:CVCategory');

        $categ = array();
        if (!$entity) {
            $message = $this->container->getParameter('internship_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        } else {
            //check if not the owner company
            //so will check for job active or not
            if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY') || $this->get('security.context')->getToken()->getUser()->getId() != $entity->getCompany()->getId()) {
                $todayDate = new \DateTime('today');
                if (!$entity->getActive() || $entity->getActiveTo() < $todayDate || $entity->getActiveFrom() > $todayDate) {
                    $message = $this->container->getParameter('internship_not_found_error_msg');
                    return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                                'message' => $message,));
                }
            }
        }


        //get this job company
        $company = $entity->getCompany();

        //check if this job available
        $nowDate = date('Y-m-d');
        $jobAvailabilityFlag = 0;
        if (strtotime($nowDate) <= strtotime($entity->getActiveTo()->format('Y-m-d')) && strtotime($entity->getActiveFrom()->format('Y-m-d')) <= strtotime($nowDate)) {
            $jobAvailabilityFlag = 1;
        }

        //check if the logein user add this job before
        $addedBeforeFlag = 1;
        if (TRUE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user object
            $user = $this->get('security.context')->getToken()->getUser();
            $userInternshipObject = $userInternshipRepo->findOneBy(array('user' => $user->getId(), 'internship' => $id));
            if ($userInternshipObject) {
                $addedBeforeFlag = 0;
            }
        }

        //get applyed users
        $applyedUsers = array();
        $otherJobs = array();
        $interviewsUsers = array();
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //get applyed users
            $applyedUsers = $userInternshipRepo->getJobApplyedUsers($id);
            //get others company jobs
            $otherJobs = $internshipRepo->getOtherJobs($id, $company->getId());
            //get interviews user
            $interviewsUsers = $interviewRepo->findBy(array('company' => $company->getId(), 'internship' => $id), array('interviewDate' => 'desc'));
        }

        //get job categories
        $jobCategories = array();
        foreach ($entity->getCategories() as $category) {
            $jobCategories [] = $category->getId();
        }

        //get latest jobs
        $LatestJobs = array();
        if (true === $this->get('security.context')->isGranted('ROLE_USER')) {
            //get logedin user object
            $user = $this->get('security.context')->getToken()->getUser();

            $cvs = $em->getRepository('ObjectsInternJumpBundle:CV')->getAllCvs($user->getId());
            //print_r($cvs);
            if ($cvs) {//if student Has CVs
                //echo "found cvs <br>";
                foreach ($cvs as $cv) {
                    foreach ($cv->getCategories() as $cat)
                        $categ[] = $cat->getId();
                }
            }

            if (sizeof($categ) > 0) { //found array of categories
                $LatestJobs = $em->getRepository('ObjectsInternJumpBundle:Internship')->getLatestJobs($categ, 5);
            }
        }

        //get related jobs
        $jobCatIds = $internshipRepo->getJobCatIds($id);
        $relatedJobs = array();
        if (sizeof($jobCatIds) > 0) {
            $relatedJobs = $internshipRepo->getRelatedJobs($id, $jobCatIds);
        }

        return $this->render('ObjectsInternJumpBundle:Internship:fb_show.html.twig', array(
                    'entity' => $entity,
                    'company' => $company,
                    'otherJobs' => $otherJobs,
                    'interviewsUsers' => $interviewsUsers,
                    'jobAvailabilityFlag' => $jobAvailabilityFlag,
                    'addedBeforeFlag' => $addedBeforeFlag,
                    'applyedUsers' => $applyedUsers,
                    'jobCategories' => $jobCategories,
                    'job_added_before_message' => $this->container->getParameter('job_added_before_message_show_job_page'),
                    'job_apply_success_message' => $this->container->getParameter('job_apply_success_message_show_job_page'),
//                    'allCompanies' => $allCompanies,
                    'LatestJobs' => $LatestJobs,
                    'relatedJobs' => $relatedJobs
        ));
    }

    /**
     * this function will get all countries json
     * @author Ahmed
     */
    public function getAllCountriesAction() {
        $em = $this->getDoctrine()->getEntityManager();
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');

        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray [$value['id']] = $value['name'];
        }

        return new Response(json_encode($allCountriesArray));
    }

    /**
     * this function will get all country cities & states by country id
     * @author Ahmed
     * @param int $countryId
     */
    public function getCounteyCitiesStatesAction($countryId) {
        $em = $this->getDoctrine()->getEntityManager();
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        //get country cities
        $countryCities = $countryRepo->getCountryCities($countryId);
        $countryCitiesArray = array();
        foreach ($countryCities as $value) {
            $countryCitiesArray [$value['id']] = $value['name'];
        }
        //get country states
        $countryStates = $countryRepo->getCountryStates($countryId);
        $countryStatesArray = array();
        foreach ($countryStates as $value) {
            $countryStatesArray [$value['id']] = $value['name'];
        }

        $resultsArray = array();
        $resultsArray ['cities'] = $countryCitiesArray;
        $resultsArray ['states'] = $countryStatesArray;
        return new Response(json_encode($resultsArray));
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
     * Displays a form to create a new Internship entity.
     *
     */
    public function newAction() {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //check for managers
            if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            }
        }

        //get logedin company objects
        //check for manager
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }

        $em = $this->getDoctrine()->getEntityManager();
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');

        //get the container object
        $container = $this->container;
        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray [$value['id']] = $value['name'];
        }
        $entity = new Internship();
        $entity->setCompany($company);
        $entity->setAddress($company->getAddress());

        //add one langauge entity to the internship
        $newInternshipLanguage = new \Objects\InternJumpBundle\Entity\InternshipLanguage();
//        $newInternshipLanguage->setLanguage(new \Objects\InternJumpBundle\Entity\Language());
        $entity->addInternshipLanguage($newInternshipLanguage);


        //Get State Repo
        $stateRepo = $em->getRepository('ObjectsInternJumpBundle:State');
        //Get The NewYork State which is decided to be set as a default state
        $defaultState = $stateRepo->findOneBy(array('slug' => 'new_york'));
        //Get the default state id to set it in the script chesen
        $defaultStateID = $defaultState->getId();
        //Get default state name
        $defaultStateName = $defaultState->getName();


        $request = $this->getRequest();
        //get after 1 year date
        $afterOneYearDate = new \DateTime('+1 year');

        //get validation group
        $formValidationGroups [] = 'newInternship';

        //minimumGPA list
        $minimumGPAArray = array();
        $minimumGPAArray ["0"] = "Doesn't Matter";
        $No = 0.1;
        for ($index = 1; $index <= 40; $index++) {
            $minimumGPAArray ["$No"] = $No;
            $No += 0.1;
        }
        //numberOfOpenings list
        $numberOfOpeningsArray = array();
        for ($index = 1; $index <= 30; $index++) {
            $numberOfOpeningsArray [$index] = $index;
        }

        //sessionPeriod list
        $nowYear = date("Y");
        $nextYear = $nowYear + 1;
        $sessionPeriodArray = array(
            'ASAP' => 'ASAP',
            'Flexable' => 'Flexable',
            'As Defined' => 'As Defined',
            'Spring 2013' => 'Spring ' . $nowYear,
            'Summer ' . $nowYear => 'Summer ' . $nowYear,
            'Fall ' . $nowYear => 'Fall ' . $nowYear,
            'Winter ' . $nowYear => 'Winter ' . $nowYear,
            'Spring ' . $nextYear => 'Spring ' . $nextYear,
            'Summer ' . $nextYear => 'Summer ' . $nextYear,
            'Fall ' . $nextYear => 'Fall ' . $nextYear,
            'Winter ' . $nextYear => 'Winter ' . $nextYear
        );
        //create a add new job form
        $form = $this->createFormBuilder($entity, array('validation_groups' => $formValidationGroups))
                ->add('positionType', 'choice', array('choices' => array('Internship' => 'Internship', 'Entry Level' => 'Entry Level')))
                ->add('workLocation', 'choice', array('choices' => array('Office' => 'Office', 'Virtual' => 'Virtual', 'Doesn’t Matter' => 'Doesn’t Matter')))
                ->add('minimumGPA', 'choice', array('choices' => $minimumGPAArray))
                ->add('numberOfOpenings', 'choice', array('choices' => $numberOfOpeningsArray))
                ->add('sessionPeriod', 'choice', array('choices' => $sessionPeriodArray))
                ->add('activeFrom', 'date', array('attr' => array('class' => 'activeFrom'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->add('activeTo', 'date', array('attr' => array('class' => 'activeTo'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->add('title')
                ->add('otherSkills', 'text', array('required' => FALSE))
                ->add('skills', 'entity', array(
                    'required' => FALSE,
                    'multiple' => true,
                    'class' => 'ObjectsInternJumpBundle:Skill',
                    'query_builder' => function(EntityRepository $er) {
                        $qb = $er->createQueryBuilder('s');
                        $qb->where('s.isSystem = 1');
                        return $qb;
                    }
                ))
//                ->add('keywords', 'text', array('required' => FALSE))
                ->add('compensation')
                ->add('description', null, array('required' => FALSE))
                ->add('requirements')
                ->add('categories', null, array('required' => FALSE))
                ->add('country', 'choice', array(
                    'choices' => $allCountriesArray,
                    'preferred_choices' => array($company->getCountry()),
                ))
                ->add('city')
                ->add('state', 'choice', array('empty_value' => '--- choose state ---', 'required' => false))
                ->add('address', 'text')
                ->add('zipcode')
                ->add('active', null, array('required' => FALSE))
                ->add('languages', 'collection', array('type' => new \Objects\InternJumpBundle\Form\InternshipLanguageType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false))
                ->add('Latitude', 'hidden')
                ->add('Longitude', 'hidden')
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                //get the user object from the form
                $entity = $form->getData();

                //check for other skills
                if ($entity->otherSkills) {
                    $skills = explode(',', $entity->otherSkills);
                    foreach ($skills as $skill) {
                        $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');
                        //check if this skill not exist in database
                        $skillObject = $skillRepo->findOneBy(array('title' => trim($skill)));
                        if (!$skillObject) {
                            //create new skill
                            $newSkill = new Skill();
                            $newSkill->setTitle(trim($skill));
                            $newSkill->setIsSystem(true);
                            $em->persist($newSkill);
                            $em->flush();
                            //add the skill to the user
                            $entity->addSkill($newSkill);
                        } else {
                            //check if the user have this skill
                            if (!$entity->getSkills()->contains($skillObject)) {
                                //add the skill to the user
                                $entity->addSkill($skillObject);
                            }
                        }
                    }
                }

                //check for keywords
                $keywrodsRepo = $em->getRepository('ObjectsInternJumpBundle:Keywords');

                if ($request->get('keywords')) {
                    //expload the keywords
                    $keywordsArray = explode(',', $request->get('keywords'));
//                    $newArrayColection = new ArrayCollection();
                    foreach ($keywordsArray as $onekeyword) {
                        //check if keyword exist
                        $keyword = $keywrodsRepo->findOneBy(array('name' => trim($onekeyword)));
                        if ($keyword) {
//                            $entity->setKeywords($newArrayColection);
                            $entity->addKeywords($keyword);
                        } else {
                            $newKeyWords = new \Objects\InternJumpBundle\Entity\Keywords();
                            $newKeyWords->setName(trim($onekeyword));
                            $em->persist($newKeyWords);
                            $em->flush();

//                            $entity->setKeywords($newArrayColection);
                            $entity->addKeywords($newKeyWords);
                        }
                    }
                }

                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();

                //set the success flag in the session
                $this->getRequest()->getSession()->setFlash('success', $container->getParameter('new_job_success_message'));


                //send email for suitable users
//                $categoryIdsArray = array();
//                foreach ($entity->getCategories() as $category) {
//                    $categoryIdsArray[] = $category->getId();
//                }
//                //get suitable cvs for this job categories
//                if (sizeof($categoryIdsArray) > 0) {
//
//                    //Now Get Country and State to compare them with useres' before sending them emails
//                    //get Job Country
//                    $country = $entity->getCountry();
//                    //get Job State
//                    $state = $entity->getState();
//                    //get suitable users' cvs
//                    $suitableCvs = $cvRepo->getNewJobSuitableCvs($categoryIdsArray, $country, $state);
//
//                    $newJobLink = $container->get('router')->generate('internship_show', array('id' => $entity->getId()), TRUE);
//                    $messageText = $container->getParameter('new_job_to_suitable_users_message_text');
//                    $subject = $container->getParameter('new_job_to_suitable_users_subject_text');
//                    //send email for the results users
//                    foreach ($suitableCvs as $user) {
//                        $message = \Swift_Message::newInstance()
//                                ->setSubject($subject)
//                                ->setFrom($container->getParameter('contact_us_email'))
//                                ->setTo($user['email'])
//                                ->setBody($container->get('templating')->render('ObjectsInternJumpBundle:Internship:newjobMail.html.twig', array(
//                                    'messageText' => $messageText,
//                                    'newJobLink' => $newJobLink
//                                )))
//                        ;
//                        //send the mail
//                        $container->get('mailer')->send($message);
//                    }
//                }
                //check if company want to add another job
//                if (isset($_POST['create'])) {
                return $this->redirect($this->generateUrl('internship_show', array('id' => $entity->getId())));
//                } elseif (isset($_POST['create-add-another'])) {
//                    return $this->redirect($this->generateUrl('internship_new', array()));
//                }
            }
        }

        return $this->render('ObjectsInternJumpBundle:Internship:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
                    'company' => $company,
                    'no_zipcode_message_new_job_page' => $this->container->getParameter('no_zipcode_message_new_job_page'),
                    'map_change_location_message' => $this->container->getParameter('map_change_location_message_new_job_page'),
                    'formName' => $this->container->getParameter('companyAddJob_FormName'),
                    'formDesc' => $this->container->getParameter('companyAddJob_FormDesc'),
                    'defaultStateName' => $defaultStateName,
        ));
    }

    /**
     * Displays a form to edit an existing Internship entity.
     *
     */
    public function editAction($id) {
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //check for managers
            if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            }
        }

        //get logedin company objects
        //check for manager
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }

        $em = $this->getDoctrine()->getEntityManager();
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray [$value['id']] = $value['name'];
        }

        $request = $this->getRequest();
        $entity = $em->getRepository('ObjectsInternJumpBundle:Internship')->find($id);

        if (!$entity) {
            $message = $this->container->getParameter('internship_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get validation group
        $formValidationGroups [] = 'editInternship';

        $minimumGPAArray = array();
        $minimumGPAArray ["0"] = "Doesn't Matter";
        $No = 0.1;
        for ($index = 1; $index <= 40; $index++) {
            $minimumGPAArray ["$No"] = $No;
            $No += 0.1;
        }

        $numberOfOpeningsArray = array();
        for ($index = 1; $index <= 30; $index++) {
            $numberOfOpeningsArray [$index] = $index;
        }

        //sessionPeriod list
        $nowYear = date("Y");
        $nextYear = $nowYear + 1;
        $sessionPeriodArray = array(
            'ASAP' => 'ASAP',
            'Flexable' => 'Flexable',
            'As Defined' => 'As Defined',
            'Spring 2013' => 'Spring ' . $nowYear,
            'Summer ' . $nowYear => 'Summer ' . $nowYear,
            'Fall ' . $nowYear => 'Fall ' . $nowYear,
            'Winter ' . $nowYear => 'Winter ' . $nowYear,
            'Spring ' . $nextYear => 'Spring ' . $nextYear,
            'Summer ' . $nextYear => 'Summer ' . $nextYear,
            'Fall ' . $nextYear => 'Fall ' . $nextYear,
            'Winter ' . $nextYear => 'Winter ' . $nextYear
        );

        //get internship keywords
        $keywords = $entity->getKeywords();
        $keywordsString = "";
        $index = sizeof($keywords);
        foreach ($keywords as $keyword) {
            if ($index > 1)
                $keywordsString .= $keyword->getName() . ',';
            else
                $keywordsString .= $keyword->getName();
            $index--;
        }

        //check for langauge
        //add one langauge entity to the internship
        if (sizeof($entity->getLanguages()) < 1) {
            $newInternshipLanguage = new \Objects\InternJumpBundle\Entity\InternshipLanguage();
            $entity->addInternshipLanguage($newInternshipLanguage);
        }

        //create a add new job form
        $editForm = $this->createFormBuilder($entity, array('validation_groups' => $formValidationGroups))
                ->add('positionType', 'choice', array('choices' => array('Internship' => 'Internship', 'Entry Level' => 'Entry Level')))
                ->add('workLocation', 'choice', array('choices' => array('Office' => 'Office', 'Virtual' => 'Virtual', 'Doesn’t Matter' => 'Doesn’t Matter')))
                ->add('minimumGPA', 'choice', array('choices' => $minimumGPAArray))
                ->add('numberOfOpenings', 'choice', array('choices' => $numberOfOpeningsArray))
                ->add('sessionPeriod', 'choice', array('choices' => $sessionPeriodArray))
                ->add('activeFrom', 'date', array('attr' => array('class' => 'activeFrom'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->add('activeTo', 'date', array('attr' => array('class' => 'activeTo'), 'widget' => 'single_text', 'format' => 'yyyy-MM-dd'))
                ->add('title')
//                ->add('keywords', null, array('required' => FALSE))
//                ->add('skills', null, array('required' => FALSE, 'attr' => array('class' => 'chzn-select', 'style' => 'width:310px;')))
                ->add('otherSkills', 'text', array('required' => FALSE))
                ->add('skills', 'entity', array(
                    'required' => FALSE,
                    'multiple' => true,
                    'class' => 'ObjectsInternJumpBundle:Skill',
                    'query_builder' => function(EntityRepository $er) {
                        $qb = $er->createQueryBuilder('s');
                        $qb->where('s.isSystem = 1');
                        return $qb;
                    },
                    'attr' => array('class' => 'chzn-select', 'style' => 'width:310px;')
                ))
                ->add('compensation')
                ->add('description', null, array('required' => FALSE))
                ->add('requirements')
                ->add('categories', null, array('required' => FALSE))
                ->add('country', 'choice', array(
                    'choices' => $allCountriesArray))
                ->add('city', NULL, array('attr' => array('style' => 'width:310px;')))
                ->add('state', 'choice', array('empty_value' => '--- choose state ---', 'required' => false))
                ->add('address', 'text')
                ->add('zipcode')
                ->add('active', null, array('required' => FALSE))
                ->add('languages', 'collection', array('type' => new \Objects\InternJumpBundle\Form\InternshipLanguageType(), 'allow_add' => true, 'allow_delete' => true, 'by_reference' => false))
                ->add('Latitude', 'hidden')
                ->add('Longitude', 'hidden')
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                //get the user object from the form
                $entity = $editForm->getData();

                //check for other skills
                if ($entity->otherSkills) {
                    $skills = explode(',', $entity->otherSkills);
                    foreach ($skills as $skill) {
                        $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');
                        //check if this skill not exist in database
                        $skillObject = $skillRepo->findOneBy(array('title' => trim($skill)));
                        if (!$skillObject) {
                            //create new skill
                            $newSkill = new Skill();
                            $newSkill->setTitle(trim($skill));
                            $newSkill->setIsSystem(true);
                            $em->persist($newSkill);
                            $em->flush();
                            //add the skill to the user
                            $entity->addSkill($newSkill);
                        } else {
                            //check if the user have this skill
                            if (!$entity->getSkills()->contains($skillObject)) {
                                //add the skill to the user
                                $entity->addSkill($skillObject);
                            }
                        }
                    }
                }

                //check for keywords
                if ($request->get('keywords')) {
                    $keywords = explode(",", $request->get('keywords'));
                    //remove internship keywords
                    $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
                    $internshipObject = $internshipRepo->find($id);
                    $internshipObject->deleteKeywords();

                    foreach ($keywords as $keyword) {
                        //check if keyword exist
                        $keywordsRepo = $em->getRepository('ObjectsInternJumpBundle:Keywords');
                        $keywordObject = $keywordsRepo->findOneBy(array('name' => $keyword));
                        if ($keywordObject) {
                            $entity->addKeywords($keywordObject);
                        } else {
                            $newKeyWords = new \Objects\InternJumpBundle\Entity\Keywords();
                            $newKeyWords->setName(trim($keyword));
                            $em->persist($newKeyWords);
                            $em->flush();

                            $entity->addKeywords($newKeyWords);
                        }
                    }
                }



                $entity->setCompany($company);
                $em->persist($entity);
                $em->flush();

                //set the success flag in the session
                $this->getRequest()->getSession()->setFlash('success', 'Internship modified successfully');


                return $this->redirect($this->generateUrl('internship_edit', array('id' => $id)));
            }
        }


        return $this->render('ObjectsInternJumpBundle:Internship:edit.html.twig', array(
                    'entity' => $entity,
                    'company' => $company,
                    'form' => $editForm->createView(),
                    'keywordsString' => $keywordsString,
                    'no_zipcode_message_new_job_page' => $this->container->getParameter('no_zipcode_message_new_job_page'),
                    'map_change_location_message' => $this->container->getParameter('map_change_location_message_new_job_page'),
                    'formName' => $this->container->getParameter('companyEditJob_FormName'),
                    'formDesc' => $this->container->getParameter('companyEditJob_FormDesc'),
        ));
    }

    /**
     * Deletes a Internship entity.
     *
     */
    public function deleteAction($id) {
        //check for logrdin company
        //check for logrdin company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            //check for managers
            if (FALSE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
                return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
            }
        }

        $em = $this->getDoctrine()->getEntityManager();
        $job = $em->getRepository('ObjectsInternJumpBundle:Internship')->find($id);

        if (!$job) {
            $message = $this->container->getParameter('internship_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }
        //get job company
        $jobCompany = $job->getCompany();
        //get logedin company objects
        //check for manager
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }

        //check if the logedin company is the owner
        if ($company->getId() == $jobCompany->getId()) {
            $em->remove($job);
            $em->flush();
            //set the success flag in the session
            $this->getRequest()->getSession()->setFlash('success', 'Internship deleted successfully');
        }

        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            return $this->redirect($this->generateUrl('internship', array('loginName' => $company->getLoginName())));
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            return $this->redirect($this->generateUrl('manager_home', array()));
        }
    }

    /**
     * this action will add job to the logein user
     * @author Ahmed
     * @param int $jobId
     */
    public function addUserJobAction($jobId, $cvId) {
        //check if not loged in user
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }

        $em = $this->getDoctrine()->getEntityManager();
        $job = $em->getRepository('ObjectsInternJumpBundle:Internship')->find($jobId);
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');
        $interestRepo = $em->getRepository('ObjectsInternJumpBundle:Interest');

        if (!$job) {
            $message = $this->container->getParameter('internship_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }

        //get job company
        $company = $job->getCompany();
        //get logedin user object
        $user = $this->get('security.context')->getToken()->getUser();
        //get user cv
        $userCv = $cvRepo->find($cvId);
        //check if the logein user add this job before
        $userInternshipObject = $userInternshipRepo->findOneBy(array('user' => $user->getId(), 'internship' => $jobId));
        if ($userInternshipObject) {
            return new Response('added before');
        } else {
            //create new userInternShip object
            $newUserInternShip = new \Objects\InternJumpBundle\Entity\UserInternship();
            $newUserInternShip->setInternship($job);
            $newUserInternShip->setUser($user);
            $newUserInternShip->setStatus('apply');
            $newUserInternShip->setCv($userCv);
            $em->persist($newUserInternShip);

            $em->flush();

            //add user interset for this company
            //check if the user interset this campany before
            $intersetObject = $interestRepo->findOneBy(array('user' => $user->getId(), 'company' => $company->getId()));
            $intersetId = NULL;
            if (!$intersetObject) {
                $newIntersetObject = new \Objects\InternJumpBundle\Entity\Interest();
                $newIntersetObject->setAccepted('accepted');
                $newIntersetObject->setCompany($company);
                $newIntersetObject->setUser($user);
                $newIntersetObject->setRespondedAt(new \DateTime());
                $newIntersetObject->setValidTo(new \DateTime());
                $newIntersetObject->setCvId($cvId);
                $em->persist($newIntersetObject);
                $em->flush();

                $intersetId = $newIntersetObject->getId();
            } elseif ($intersetObject->getAccepted() == 'pending') {
                //check the valid to date
                if ($intersetObject->getValidTo() > new \DateTime('today')) {
                    $intersetObject->setAccepted('accepted');
                    $intersetObject->setCreatedAt(new \DateTime());
                    $intersetObject->setRespondedAt(new \DateTime());
                } else {
                    $intersetObject->setAccepted('accepted');
                    $intersetObject->setCreatedAt(new \DateTime());
                    $intersetObject->setRespondedAt(new \DateTime());
                    $intersetObject->setValidTo(new \DateTime());
                }

                $intersetId = $intersetObject->getId();
            }

            //add company job apply notification
            $comapnyNotification = new \Objects\InternJumpBundle\Entity\CompanyNotification();
            $comapnyNotification->setCompany($company);
            $comapnyNotification->setUser($user);
            $comapnyNotification->setType('user_job_apply');
            $comapnyNotification->setTypeId($jobId);
            $em->persist($comapnyNotification);
            InternjumpController::companyNotificationMail($this->container, $user, $company, 'user_job_apply', $jobId);



            $em->flush();

            //get the session object
            $session = $this->getRequest()->getSession();
            $session->setFlash('success', $this->container->getParameter('job_apply_success_message_show_job_page'));

            return new Response('done');
        }
    }

    /**
     * this action will show all active user cvs to use it in apply on job
     * @author Ahmed
     */
    public function userCvsAction() {
        //check if not loged in user
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->redirect($this->generateUrl('site_homepage', array(), TRUE));
        }
        $em = $this->getDoctrine()->getEntityManager();
        $cvRepo = $em->getRepository('ObjectsInternJumpBundle:CV');
        //get logedin user object
        $user = $this->get('security.context')->getToken()->getUser();

        //get all user cvs
        $allUserCvs = $cvRepo->findBy(array('user' => $user->getId()), array('createdAt' => 'desc'));
        return $this->render('ObjectsInternJumpBundle:Internship:userCvs.html.twig', array(
                    'allUserCvs' => $allUserCvs,
                    'user_does_not_have_cv_message' => $this->container->getParameter('user_does_not_have_cv_message')
        ));
    }

}

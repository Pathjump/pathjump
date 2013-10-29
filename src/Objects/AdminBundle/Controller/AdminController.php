<?php

namespace Objects\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Objects\AdminBundle\Form\Config;
use Objects\AdminBundle\Form\ConfigType;

/**
 * Admin controller.
 * @author Ola
 *
 */
class AdminController extends Controller {

    /**
     * @author ahmed
     * @param date $from
     * @param date $to
     */
    public function endOfDayReportAction($from, $to) {
        $em = $this->getDoctrine()->getEntityManager();
        $dayActivityRepo = $em->getRepository('ObjectsInternJumpBundle:DayActivity');


        if (!$from)
            $from = new \DateTime('-1 month');
        else
            $from = new \DateTime($from);

        if (!$to)
            $to = new \DateTime();
        else
            $to = new \DateTime($to);


        $dayActivities = $dayActivityRepo->getActivites($from, $to);

        return $this->render('ObjectsAdminBundle:Admin:endOfDayReport.html.twig', array(
                    'dayActivities' => $dayActivities
        ));
    }

    /**
     * the default admin home page action
     * @return Response
     */
    public function homeAction() {


        return $this->render('ObjectsAdminBundle:Admin:home.html.twig');
    }

    /**
     *
     * Admin Action for editing Constants in service file
     * @return response
     */
    public function adminChangeConstantsAction() {
        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            //create a new config
            $config = array();
            //get the container
            $container = $this->container;

            //set the configuration from the files
            $config['contact_us_email'] = $container->getParameter('contact_us_email');
            $config['site_meta_title'] = $container->getParameter('site_meta_title');
            $config['site_meta_keywords'] = $container->getParameter('site_meta_keywords');
            $config['site_meta_description'] = $container->getParameter('site_meta_description');
            $config['analytics'] = $container->getParameter('analytics');

            $config['new_job_success_message'] = $container->getParameter('new_job_success_message');
            $config['new_task_success_message'] = $container->getParameter('new_task_success_message');
            $config['new_job_to_suitable_users_message_text'] = $container->getParameter('new_job_to_suitable_users_message_text');
            $config['new_job_to_suitable_users_subject_text'] = $container->getParameter('new_job_to_suitable_users_subject_text');
            $config['skills_autocomplete_number'] = $container->getParameter('skills_autocomplete_number');
            $config['refresh_notification_time'] = $container->getParameter('refresh_notification_time');
            $config['jobs_per_page_index_jobs_page'] = $container->getParameter('jobs_per_page_index_jobs_page');
            $config['job_apply_success_message_show_job_page'] = $container->getParameter('job_apply_success_message_show_job_page');
            $config['job_added_before_message_show_job_page'] = $container->getParameter('job_added_before_message_show_job_page');
            $config['map_change_location_message_new_job_page'] = $container->getParameter('map_change_location_message_new_job_page');
            $config['no_zipcode_message_new_job_page'] = $container->getParameter('no_zipcode_message_new_job_page');
            $config['company_user_interset_waiting_message_user_data_page'] = $container->getParameter('company_user_interset_waiting_message_user_data_page');
            $config['company_user_interset_reject_message_user_data_page'] = $container->getParameter('company_user_interset_reject_message_user_data_page');
            $config['company_user_hire_pending_message_user_data_page'] = $container->getParameter('company_user_hire_pending_message_user_data_page');
            $config['company_user_hire_accept_message_user_data_page'] = $container->getParameter('company_user_hire_accept_message_user_data_page');
            $config['company_user_hire_reject_message_user_data_page'] = $container->getParameter('company_user_hire_reject_message_user_data_page');
            $config['company_user_interview_pending_message_user_data_page'] = $container->getParameter('company_user_interview_pending_message_user_data_page');
            $config['company_user_interview_accept_message_user_data_page'] = $container->getParameter('company_user_interview_accept_message_user_data_page');
            $config['company_user_interview_reject_message_user_data_page'] = $container->getParameter('company_user_interview_reject_message_user_data_page');
            $config['create_interview_request_success_message_user_data_page'] = $container->getParameter('create_interview_request_success_message_user_data_page');
            $config['create_hire_request_success_message_user_data_page'] = $container->getParameter('create_hire_request_success_message_user_data_page');
            $config['user_does_not_have_cv_message'] = $container->getParameter('user_does_not_have_cv_message');
            $config['default_latitude_company_signup_page'] = $container->getParameter('default_latitude_company_signup_page');
            $config['default_longitude_company_signup_page'] = $container->getParameter('default_longitude_company_signup_page');
            $config['company_signup_welcome_message'] = $container->getParameter('company_signup_welcome_message');
            $config['tasks_per_show_page'] = $container->getParameter('tasks_per_show_page');
            $config['jobs_per_search_results_page'] = $container->getParameter('jobs_per_search_results_page');
            $config['face_page_url'] = $container->getParameter('face_page_url');
            $config['twitter_page_url'] = $container->getParameter('twitter_page_url');
            $config['google_page_url'] = $container->getParameter('google_page_url');
            $config['linkledIn_page_url'] = $container->getParameter('linkledIn_page_url');
            $config['home_page_video_id'] = $container->getParameter('home_page_video_id');

            $config['a_grade_points'] = $container->getParameter('a_grade_points');
            $config['b_grade_points'] = $container->getParameter('b_grade_points');
            $config['c_grade_points'] = $container->getParameter('c_grade_points');
            $config['d_grade_points'] = $container->getParameter('d_grade_points');
            $config['ef_grade_points'] = $container->getParameter('e/f_grade_points');
            $config['associate_degree_points'] = $container->getParameter('associate_degree_points');
            $config['bachelor_degree_points'] = $container->getParameter('bachelor_degree_points');
            $config['master_degree_points'] = $container->getParameter('master_degree_points');
            $config['doctoral_degree_points'] = $container->getParameter('doctoral_degree_points');
            $config['one_year_experience_points'] = $container->getParameter('one_year_experience_points');
            $config['skill_point'] = $container->getParameter('skill_point');
            $config['contact_detinations'] = $container->getParameter('contact_detinations');
            $config['contact_info_name'] = $container->getParameter('contact_info_name');
            $config['contact_info_address_part1'] = $container->getParameter('contact_info_address_part1');
            $config['contact_info_address_part2'] = $container->getParameter('contact_info_address_part2');
            $config['resource_per_page'] = $container->getParameter('resource_per_page');
            $config['facebook_mesasage'] = $container->getParameter('facebook_mesasage');
            $config['internjumb_copyright'] = $container->getParameter('internjumb_copyright');
            $config['info_email'] = $container->getParameter('info_email');
            $config['user_not_found_error_msg'] = $container->getParameter('user_not_found_error_msg');
            $config['company_not_found_error_msg'] = $container->getParameter('company_not_found_error_msg');
            $config['cv_not_found_error_msg'] = $container->getParameter('cv_not_found_error_msg');
            $config['interview_not_found_error_msg'] = $container->getParameter('interview_not_found_error_msg');
            $config['request_not_found_error_msg'] = $container->getParameter('request_not_found_error_msg');
            $config['question_not_found_error_msg'] = $container->getParameter('question_not_found_error_msg');
            $config['interest_not_found_error_msg'] = $container->getParameter('interest_not_found_error_msg');
            $config['education_not_found_error_msg'] = $container->getParameter('education_not_found_error_msg');
            $config['emp_history_not_found_error_msg'] = $container->getParameter('emp_history_not_found_error_msg');
            $config['skill_not_found_error_msg'] = $container->getParameter('skill_not_found_error_msg');
            $config['task_not_found_error_msg'] = $container->getParameter('task_not_found_error_msg');
            $config['user_message_not_found_error_msg'] = $container->getParameter('user_message_not_found_error_msg');
            $config['company_message_not_found_error_msg'] = $container->getParameter('company_message_not_found_error_msg');
            $config['internship_not_found_error_msg'] = $container->getParameter('internship_not_found_error_msg');


            $config['worth_default_statrting_salary'] = $container->getParameter('worth_default_statrting_salary');
            $config['worth_experience_boost_value'] = $container->getParameter('worth_experience_boost_value');
            $config['worth_no_education'] = $container->getParameter('worth_no_education');
            $config['worth_education_end_date_empty'] = $container->getParameter('worth_education_end_date_empty');
            $config['worth_education_major_empty'] = $container->getParameter('worth_education_major_empty');
            $config['worth_no_major_skills_match'] = $container->getParameter('worth_no_major_skills_match');
            $config['worth_no_experience'] = $container->getParameter('worth_no_experience');
            $config['worth_no_skills'] = $container->getParameter('worth_no_skills');
            $config['worth_year_boost'] = $container->getParameter('worth_year_boost');
            $config['worth_facebook_message'] = $container->getParameter('worth_facebook_message');
            $config['worth_select_from'] = $container->getParameter('worth_select_from');
            $config['user_worth_description'] = $container->getParameter('user_worth_description');
            $config['user_net_worth_description'] = $container->getParameter('user_net_worth_description');

            //make form to fill it with data
            $form = $this->createFormBuilder($config)
                    ->add('contact_us_email', 'email')
                    ->add('site_meta_title', 'text')
                    ->add('site_meta_keywords', 'text')
                    ->add('site_meta_description', 'text')
                    ->add('analytics', 'textarea')
                    ->add('new_job_to_suitable_users_message_text', 'text')
                    ->add('new_job_to_suitable_users_subject_text', 'text')
                    ->add('skills_autocomplete_number', 'integer')
                    ->add('refresh_notification_time', 'integer')
                    ->add('jobs_per_page_index_jobs_page', 'integer')
                    ->add('job_apply_success_message_show_job_page', 'text')
                    ->add('job_added_before_message_show_job_page', 'text')
                    ->add('map_change_location_message_new_job_page', 'text')
                    ->add('no_zipcode_message_new_job_page', 'text')
                    ->add('company_user_interset_waiting_message_user_data_page', 'text')
                    ->add('company_user_interset_reject_message_user_data_page', 'text')
                    ->add('company_user_hire_pending_message_user_data_page', 'text')
                    ->add('company_user_hire_accept_message_user_data_page', 'text')
                    ->add('company_user_hire_reject_message_user_data_page', 'text')
                    ->add('company_user_interview_pending_message_user_data_page', 'text')
                    ->add('company_user_interview_accept_message_user_data_page', 'text')
                    ->add('company_user_interview_reject_message_user_data_page', 'text')
                    ->add('create_interview_request_success_message_user_data_page', 'text')
                    ->add('create_hire_request_success_message_user_data_page', 'text')
                    ->add('user_does_not_have_cv_message', 'text')
                    ->add('default_latitude_company_signup_page', 'text')
                    ->add('default_longitude_company_signup_page', 'text')
                    ->add('company_signup_welcome_message', 'text')
                    ->add('tasks_per_show_page', 'integer')
                    ->add('jobs_per_search_results_page', 'integer')
                    ->add('face_page_url', 'text', array('required' => FALSE))
                    ->add('twitter_page_url', 'text', array('required' => FALSE))
                    ->add('google_page_url', 'text', array('required' => FALSE))
                    ->add('linkledIn_page_url', 'text', array('required' => FALSE))
                    ->add('home_page_video_id', 'text', array('required' => FALSE))
                    ->add('new_job_success_message', 'text')
                    ->add('new_task_success_message', 'text')
                    ->add('a_grade_points', 'integer')
                    ->add('b_grade_points', 'integer')
                    ->add('c_grade_points', 'integer')
                    ->add('d_grade_points', 'integer')
                    ->add('ef_grade_points', 'integer')
                    ->add('associate_degree_points', 'integer')
                    ->add('bachelor_degree_points', 'integer')
                    ->add('master_degree_points', 'integer')
                    ->add('doctoral_degree_points', 'integer')
                    ->add('one_year_experience_points', 'integer')
                    ->add('skill_point', 'integer')
                    ->add('contact_detinations', 'text')
                    ->add('resource_per_page', 'integer')
                    ->add('facebook_mesasage', 'textarea')
                    ->add('internjumb_copyright', 'text')
                    ->add('info_email', 'email')
                    ->add('contact_info_name', 'text')
                    ->add('contact_info_address_part1', 'text')
                    ->add('contact_info_address_part2', 'text')
                    ->add('user_not_found_error_msg', 'textarea')
                    ->add('company_not_found_error_msg', 'textarea')
                    ->add('cv_not_found_error_msg', 'textarea')
                    ->add('interview_not_found_error_msg', 'textarea')
                    ->add('request_not_found_error_msg', 'textarea')
                    ->add('question_not_found_error_msg', 'textarea')
                    ->add('interest_not_found_error_msg', 'textarea')
                    ->add('education_not_found_error_msg', 'textarea')
                    ->add('emp_history_not_found_error_msg', 'textarea')
                    ->add('skill_not_found_error_msg', 'textarea')
                    ->add('task_not_found_error_msg', 'textarea')
                    ->add('user_message_not_found_error_msg', 'textarea')
                    ->add('company_message_not_found_error_msg', 'textarea')
                    ->add('internship_not_found_error_msg', 'textarea')
                    ->add('worth_default_statrting_salary', 'integer')
                    ->add('worth_experience_boost_value', 'integer')
                    ->add('worth_no_education', 'text')
                    ->add('worth_education_end_date_empty', 'text')
                    ->add('worth_education_major_empty', 'text')
                    ->add('worth_no_major_skills_match', 'text')
                    ->add('worth_no_experience', 'text')
                    ->add('worth_no_skills', 'text')
                    ->add('worth_year_boost', 'integer')
                    ->add('worth_facebook_message', 'text')
                    ->add('worth_select_from', 'choice', array(
                        'choices' => array('automatic' => 'Automatic', 'manually' => 'Manually')
                    ))
                    ->add('user_worth_description', 'text')
                    ->add('user_net_worth_description', 'text')
                    ->getForm();

            $request = $this->getRequest();


            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                //check that the form is valid
                if ($form->isValid()) {

                    //form data array
                    $formDataArray = $form->getData();
                    $message = FALSE;
                    //this flag is for opening the first configuration file
                    $firstFileChange = FALSE;
                    //compare existed data with the data entered by the user
                    //check if any of the configurations in the first file need change
                    if ($formDataArray['user_worth_description'] != $container->getParameter('user_worth_description')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['user_net_worth_description'] != $container->getParameter('user_net_worth_description')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_default_statrting_salary'] != $container->getParameter('worth_default_statrting_salary')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_experience_boost_value'] != $container->getParameter('worth_experience_boost_value')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_no_education'] != $container->getParameter('worth_no_education')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_education_end_date_empty'] != $container->getParameter('worth_education_end_date_empty')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_education_major_empty'] != $container->getParameter('worth_education_major_empty')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_no_major_skills_match'] != $container->getParameter('worth_no_major_skills_match')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_no_experience'] != $container->getParameter('worth_no_experience')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_no_skills'] != $container->getParameter('worth_no_skills')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_year_boost'] != $container->getParameter('worth_year_boost')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_facebook_message'] != $container->getParameter('worth_facebook_message')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['worth_select_from'] != $container->getParameter('worth_select_from')) {
                        $firstFileChange = TRUE;
                    }



                    if ($formDataArray['contact_us_email'] != $container->getParameter('contact_us_email')) {
                        $firstFileChange = TRUE;
                    }

                    if ($formDataArray['new_job_success_message'] != $container->getParameter('new_job_success_message')) {
                        $firstFileChange = TRUE;
                    }

                    if ($formDataArray['new_task_success_message'] != $container->getParameter('new_task_success_message')) {
                        $firstFileChange = TRUE;
                    }

                    if ($formDataArray['home_page_video_id'] != $container->getParameter('home_page_video_id')) {
                        $firstFileChange = TRUE;
                    }

                    if ($formDataArray['site_meta_title'] != $container->getParameter('site_meta_title')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['site_meta_keywords'] != $container->getParameter('site_meta_keywords')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['site_meta_description'] != $container->getParameter('site_meta_description')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['analytics'] != $container->getParameter('analytics')) {
                        $firstFileChange = TRUE;
                    }




                    if ($formDataArray['new_job_to_suitable_users_message_text'] != $container->getParameter('new_job_to_suitable_users_message_text')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['new_job_to_suitable_users_subject_text'] != $container->getParameter('new_job_to_suitable_users_subject_text')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['skills_autocomplete_number'] != $container->getParameter('skills_autocomplete_number')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['refresh_notification_time'] != $container->getParameter('refresh_notification_time')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['jobs_per_page_index_jobs_page'] != $container->getParameter('jobs_per_page_index_jobs_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['job_apply_success_message_show_job_page'] != $container->getParameter('job_apply_success_message_show_job_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['job_added_before_message_show_job_page'] != $container->getParameter('job_added_before_message_show_job_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['map_change_location_message_new_job_page'] != $container->getParameter('map_change_location_message_new_job_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['no_zipcode_message_new_job_page'] != $container->getParameter('no_zipcode_message_new_job_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_user_interset_waiting_message_user_data_page'] != $container->getParameter('company_user_interset_waiting_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_user_interset_reject_message_user_data_page'] != $container->getParameter('company_user_interset_reject_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_user_hire_pending_message_user_data_page'] != $container->getParameter('company_user_hire_pending_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_user_hire_accept_message_user_data_page'] != $container->getParameter('company_user_hire_accept_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_user_hire_reject_message_user_data_page'] != $container->getParameter('company_user_hire_reject_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_user_interview_pending_message_user_data_page'] != $container->getParameter('company_user_interview_pending_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_user_interview_accept_message_user_data_page'] != $container->getParameter('company_user_interview_accept_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_user_interview_reject_message_user_data_page'] != $container->getParameter('company_user_interview_reject_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['create_interview_request_success_message_user_data_page'] != $container->getParameter('create_interview_request_success_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['create_hire_request_success_message_user_data_page'] != $container->getParameter('create_hire_request_success_message_user_data_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['user_does_not_have_cv_message'] != $container->getParameter('user_does_not_have_cv_message')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['default_latitude_company_signup_page'] != $container->getParameter('default_latitude_company_signup_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['default_longitude_company_signup_page'] != $container->getParameter('default_longitude_company_signup_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_signup_welcome_message'] != $container->getParameter('company_signup_welcome_message')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['tasks_per_show_page'] != $container->getParameter('tasks_per_show_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['jobs_per_search_results_page'] != $container->getParameter('jobs_per_search_results_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['face_page_url'] != $container->getParameter('face_page_url')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['twitter_page_url'] != $container->getParameter('twitter_page_url')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['google_page_url'] != $container->getParameter('google_page_url')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['linkledIn_page_url'] != $container->getParameter('linkledIn_page_url')) {
                        $firstFileChange = TRUE;
                    }



                    if ($formDataArray['a_grade_points'] != $container->getParameter('a_grade_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['b_grade_points'] != $container->getParameter('b_grade_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['c_grade_points'] != $container->getParameter('c_grade_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['d_grade_points'] != $container->getParameter('d_grade_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['ef_grade_points'] != $container->getParameter('e/f_grade_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['associate_degree_points'] != $container->getParameter('associate_degree_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['bachelor_degree_points'] != $container->getParameter('bachelor_degree_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['master_degree_points'] != $container->getParameter('master_degree_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['doctoral_degree_points'] != $container->getParameter('doctoral_degree_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['one_year_experience_points'] != $container->getParameter('one_year_experience_points')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['skill_point'] != $container->getParameter('skill_point')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['contact_detinations'] != $container->getParameter('contact_detinations')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['resource_per_page'] != $container->getParameter('resource_per_page')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['facebook_mesasage'] != $container->getParameter('facebook_mesasage')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['internjumb_copyright'] != $container->getParameter('internjumb_copyright')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['info_email'] != $container->getParameter('info_email')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['contact_info_name'] != $container->getParameter('contact_info_name')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['contact_info_address_part1'] != $container->getParameter('contact_info_address_part1')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['contact_info_address_part2'] != $container->getParameter('contact_info_address_part2')) {
                        $firstFileChange = TRUE;
                    }



                    if ($formDataArray['user_not_found_error_msg'] != $container->getParameter('user_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_not_found_error_msg'] != $container->getParameter('company_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['cv_not_found_error_msg'] != $container->getParameter('cv_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['interview_not_found_error_msg'] != $container->getParameter('interview_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['request_not_found_error_msg'] != $container->getParameter('request_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['question_not_found_error_msg'] != $container->getParameter('question_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['interest_not_found_error_msg'] != $container->getParameter('interest_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['education_not_found_error_msg'] != $container->getParameter('education_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['emp_history_not_found_error_msg'] != $container->getParameter('emp_history_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['skill_not_found_error_msg'] != $container->getParameter('skill_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['task_not_found_error_msg'] != $container->getParameter('task_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['user_message_not_found_error_msg'] != $container->getParameter('user_message_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['company_message_not_found_error_msg'] != $container->getParameter('company_message_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['internship_not_found_error_msg'] != $container->getParameter('internship_not_found_error_msg')) {
                        $firstFileChange = TRUE;
                    }


                    //check if we need to open the first file to change it is config
                    if ($firstFileChange) {


                        //the configuration file path
                        $configFile = __DIR__ . '/../../InternJumpBundle/Resources/config/config.yml';
                        //create a new yaml parser
                        $yaml = new \Symfony\Component\Yaml\Parser();
                        //try to open the configuration file
                        $content = @file_get_contents($configFile);
                        //check if we opened the file
                        if ($content !== FALSE) {

                            //file opened try to parse the content
                            try {
                                //parser to parse services.yml and get its data
                                $value = $yaml->parse($content);
                            } catch (\Exception $e) {
                                // an error occurred during parsing
                                return $this->render('::general_admin.html.twig', array(
                                            'message' => 'Unable to parse the YAML File: ' . $configFile . '<br/><strong>Please fix this: </strong>' . $e->getMessage()
                                ));
                            }
                            //set the parameters in the file
                            $value['parameters']['worth_default_statrting_salary'] = $formDataArray['worth_default_statrting_salary'];
                            $value['parameters']['worth_experience_boost_value'] = $formDataArray['worth_experience_boost_value'];
                            $value['parameters']['worth_no_education'] = $formDataArray['worth_no_education'];
                            $value['parameters']['worth_education_end_date_empty'] = $formDataArray['worth_education_end_date_empty'];
                            $value['parameters']['worth_education_major_empty'] = $formDataArray['worth_education_major_empty'];
                            $value['parameters']['worth_no_major_skills_match'] = $formDataArray['worth_no_major_skills_match'];
                            $value['parameters']['worth_no_experience'] = $formDataArray['worth_no_experience'];
                            $value['parameters']['worth_no_skills'] = $formDataArray['worth_no_skills'];
                            $value['parameters']['worth_year_boost'] = $formDataArray['worth_year_boost'];
                            $value['parameters']['worth_facebook_message'] = $formDataArray['worth_facebook_message'];
                            $value['parameters']['worth_select_from'] = $formDataArray['worth_select_from'];
                            $value['parameters']['user_worth_description'] = $formDataArray['user_worth_description'];
                            $value['parameters']['user_net_worth_description'] = $formDataArray['user_net_worth_description'];


                            $value['parameters']['contact_us_email'] = $formDataArray['contact_us_email'];
                            $value['parameters']['site_meta_title'] = $formDataArray['site_meta_title'];
                            $value['parameters']['site_meta_keywords'] = $formDataArray['site_meta_keywords'];
                            $value['parameters']['site_meta_description'] = $formDataArray['site_meta_description'];
                            $value['parameters']['analytics'] = $formDataArray['analytics'];

                            $value['parameters']['new_job_success_message'] = $formDataArray['new_job_success_message'];
                            $value['parameters']['new_task_success_message'] = $formDataArray['new_task_success_message'];

                            $value['parameters']['home_page_video_id'] = $formDataArray['home_page_video_id'];
                            $value['parameters']['linkledIn_page_url'] = $formDataArray['linkledIn_page_url'];
                            $value['parameters']['google_page_url'] = $formDataArray['google_page_url'];
                            $value['parameters']['twitter_page_url'] = $formDataArray['twitter_page_url'];
                            $value['parameters']['face_page_url'] = $formDataArray['face_page_url'];
                            $value['parameters']['jobs_per_search_results_page'] = $formDataArray['jobs_per_search_results_page'];
                            $value['parameters']['tasks_per_show_page'] = $formDataArray['tasks_per_show_page'];
                            $value['parameters']['company_signup_welcome_message'] = $formDataArray['company_signup_welcome_message'];
                            $value['parameters']['default_longitude_company_signup_page'] = $formDataArray['default_longitude_company_signup_page'];
                            $value['parameters']['default_latitude_company_signup_page'] = $formDataArray['default_latitude_company_signup_page'];
                            $value['parameters']['user_does_not_have_cv_message'] = $formDataArray['user_does_not_have_cv_message'];
                            $value['parameters']['create_hire_request_success_message_user_data_page'] = $formDataArray['create_hire_request_success_message_user_data_page'];
                            $value['parameters']['create_interview_request_success_message_user_data_page'] = $formDataArray['create_interview_request_success_message_user_data_page'];
                            $value['parameters']['company_user_interview_reject_message_user_data_page'] = $formDataArray['company_user_interview_reject_message_user_data_page'];
                            $value['parameters']['company_user_interview_accept_message_user_data_page'] = $formDataArray['company_user_interview_accept_message_user_data_page'];
                            $value['parameters']['company_user_interview_pending_message_user_data_page'] = $formDataArray['company_user_interview_pending_message_user_data_page'];
                            $value['parameters']['company_user_hire_reject_message_user_data_page'] = $formDataArray['company_user_hire_reject_message_user_data_page'];
                            $value['parameters']['company_user_hire_accept_message_user_data_page'] = $formDataArray['company_user_hire_accept_message_user_data_page'];
                            $value['parameters']['company_user_hire_pending_message_user_data_page'] = $formDataArray['company_user_hire_pending_message_user_data_page'];
                            $value['parameters']['company_user_interset_reject_message_user_data_page'] = $formDataArray['company_user_interset_reject_message_user_data_page'];
                            $value['parameters']['company_user_interset_waiting_message_user_data_page'] = $formDataArray['company_user_interset_waiting_message_user_data_page'];
                            $value['parameters']['no_zipcode_message_new_job_page'] = $formDataArray['no_zipcode_message_new_job_page'];
                            $value['parameters']['map_change_location_message_new_job_page'] = $formDataArray['map_change_location_message_new_job_page'];
                            $value['parameters']['job_added_before_message_show_job_page'] = $formDataArray['job_added_before_message_show_job_page'];
                            $value['parameters']['job_apply_success_message_show_job_page'] = $formDataArray['job_apply_success_message_show_job_page'];
                            $value['parameters']['jobs_per_page_index_jobs_page'] = $formDataArray['jobs_per_page_index_jobs_page'];
                            $value['parameters']['refresh_notification_time'] = $formDataArray['refresh_notification_time'];
                            $value['parameters']['skills_autocomplete_number'] = $formDataArray['skills_autocomplete_number'];
                            $value['parameters']['new_job_to_suitable_users_subject_text'] = $formDataArray['new_job_to_suitable_users_subject_text'];
                            $value['parameters']['new_job_to_suitable_users_message_text'] = $formDataArray['new_job_to_suitable_users_message_text'];

                            $value['parameters']['a_grade_points'] = $formDataArray['a_grade_points'];
                            $value['parameters']['b_grade_points'] = $formDataArray['b_grade_points'];
                            $value['parameters']['c_grade_points'] = $formDataArray['c_grade_points'];
                            $value['parameters']['d_grade_points'] = $formDataArray['d_grade_points'];
                            $value['parameters']['e/f_grade_points'] = $formDataArray['ef_grade_points'];
                            $value['parameters']['associate_degree_points'] = $formDataArray['associate_degree_points'];
                            $value['parameters']['bachelor_degree_points'] = $formDataArray['bachelor_degree_points'];
                            $value['parameters']['master_degree_points'] = $formDataArray['master_degree_points'];
                            $value['parameters']['doctoral_degree_points'] = $formDataArray['doctoral_degree_points'];
                            $value['parameters']['one_year_experience_points'] = $formDataArray['one_year_experience_points'];
                            $value['parameters']['skill_point'] = $formDataArray['skill_point'];
                            $value['parameters']['contact_detinations'] = $formDataArray['contact_detinations'];
                            $value['parameters']['resource_per_page'] = $formDataArray['resource_per_page'];
                            $value['parameters']['facebook_mesasage'] = $formDataArray['facebook_mesasage'];
                            $value['parameters']['internjumb_copyright'] = $formDataArray['internjumb_copyright'];
                            $value['parameters']['info_email'] = $formDataArray['info_email'];
                            $value['parameters']['contact_info_name'] = $formDataArray['contact_info_name'];
                            $value['parameters']['contact_info_address_part1'] = $formDataArray['contact_info_address_part1'];
                            $value['parameters']['contact_info_address_part2'] = $formDataArray['contact_info_address_part2'];

                            $value['parameters']['user_not_found_error_msg'] = $formDataArray['user_not_found_error_msg'];
                            $value['parameters']['company_not_found_error_msg'] = $formDataArray['company_not_found_error_msg'];
                            $value['parameters']['cv_not_found_error_msg'] = $formDataArray['cv_not_found_error_msg'];
                            $value['parameters']['interview_not_found_error_msg'] = $formDataArray['interview_not_found_error_msg'];
                            $value['parameters']['request_not_found_error_msg'] = $formDataArray['request_not_found_error_msg'];
                            $value['parameters']['question_not_found_error_msg'] = $formDataArray['question_not_found_error_msg'];
                            $value['parameters']['interest_not_found_error_msg'] = $formDataArray['interest_not_found_error_msg'];
                            $value['parameters']['education_not_found_error_msg'] = $formDataArray['education_not_found_error_msg'];
                            $value['parameters']['emp_history_not_found_error_msg'] = $formDataArray['emp_history_not_found_error_msg'];
                            $value['parameters']['skill_not_found_error_msg'] = $formDataArray['skill_not_found_error_msg'];
                            $value['parameters']['task_not_found_error_msg'] = $formDataArray['task_not_found_error_msg'];
                            $value['parameters']['user_message_not_found_error_msg'] = $formDataArray['user_message_not_found_error_msg'];
                            $value['parameters']['company_message_not_found_error_msg'] = $formDataArray['company_message_not_found_error_msg'];
                            $value['parameters']['internship_not_found_error_msg'] = $formDataArray['internship_not_found_error_msg'];


                            //dump to make spaces and format of the file before update it
                            $dumper = new \Symfony\Component\Yaml\Dumper();
                            $yaml = $dumper->dump($value, 3);
                            //write the data to the file
                            $writed = @file_put_contents($configFile, $yaml);
                            if ($writed === FALSE) {
                                //an error occurred during parsing
                                $message = "Unable to write in the YAML file: $configFile";
                            }
                        } else {
                            // an error occurred during parsing
                            $message = "Unable to open the YAML file: $configFile";
                        }
                    }
                    //get the session to set flag
                    $session = $request->getSession();
                    //clear the previous flashes
                    $session->clearFlashes();
                    //check if any error occured
                    if ($message) {
                        //set the error flag
                        $session->setFlash('error', $message);
                    } else {
                        //check if user changed any thing in the form
                        if ($firstFileChange) {
                            //clear the cache for the new configurations to take effect
                            exec(PHP_BINDIR . '/php-cli ' . __DIR__ . '/../../../../app/console cache:clear -e prod');
                            exec(PHP_BINDIR . '/php-cli ' . __DIR__ . '/../../../../app/console cache:warmup --no-debug -e prod');
                            //set the success flag
                            $session->setFlash('success', 'Your configurations were saved');
                            return $this->redirect($this->generateUrl('admin_change_constants'));
                        } else {
                            //set the notice flag
                            $session->setFlash('notice', 'Nothing changed');
                        }
                    }
                } else {
                    echo 'not valid';
                }
            }

            return $this->render('ObjectsAdminBundle:Admin:changeConstants.html.twig', array(
                        'form' => $form->createView(),
                        'config' => $config
            ));
        } else {
            return $this->redirect($this->generateUrl('ObjectsInternJumpBundle_homepage'));
        }
    }

    /**
     * Action to edit about us page content (aboutUs.txt)
     * @return response
     */
    public function EditAboutUsAction() {

        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $config = array();
            $config['aboutustext'] = file_get_contents(__DIR__ . "/../../../../web/sitePages/aboutUs.txt");

            $file = __DIR__ . "/../../../../web/sitePages/aboutUs.txt";
            $form = $this->createFormBuilder($config)
                            ->add('aboutustext', 'textarea')->getForm();

            //print_r($config);exit;
            $request = $this->getRequest();



            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                if ($form->isValid()) {

                    $data = $form->getData();
                    $text = $data['aboutustext'];
                    $fp = fopen($file, "w") or die("Error opening file in write mode!");
                    fputs($fp, $text);
                    fclose($fp) or die("Error closing file!");
                }
            }

            return $this->render('ObjectsAdminBundle:Admin:editAboutUs.html.twig', array(
                        'form' => $form->createView()
                        , 'config' => $config
            ));
        } else {
            return $this->redirect($this->generateUrl('ObjectsInternJumpBundle_homepage'));
        }
    }

    /**
     * Action to edit the content of terms and services pages (TermsOfService.txt)
     * @return respnse
     */
    public function EditTermsOfServiceAction() {
        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $config = array();
            $config['TermsOfServicetext'] = file_get_contents(__DIR__ . "/../../../../web/sitePages/TermsOfService.txt");

            $file = __DIR__ . "/../../../../web/sitePages/TermsOfService.txt";
            $form = $this->createFormBuilder($config)
                            ->add('TermsOfServicetext', 'textarea')->getForm();

            //print_r($config);exit;
            $request = $this->getRequest();



            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                if ($form->isValid()) {

                    $data = $form->getData();
                    $text = $data['TermsOfServicetext'];
                    $fp = fopen($file, "w") or die("Error opening file in write mode!");
                    fputs($fp, $text);
                    fclose($fp) or die("Error closing file!");
                }
            }

            return $this->render('ObjectsAdminBundle:Admin:editTermsOfService.html.twig', array(
                        'form' => $form->createView()
                        , 'config' => $config
            ));
        } else {
            return $this->redirect($this->generateUrl('ObjectsInternJumpBundle_homepage'));
        }
    }

    /**
     * Action to edit the content of privacy policy page (PrivacyPolicy.txt)
     * @return response
     */
    public function EditPrivacyPolicyAction() {

        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $config = array();
            $config['PrivacyPolicytext'] = file_get_contents(__DIR__ . "/../../../../web/sitePages/PrivacyPolicy.txt");

            $file = __DIR__ . "/../../../../web/sitePages/PrivacyPolicy.txt";
            $form = $this->createFormBuilder($config)
                            ->add('PrivacyPolicytext', 'textarea')->getForm();

            //print_r($config);exit;
            $request = $this->getRequest();



            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                if ($form->isValid()) {

                    $data = $form->getData();
                    $text = $data['PrivacyPolicytext'];
                    $fp = fopen($file, "w") or die("Error opening file in write mode!");
                    fputs($fp, $text);
                    fclose($fp) or die("Error closing file!");
                }
            }

            return $this->render('ObjectsAdminBundle:Admin:editPrivacyPolicytext.html.twig', array(
                        'form' => $form->createView()
                        , 'config' => $config
            ));
        } else {
            return $this->redirect($this->generateUrl('ObjectsInternJumpBundle_homepage'));
        }
    }

    /**
     * This Action to edit shcools static page
     * @author Ola
     */
    public function schoolsAction() {

        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $config = array();
            $config['schoolstext1'] = file_get_contents(__DIR__ . "/../../../../web/sitePages/schools_p1.txt");
            $config['schoolstext2'] = file_get_contents(__DIR__ . "/../../../../web/sitePages/schools_p2.txt");

            $file1 = __DIR__ . "/../../../../web/sitePages/schools_p1.txt";
            $file2 = __DIR__ . "/../../../../web/sitePages/schools_p2.txt";
            $form = $this->createFormBuilder($config)
                            ->add('schoolstext1', 'textarea')
                            ->add('schoolstext2', 'textarea')->getForm();

            //print_r($config);exit;
            $request = $this->getRequest();



            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                if ($form->isValid()) {

                    $data = $form->getData();
                    $text1 = $data['schoolstext1'];
                    $text2 = $data['schoolstext2'];
                    $fp1 = fopen($file1, "w") or die("Error opening file in write mode!");
                    $fp2 = fopen($file2, "w") or die("Error opening file in write mode!");
                    fputs($fp1, $text1);
                    fputs($fp2, $text2);
                    fclose($fp1) or die("Error closing file!");
                    fclose($fp2) or die("Error closing file!");
                }
            }

            return $this->render('ObjectsAdminBundle:Admin:schools.html.twig', array(
                        'form' => $form->createView()
                        , 'config' => $config
            ));
        } else {
            return $this->redirect($this->generateUrl('ObjectsInternJumpBundle_homepage'));
        }
    }

    /**
     *
     * Admin Action for editing Constants of API Bundle config file
     * @return response
     */
    public function adminChangeApiConstantsAction() {
        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            //create a new config
            $config = array();
            //get the container
            $container = $this->container;

            //set the configuration from the files
            $config['consumer_key'] = $container->getParameter('consumer_key');
            $config['consumer_secret'] = $container->getParameter('consumer_secret');
            $config['fb_app_id'] = $container->getParameter('fb_app_id');
            $config['fb_app_secret'] = $container->getParameter('fb_app_secret');
            $config['linkedin_api_key'] = $container->getParameter('linkedin_api_key');
            $config['linkedin_secret_key'] = $container->getParameter('linkedin_secret_key');

            //make form to fill it with data
            $form = $this->createFormBuilder($config)
                    ->add('consumer_key', 'text', array('required' => true))
                    ->add('consumer_secret', 'text', array('required' => true))
                    ->add('fb_app_id', 'text', array('required' => true))
                    ->add('fb_app_secret', 'text', array('required' => true))
                    ->add('linkedin_api_key', 'text', array('required' => true))
                    ->add('linkedin_secret_key', 'text', array('required' => true))
                    ->getForm();

            $request = $this->getRequest();


            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                //check that the form is valid
                if ($form->isValid()) {

                    //form data array
                    $formDataArray = $form->getData();
                    $message = FALSE;
                    //this flag is for opening the first configuration file
                    $firstFileChange = FALSE;
                    //compare existed data with the data entered by the user
                    //check if any of the configurations in the first file need change
                    if ($formDataArray['consumer_key'] != $container->getParameter('consumer_key')) {
                        $firstFileChange = TRUE;
                    }

                    if ($formDataArray['consumer_secret'] != $container->getParameter('consumer_secret')) {
                        $firstFileChange = TRUE;
                    }

                    if ($formDataArray['fb_app_id'] != $container->getParameter('fb_app_id')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['fb_app_secret'] != $container->getParameter('fb_app_secret')) {
                        $firstFileChange = TRUE;
                    }

                    if ($formDataArray['linkedin_api_key'] != $container->getParameter('linkedin_api_key')) {
                        $firstFileChange = TRUE;
                    }
                    if ($formDataArray['linkedin_secret_key'] != $container->getParameter('linkedin_secret_key')) {
                        $firstFileChange = TRUE;
                    }

                    //check if we need to open the first file to change it is config
                    if ($firstFileChange) {


                        //the configuration file path
                        $configFile = __DIR__ . '/../../APIBundle/Resources/config/config.yml';
                        //create a new yaml parser
                        $yaml = new \Symfony\Component\Yaml\Parser();
                        //try to open the configuration file
                        $content = @file_get_contents($configFile);
                        //check if we opened the file
                        if ($content !== FALSE) {

                            //file opened try to parse the content
                            try {
                                //parser to parse config.yml and get its data
                                $value = $yaml->parse($content);
                            } catch (\Exception $e) {
                                // an error occurred during parsing
                                return $this->render('::general_admin.html.twig', array(
                                            'message' => 'Unable to parse the YAML File: ' . $configFile . '<br/><strong>Please fix this: </strong>' . $e->getMessage()
                                ));
                            }
                            //set the parameters in the file
                            $value['parameters']['consumer_key'] = $formDataArray['consumer_key'];
                            $value['parameters']['consumer_secret'] = $formDataArray['consumer_secret'];
                            $value['parameters']['fb_app_id'] = $formDataArray['fb_app_id'];
                            $value['parameters']['fb_app_secret'] = $formDataArray['fb_app_secret'];
                            $value['parameters']['linkedin_api_key'] = $formDataArray['linkedin_api_key'];
                            $value['parameters']['linkedin_secret_key'] = $formDataArray['linkedin_secret_key'];
                            //dump to make spaces and format of the file before update it
                            $dumper = new \Symfony\Component\Yaml\Dumper();
                            $yaml = $dumper->dump($value, 3);
                            //write the data to the file
                            $writed = @file_put_contents($configFile, $yaml);
                            return $this->redirect($this->generateUrl('admin_change_api_constants'));
                        }
                    }
                } else {
                    echo 'not valid';
                }
            }

            return $this->render('ObjectsAdminBundle:Admin:changeApiConstants.html.twig', array(
                        'form' => $form->createView(),
                        'config' => $config
            ));
        } else {
            return $this->redirect($this->generateUrl('ObjectsInternJumpBundle_homepage'));
        }
    }

    /**
     * this function will used to edit employers data page
     */
    public function editEmployersDataPageAction() {
        $data = array(
            'employersData' => @file_get_contents(__DIR__ . '/../../../../web/sitePages/employersData.txt')
        );
        $form = $this->createFormBuilder($data)
                ->add('employersData', 'textarea')
                ->getForm();
        $request = $this->getRequest(); //the default message to display to the user
        $message = FALSE;
        $status = FALSE;
        if ($request->getMethod() == 'POST') {
            //bind the form with the submitted data
            $form->bindRequest($request);
            //validate the form data
            if ($form->isValid()) {
                //form is valid get the data
                $data = $form->getData();
                //save the data to the file
                $bytes = @file_put_contents(__DIR__ . '/../../../../web/sitePages/employersData.txt', $data['employersData']);
                if ($bytes === FALSE) {
                    //set the error message
                    $message = 'writing failed';
                } else {
                    //set the success message
                    $message = 'edited was successful';
                    $status = 'success';
                }
            }
        }
        return $this->render('ObjectsAdminBundle:Admin:editEmployersData.html.twig', array(
                    'form' => $form->createView(),
                    'message' => $message,
                    'status' => $status
        ));
    }

    /**
     * this function will used to edit students data page
     */
    public function editStudentsDataPageAction() {
        $data = array(
            'studentsData' => @file_get_contents(__DIR__ . '/../../../../web/sitePages/studentsData.txt')
        );
        $form = $this->createFormBuilder($data)
                ->add('studentsData', 'textarea')
                ->getForm();
        $request = $this->getRequest(); //the default message to display to the user
        $message = FALSE;
        $status = FALSE;
        if ($request->getMethod() == 'POST') {
            //bind the form with the submitted data
            $form->bindRequest($request);
            //validate the form data
            if ($form->isValid()) {
                //form is valid get the data
                $data = $form->getData();
                //save the data to the file
                $bytes = @file_put_contents(__DIR__ . '/../../../../web/sitePages/studentsData.txt', $data['studentsData']);
                if ($bytes === FALSE) {
                    //set the error message
                    $message = 'writing failed';
                } else {
                    //set the success message
                    $message = 'edited was successful';
                    $status = 'success';
                }
            }
        }
        return $this->render('ObjectsAdminBundle:Admin:editStudentsData.html.twig', array(
                    'form' => $form->createView(),
                    'message' => $message,
                    'status' => $status
        ));
    }

    /**
     * Action to edit about us page content (CampusReps.txt)
     * @return response
     */
    public function EditCampusRepsAction() {

        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $config = array();
            $config['CampusReps'] = file_get_contents(__DIR__ . "/../../../../web/sitePages/CampusReps.txt");

            $file = __DIR__ . "/../../../../web/sitePages/CampusReps.txt";
            $form = $this->createFormBuilder($config)
                            ->add('CampusReps', 'textarea')->getForm();

            //print_r($config);exit;
            $request = $this->getRequest();



            if ($request->getMethod() == 'POST') {
                $form->bindRequest($request);
                if ($form->isValid()) {

                    $data = $form->getData();
                    $text = $data['CampusReps'];
                    $fp = fopen($file, "w") or die("Error opening file in write mode!");
                    fputs($fp, $text);
                    fclose($fp) or die("Error closing file!");
                }
            }

            return $this->render('ObjectsAdminBundle:Admin:editCampusReps.html.twig', array(
                        'form' => $form->createView()
                        , 'config' => $config
            ));
        } else {
            return $this->redirect($this->generateUrl('ObjectsInternJumpBundle_homepage'));
        }
    }

}

<?php

namespace Objects\InternJumpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Ahmed
 */
class ReminderCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();
        //Defining the name of command, its description and its argument
        $this->setName('Internjump:Reminder')
                ->setDescription('Internjump Reminder.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $container = $this->getContainer();
        $mailer = $container->get('mailer');
        $context = $this->getContainer()->get('router')->getContext();
        $context->setHost($container->getParameter('host_name'));
        $context->setScheme('https');

        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        $roleRepo = $em->getRepository('ObjectsUserBundle:Role');

        $todayDate = new \DateTime(date('Y-m-d'));
        $threeDate = clone $todayDate;
        $threeDate = $threeDate->modify('-3day');
        $sevenDate = clone $todayDate;
        $sevenDate = $sevenDate->modify('-7day');
        $sevenDate = clone $todayDate;
        $sevenDate = $sevenDate->modify('-7day');
        $fifteenDate = clone $todayDate;
        $fifteenDate = $fifteenDate->modify('-15day');

        //get role not active
        $roleNotActive = $roleRepo->findOneByName('ROLE_NOTACTIVE');

        //get not active users from 3,7,15 days
        $newUsers = $userRepo->getNotActiveUsers($threeDate, $sevenDate, $fifteenDate, $roleNotActive->getId());
        foreach ($newUsers as $user) {
            $body = $container->get('templating')->render('ObjectsInternJumpBundle:Internjump:Emails\notActiveUserReminder.txt.twig', array(
                'user' => $user
            ));

            $message = \Swift_Message::newInstance()
                    ->setSubject('InternJump - You are just a step away from landing.')
                    ->setFrom($container->getParameter('contact_us_email'))
                    ->setTo($user->getEmail())
                    ->setBody($body);
            ;
            $mailer->send($message);
        }

        //not active companies
        //get role not active
        $roleNotActive = $roleRepo->findOneByName('ROLE_NOTACTIVE_COMPANY');
        //get not active companies from 3,7,15 days
        $newCompanies = $companyRepo->getNotActiveCompanies($threeDate, $sevenDate, $fifteenDate, $roleNotActive->getId());
        foreach ($newCompanies as $company) {
            $body = $container->get('templating')->render('ObjectsInternJumpBundle:Internjump:Emails\notActiveComapnyReminder.txt.twig', array(
                'company' => $company
            ));

            $message = \Swift_Message::newInstance()
                    ->setSubject('InternJump - You are just a step away from landing.')
                    ->setFrom($container->getParameter('contact_us_email'))
                    ->setTo($company->getEmail())
                    ->setBody($body);
            ;
            $mailer->send($message);
        }

        //not complete resume
        //get role active
        $roleActive = $roleRepo->findOneByName('ROLE_USER');

        //get active users from 3,7,15 days
        $notCompleteResumeUsers = $userRepo->getNotCompleteResumeUsers($threeDate, $sevenDate, $fifteenDate, $roleActive->getId());
        foreach ($notCompleteResumeUsers as $user) {
            $body = $container->get('templating')->render('ObjectsInternJumpBundle:Internjump:Emails\completeResumeUserReminder.txt.twig', array(
                'user' => $user
            ));

            $message = \Swift_Message::newInstance()
                    ->setSubject('InternJump - Complete your resume.')
                    ->setFrom($container->getParameter('contact_us_email'))
                    ->setTo($user['email'])
                    ->setBody($body);
            ;
            $mailer->send($message);
        }

        //student active but did not take the character quiz
        //get active users from 3,7,15 days
        $notCharacterQuizUsers = $userRepo->getCharacterQuizUsers($threeDate, $sevenDate, $fifteenDate, $roleActive->getId());
        foreach ($notCharacterQuizUsers as $user) {
            $body = $container->get('templating')->render('ObjectsInternJumpBundle:Internjump:Emails\notCharacterQuizUserReminder.txt.twig', array(
                'user' => $user
            ));

            $message = \Swift_Message::newInstance()
                    ->setSubject('InternJump - Find out what character you are.')
                    ->setFrom($container->getParameter('contact_us_email'))
                    ->setTo($user->getEmail())
                    ->setBody($body);
            ;
            $mailer->send($message);
        }


        //no jobs active companies
        //get role active
        $roleActiveCompany = $roleRepo->findOneByName('ROLE_COMPANY');
        //get not active companies from 3,7,15 days
        $noJobsCompanies = $companyRepo->getNoJobsCompanies($threeDate, $sevenDate, $fifteenDate, $roleActiveCompany->getId());
        foreach ($noJobsCompanies as $company) {
            $body = $container->get('templating')->render('ObjectsInternJumpBundle:Internjump:Emails\noJobsComapnyReminder.txt.twig', array(
                'company' => $company
            ));

            $message = \Swift_Message::newInstance()
                    ->setSubject('InternJump - Add new position.')
                    ->setFrom($container->getParameter('contact_us_email'))
                    ->setTo($company['email'])
                    ->setBody($body);
            ;
            $mailer->send($message);
        }


        //no tasks companies
        //get not active companies from 3,7,15 days
        $noTasksCompanies = $companyRepo->getNoTasksCompanies($threeDate, $sevenDate, $fifteenDate, $roleActiveCompany->getId());
        foreach ($noTasksCompanies as $company) {
            $body = $container->get('templating')->render('ObjectsInternJumpBundle:Internjump:Emails\noTasksComapnyReminder.txt.twig', array(
                'company' => $company
            ));

            $message = \Swift_Message::newInstance()
                    ->setSubject('InternJump - No tasks assigned.')
                    ->setFrom($container->getParameter('contact_us_email'))
                    ->setTo($company['email'])
                    ->setBody($body);
            ;
            $mailer->send($message);
        }

        echo "IJ Auto reminder for students & company done \n";
    }

}

?>

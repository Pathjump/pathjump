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
class NewJobsToSuitableCvsCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();
        //Defining the name of command, its description and its argument
        $this->setName('Send:NewJobsToSuitableCvs')
                ->setDescription('Send New Jobs To Suitable Cvs.');
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
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        $cVRepositoryRepo = $em->getRepository('ObjectsInternJumpBundle:CV');


        //get active users
        $users = $userRepo->getUsersForNewJobs();
        foreach ($users as $user) {
            //get user cv categories
            $userCvCategoriesIds = $cVRepositoryRepo->getCvCatIds($user['cvId']);
            if (sizeof($userCvCategoriesIds) == 0)
                $userCvCategoriesIds = NULL;

            //get matching job
            $matchingJobs = $internshipRepo->getmatchingUserJobs($userCvCategoriesIds, $user['country'], $user['state']);

            if (sizeof($matchingJobs) > 0) {
                $body = $container->get('templating')->render('ObjectsInternJumpBundle:Internjump:Emails\NewJobsToSuitableCvs.txt.twig', array(
                    'jobs' => $matchingJobs
                ));

                $message = \Swift_Message::newInstance()
                        ->setSubject('InternJump - matching jobs')
                        ->setFrom($container->getParameter('contact_us_email'))
                        ->setTo($user['email'])
                        ->setBody($body);
                ;
                $mailer->send($message);

            }
        }

        echo "done \n";exit;
    }

}

?>

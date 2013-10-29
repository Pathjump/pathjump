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
class DayStatisticsCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();
        //Defining the name of command, its description and its argument
        $this->setName('Get:DayStatisticsCommand')
                ->setDescription('Get day statistics.');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $companyRepo = $em->getRepository('ObjectsInternJumpBundle:Company');
        $internshipRepo = $em->getRepository('ObjectsInternJumpBundle:Internship');
        $userRepo = $em->getRepository('ObjectsUserBundle:User');
        $userInternshipRepo = $em->getRepository('ObjectsInternJumpBundle:UserInternship');
        $interviewRepo = $em->getRepository('ObjectsInternJumpBundle:Interview');


        $todayDate = new \DateTime();

        //count company signups today
        $compSignupCount = sizeof($companyRepo->findBy(array('createdAt' => $todayDate)));
        //count company activated today
        $compActivatedCount = sizeof($companyRepo->findBy(array('activatedAt' => $todayDate)));
        //count new internships today
        $newInternshipsCount = sizeof($internshipRepo->findBy(array('createdAt' => $todayDate)));
        //count user signups today
        $userSignupCount = sizeof($userRepo->findBy(array('createdAt' => $todayDate)));
        //count user activated today
        $userActivatedCount = sizeof($userRepo->findBy(array('activatedAt' => $todayDate)));
        //count hired user today
        $userHiredCount = sizeof($userInternshipRepo->findBy(array('status' => 'accepted', 'createdAt' => $todayDate)));
        //count today interviews
        $todayInterviews = $interviewRepo->getTodayInterviews();

        //creat new day statistic
        $newDayStatistic = new \Objects\InternJumpBundle\Entity\DayActivity();
        $newDayStatistic->setCompanyActivatedCount($compActivatedCount);
        $newDayStatistic->setCompanySignupCount($compSignupCount);
        $newDayStatistic->setNewJobsCount($newInternshipsCount);
        $newDayStatistic->setNoOfHired($userHiredCount);
        $newDayStatistic->setNoOfInterviews($todayInterviews);
        $newDayStatistic->setStudentActivatedCount($userActivatedCount);
        $newDayStatistic->setStudentSignupCount($userSignupCount);

        $em->persist($newDayStatistic);
        $em->flush();

        echo $todayDate->format('Y-m-d');
    }

}

?>

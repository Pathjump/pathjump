<?php

namespace Objects\InternJumpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * insert cities & states into database
 * @author Ahmed
 */
class InsertCountriesCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();
        //Defining the name of command, its description and its argument
        $this->setName('Insert:Countries')
                ->setDescription('insert countries into database');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        ini_set('memory_limit', '-1');
        //get the entity manager
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $countryRepo = $em->getRepository("ObjectsInternJumpBundle:Country");

        //empty country tables
        $countryRepo->deleteAll();

        $handle = fopen(__DIR__ . '/../../../../web/citiesAndstates/Country.csv', "r");
        while (($data = fgetcsv($handle)) !== FALSE) {
            //create new country object
            $newCountryObject = new \Objects\InternJumpBundle\Entity\Country();
            $newCountryObject->setId($data['0']);
            $newCountryObject->setName($data['1']);
            $newCountryObject->setSlug($data['2']);
            $newCountryObject->setContinentCode($data['3']);
            $em->persist($newCountryObject);
            echo "new country \n";
        }

        //save all the data
        $em->flush();
        echo "done \n";
    }

}

?>

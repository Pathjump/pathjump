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
class InsertCitiesAndSatesCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();
        //Defining the name of command, its description and its argument
        $this->setName('Insert:CitiesAndSates')
                ->setDescription('insert cities & states into database');
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
        $stateRepo = $em->getRepository("ObjectsInternJumpBundle:State");
        $cityRepo = $em->getRepository("ObjectsInternJumpBundle:City");
        $countryRepo = $em->getRepository("ObjectsInternJumpBundle:Country");

        //empty state & city tables
        $cityRepo->deleteAll();
        $stateRepo->deleteAll();

        $handle = fopen(__DIR__ . '/../../../../web/citiesAndstates/cities&states.txt', "r");
        while (($data = fgetcsv($handle)) !== FALSE) {
            //get country object 
            $countryObject = $countryRepo->find($data['0']);
            if ($countryObject) {
                //check if state
                if (isset($data['3']) && $data['3'] == 'State') {
                    //new state object
                    if (isset($data['2']) && trim($data['2']) != '') {
                        $stateName = trim($data['2']);
                        //check if this state exist for the same country
                        $stateObject = $stateRepo->findOneBy(array('country' => $data['0'], 'name' => $stateName));
                        if (!$stateObject) {
                            $newStateObject = new \Objects\InternJumpBundle\Entity\State();
                            $newStateObject->setCountry($countryObject);
                            $newStateObject->setName($stateName);
                            //create slug
                            $slug = trim($stateName);
                            $slug = strtolower($slug);
                            $slug = preg_replace('/\W+/u', '_', $slug);

                            $originalSlug = $slug;
                            //check if this slug exist
                            $stateObject = $stateRepo->findOneBy(array('slug' => $slug));
                            $index = 1;
                            while ($stateObject) {
                                $slug = $originalSlug . '_' . $index;
                                $stateObject = $stateRepo->findOneBy(array('slug' => $slug));
                                $index++;
                            }
                            $newStateObject->setSlug($slug);
                            $em->persist($newStateObject);
                            //save the data
                            $em->flush();
                            echo "new state \n";
                        }
                    }
                } else {
                    if (isset($data['2']) && trim($data['2']) != '') {
                        $cityName = trim($data['2']);
                        //check if this city exist for the same country
                        $cityObject = $cityRepo->findOneBy(array('country' => $data['0'], 'name' => $cityName));
                        if (!$cityObject) {
                            //new city object
                            $newCityObject = new \Objects\InternJumpBundle\Entity\City();
                            $newCityObject->setCountry($countryObject);
                            $newCityObject->setName($cityName);
                            
                            //create slug
                            $slug = trim($cityName);
                            $slug = strtolower($slug);
                            $slug = preg_replace('/\W+/u', '_', $slug);

                            $originalSlug = $slug;
                            //check if this slug exist
                            $cityObject = $cityRepo->findOneBy(array('slug' => $slug));
                            $index = 1;
                            while ($cityObject) {
                                $slug = $originalSlug . '_' . $index;
                                $cityObject = $cityRepo->findOneBy(array('slug' => $slug));
                                $index++;
                            }
                            $newCityObject->setSlug($slug);
                            
                            $em->persist($newCityObject);
                            //save the data
                            $em->flush();
                            echo "new city \n";
                        }
                    }
                }
            }
        }


        echo "done \n";
    }

}

?>

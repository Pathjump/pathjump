<?php

namespace Objects\InternJumpBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Objects\InternJumpBundle\Entity\TopUniversity;
use Objects\InternJumpBundle\Entity\MajorSalary;

/**
 * import the hotels database info
 *
 * @author Ahmed
 */
class ImportExcelCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();
        //Defining the name of command, its description and its argument
        $this->setName('worth:data:import')
                ->setDescription('import the hotels database info');
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();

        require_once __DIR__ . '/../Resources/PHPExcel/PHPExcel.php';
        //load the top 400 university file
        $topUniversityPath = __DIR__ . '/../Resources/excelFolder/top-400-university-world.xls';
        //load major salary
        $majorSalarPath = __DIR__ . '/../Resources/excelFolder/major-starting-salary-2.xls';


        //check if exist
        if (file_exists($topUniversityPath)) {
            $objPHPExcel = \PHPExcel_IOFactory::load($topUniversityPath);
            //get first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            //count all rows
            $rowsNumber = $objPHPExcel->getActiveSheet()->getHighestRow();
            //remove header
            $rowsNumber = $rowsNumber - 1;
            for ($index = 2; $index < $rowsNumber; $index++) {
                $schoolName = $objPHPExcel->getActiveSheet()->getCell('B' . $index)->getValue();
                $schoolScore = $objPHPExcel->getActiveSheet()->getCell('C' . $index)->getValue();

                //insert the data
                $newTopUniversity = new TopUniversity();
                $newTopUniversity->setName(strtolower($schoolName));
                $newTopUniversity->setScore($schoolScore);
                $em->persist($newTopUniversity);
            }

            //add major salary
            //check if exist
            if (file_exists($majorSalarPath)) {
                $objPHPExcel = \PHPExcel_IOFactory::load($majorSalarPath);
                //get first sheet
                $objPHPExcel->setActiveSheetIndex(0);
                //count all rows
                $rowsNumber = $objPHPExcel->getActiveSheet()->getHighestRow();
                //remove header
                $rowsNumber = $rowsNumber - 1;
                for ($index = 2; $index < $rowsNumber; $index++) {
                    $majorName = $objPHPExcel->getActiveSheet()->getCell('A' . $index)->getValue();
                    $majorSalary = $objPHPExcel->getActiveSheet()->getCell('B' . $index)->getValue();
                    $majorSkills = $objPHPExcel->getActiveSheet()->getCell('C' . $index)->getValue();
                    $majorDetails = $objPHPExcel->getActiveSheet()->getCell('D' . $index)->getValue();

                    //insert the data
                    $newMajorSalary = new MajorSalary();
                    $newMajorSalary->setName(strtolower($majorName));
                    $salary = trim(str_replace(array('$', ','), '', $majorSalary));
                    $salary = explode('.', $salary);
                    $newMajorSalary->setSalary($salary['0']);
                    $newMajorSalary->setSkills(strtolower($majorSkills));
                    $newMajorSalary->setDetails($majorDetails);
                    $em->persist($newMajorSalary);
                }
            } else {
                $output->writeln("<error>No such file $majorSalarPath</error>");
            }

            $em->flush();
            $output->writeln("<info>Done :)</info>");
        } else {
            //print the error message
            $output->writeln("<error>No such file $topUniversityPath</error>");
        }
    }

}

?>

<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\DayActivity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\DayActivityRepository")
 */
class DayActivity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    /**
     * @var integer $companySignupCount
     *
     * @ORM\Column(name="companySignupCount", type="integer")
     */
    private $companySignupCount;

    /**
     * @var integer $companyActivatedCount
     *
     * @ORM\Column(name="companyActivatedCount", type="integer")
     */
    private $companyActivatedCount;

    /**
     * @var integer $newJobsCount
     *
     * @ORM\Column(name="newJobsCount", type="integer")
     */
    private $newJobsCount;

    /**
     * @var integer $studentSignupCount
     *
     * @ORM\Column(name="studentSignupCount", type="integer")
     */
    private $studentSignupCount;

    /**
     * @var integer $studentActivatedCount
     *
     * @ORM\Column(name="studentActivatedCount", type="integer")
     */
    private $studentActivatedCount;

    /**
     * @var integer $noOfInterviews
     *
     * @ORM\Column(name="noOfInterviews", type="integer")
     */
    private $noOfInterviews;

    /**
     * @var integer $noOfHired
     *
     * @ORM\Column(name="noOfHired", type="integer")
     */
    private $noOfHired;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return date
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set companySignupCount
     *
     * @param integer $companySignupCount
     */
    public function setCompanySignupCount($companySignupCount)
    {
        $this->companySignupCount = $companySignupCount;
    }

    /**
     * Get companySignupCount
     *
     * @return integer
     */
    public function getCompanySignupCount()
    {
        return $this->companySignupCount;
    }

    /**
     * Set companyActivatedCount
     *
     * @param integer $companyActivatedCount
     */
    public function setCompanyActivatedCount($companyActivatedCount)
    {
        $this->companyActivatedCount = $companyActivatedCount;
    }

    /**
     * Get companyActivatedCount
     *
     * @return integer
     */
    public function getCompanyActivatedCount()
    {
        return $this->companyActivatedCount;
    }

    /**
     * Set newJobsCount
     *
     * @param integer $newJobsCount
     */
    public function setNewJobsCount($newJobsCount)
    {
        $this->newJobsCount = $newJobsCount;
    }

    /**
     * Get newJobsCount
     *
     * @return integer
     */
    public function getNewJobsCount()
    {
        return $this->newJobsCount;
    }

    /**
     * Set studentSignupCount
     *
     * @param integer $studentSignupCount
     */
    public function setStudentSignupCount($studentSignupCount)
    {
        $this->studentSignupCount = $studentSignupCount;
    }

    /**
     * Get studentSignupCount
     *
     * @return integer
     */
    public function getStudentSignupCount()
    {
        return $this->studentSignupCount;
    }

    /**
     * Set studentActivatedCount
     *
     * @param integer $studentActivatedCount
     */
    public function setStudentActivatedCount($studentActivatedCount)
    {
        $this->studentActivatedCount = $studentActivatedCount;
    }

    /**
     * Get studentActivatedCount
     *
     * @return integer
     */
    public function getStudentActivatedCount()
    {
        return $this->studentActivatedCount;
    }

    /**
     * Set noOfInterviews
     *
     * @param integer $noOfInterviews
     */
    public function setNoOfInterviews($noOfInterviews)
    {
        $this->noOfInterviews = $noOfInterviews;
    }

    /**
     * Get noOfInterviews
     *
     * @return integer
     */
    public function getNoOfInterviews()
    {
        return $this->noOfInterviews;
    }

    /**
     * Set noOfHired
     *
     * @param integer $noOfHired
     */
    public function setNoOfHired($noOfHired)
    {
        $this->noOfHired = $noOfHired;
    }

    /**
     * Get noOfHired
     *
     * @return integer
     */
    public function getNoOfHired()
    {
        return $this->noOfHired;
    }
}
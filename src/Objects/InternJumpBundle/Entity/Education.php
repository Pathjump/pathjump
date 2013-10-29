<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Education
 * @Assert\Callback(methods={"isEndDateCorrect"}, groups={"education"})
 * @Assert\Callback(methods={"isStartDateCorrect"}, groups={"education"})
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\EducationRepository")
 */
class Education {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the user
     * @var \Objects\UserBundle\Entity\User $user
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="educations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var string $schoolName
     * @Assert\NotNull(groups={"education"})
     * @ORM\Column(name="schoolName", type="string", length=255)
     */
    private $schoolName;

    /**
     * @var boolean $underGraduate
     * @ORM\Column(name="underGraduate", type="boolean", nullable=true)
     */
    private $underGraduate;

    /**
     * @var string $major
     *
     * @ORM\Column(name="major", type="string", length=255, nullable=true)
     */
    private $major;

    /**
     * @var string $minor
     * @ORM\Column(name="minor", type="string", length=255, nullable=true)
     */
    private $minor;

    /**
     * @var integer $startDate
     * @Assert\Min(limit = 1900, groups={"education"})
     * @Assert\NotNull(groups={"education"})
     * @ORM\Column(name="underGraduateStartDate", type="integer", nullable=true)
     */
    private $startDate;

    /**
     * @var integer $endDate
     * @Assert\Min(limit = 1900, groups={"education"})
     * @Assert\NotNull(groups={"education"})
     * @ORM\Column(name="underGraduateEndDate", type="integer", nullable=true)
     */
    private $endDate;

    /**
     * @var text $extracurricularActivity
     * @ORM\Column(name="extracurricularActivity", type="text", nullable=true)
     */
    private $extracurricularActivity;

    /**
     * @var text $relevantCourseworkTaken
     * @ORM\Column(name="relevantCourseworkTaken", type="text", nullable=true)
     */
    private $relevantCourseworkTaken;

    /**
     * @var float $cumulativeGPA
     * @Assert\Max(limit = 5, message = "GPA must be less than 5.", groups={"education"})
     * @ORM\Column(name="cumulativeGPA", type="float", nullable=true)
     */
    private $cumulativeGPA;

    /**
     * @var float $majorGPA
     * @Assert\Max(limit = 5, message = "GPA must be less than 5.", groups={"education"})
     * @ORM\Column(name="majorGPA", type="float", nullable=true)
     */
    private $majorGPA;

    /**
     * @var string $graduateDegree
     * @ORM\Column(name="graduateDegree", type="string", length=255, nullable=true)
     */
    private $graduateDegree;

    /**
     * @var string $undergraduateDegree
     * @ORM\Column(name="undergraduateDegree", type="string", length=255, nullable=true)
     */
    private $undergraduateDegree;

    /**
     * this function will check if the user entered a valid endDate
     * @author Mahmoud
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isEndDateCorrect(ExecutionContext $context) {
        if ($this->endDate && $this->endDate > (date('Y') + 7)) {
            $propertyPath = $context->getPropertyPath() . '.endDate';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('End date must be less than 7 years from the current year', array(), NULL);
        }
    }

    /**
     * this function will check if the user entered a valid startDate
     * @author Mahmoud
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isStartDateCorrect(ExecutionContext $context) {
        if ($this->startDate && $this->endDate && $this->startDate > $this->endDate) {
            $propertyPath = $context->getPropertyPath() . '.endDate';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('End date must be greater than start date', array(), NULL);
        }
    }

    public function __toString() {
        return "Education $this->id";
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set schoolName
     *
     * @param string $schoolName
     */
    public function setSchoolName($schoolName) {
        $this->schoolName = $schoolName;
    }

    /**
     * Get schoolName
     *
     * @return string
     */
    public function getSchoolName() {
        return $this->schoolName;
    }

    /**
     * Set major
     *
     * @param string $major
     */
    public function setMajor($major) {
        $this->major = $major;
    }

    /**
     * Get major
     *
     * @return string
     */
    public function getMajor() {
        return $this->major;
    }

    /**
     * Set minor
     *
     * @param string $minor
     */
    public function setMinor($minor) {
        $this->minor = $minor;
    }

    /**
     * Get minor
     *
     * @return string
     */
    public function getMinor() {
        return $this->minor;
    }

    /**
     * Set extracurricularActivity
     *
     * @param text $extracurricularActivity
     */
    public function setExtracurricularActivity($extracurricularActivity) {
        $this->extracurricularActivity = $extracurricularActivity;
    }

    /**
     * Get extracurricularActivity
     *
     * @return text
     */
    public function getExtracurricularActivity() {
        return $this->extracurricularActivity;
    }

    /**
     * Set relevantCourseworkTaken
     *
     * @param text $relevantCourseworkTaken
     */
    public function setRelevantCourseworkTaken($relevantCourseworkTaken) {
        $this->relevantCourseworkTaken = $relevantCourseworkTaken;
    }

    /**
     * Get relevantCourseworkTaken
     *
     * @return text
     */
    public function getRelevantCourseworkTaken() {
        return $this->relevantCourseworkTaken;
    }

    /**
     * Set cumulativeGPA
     *
     * @param float $cumulativeGPA
     */
    public function setCumulativeGPA($cumulativeGPA) {
        $this->cumulativeGPA = $cumulativeGPA;
    }

    /**
     * Get cumulativeGPA
     *
     * @return float
     */
    public function getCumulativeGPA() {
        return $this->cumulativeGPA;
    }

    /**
     * Set majorGPA
     *
     * @param float $majorGPA
     */
    public function setMajorGPA($majorGPA) {
        $this->majorGPA = $majorGPA;
    }

    /**
     * Get majorGPA
     *
     * @return float
     */
    public function getMajorGPA() {
        return $this->majorGPA;
    }

    /**
     * Set graduateDegree
     *
     * @param string $graduateDegree
     */
    public function setGraduateDegree($graduateDegree) {
        $this->graduateDegree = $graduateDegree;
    }

    /**
     * Get graduateDegree
     *
     * @return string
     */
    public function getGraduateDegree() {
        return $this->graduateDegree;
    }

    /**
     * Set undergraduateDegree
     *
     * @param string $undergraduateDegree
     */
    public function setUndergraduateDegree($undergraduateDegree) {
        $this->undergraduateDegree = $undergraduateDegree;
    }

    /**
     * Get undergraduateDegree
     *
     * @return string
     */
    public function getUndergraduateDegree() {
        return $this->undergraduateDegree;
    }

    /**
     * Set user
     *
     * @param Objects\UserBundle\Entity\User $user
     */
    public function setUser(\Objects\UserBundle\Entity\User $user) {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Objects\UserBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set underGraduate
     *
     * @param boolean $underGraduate
     */
    public function setUnderGraduate($underGraduate) {
        $this->underGraduate = $underGraduate;
    }

    /**
     * Get underGraduate
     *
     * @return boolean
     */
    public function getUnderGraduate() {
        return $this->underGraduate;
    }

    /**
     * Set startDate
     *
     * @param integer $startDate
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    /**
     * Get startDate
     *
     * @return integer
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param integer $endDate
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

    /**
     * Get endDate
     *
     * @return integer
     */
    public function getEndDate() {
        return $this->endDate;
    }

}
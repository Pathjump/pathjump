<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\EmploymentHistory
 *
 * @Assert\Callback(methods={"isEndInCorrect"})
 * @Assert\Callback(methods={"isEmploymentTimeCorrect"})
 * @Assert\Callback(methods={"isEndInRequired"})
 * @Assert\Callback(methods={"isStartedFromCorrect"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\EmploymentHistoryRepository")
 */
class EmploymentHistory {

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
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="employmentHistories")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var boolean $isCurrent
     * @ORM\Column(name="isCurrent", type="boolean")
     */
    private $isCurrent = FALSE;

    /**
     * @var string $title
     * @Assert\NotNull
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * the worked in industry
     * @var \Objects\InternJumpBundle\Entity\CVCategory $industry
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\CVCategory")
     * @ORM\JoinColumn(name="cv_category_id", referencedColumnName="id", onDelete="SET NULL", onUpdate="CASCADE")
     */
    private $industry;

    /**
     * @var text $description
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var date $startedFrom
     * @Assert\NotNull
     * @Assert\Date
     * @ORM\Column(name="startedFrom", type="date")
     */
    private $startedFrom;

    /**
     * @var date $endedIn
     * @Assert\Date
     * @ORM\Column(name="endedIn", type="date", nullable=true)
     */
    private $endedIn;

    /**
     * @var string $companyName
     * @Assert\NotNull
     * @ORM\Column(name="companyName", type="string", length=255)
     */
    private $companyName;

    /**
     * @var string $companyUrl
     * @Assert\Url
     * @ORM\Column(name="companyUrl", type="string", length=255, nullable=true)
     */
    private $companyUrl;

    public function __toString() {
        return "$this->title at $this->companyName $this->id";
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
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set startedFrom
     *
     * @param date $startedFrom
     */
    public function setStartedFrom($startedFrom) {
        $this->startedFrom = $startedFrom;
    }

    /**
     * Get startedFrom
     *
     * @return date
     */
    public function getStartedFrom() {
        return $this->startedFrom;
    }

    /**
     * Set endedIn
     *
     * @param date $endedIn
     */
    public function setEndedIn($endedIn) {
        $this->endedIn = $endedIn;
    }

    /**
     * Get endedIn
     *
     * @return date
     */
    public function getEndedIn() {
        return $this->endedIn;
    }

    /**
     * Set companyName
     *
     * @param string $companyName
     */
    public function setCompanyName($companyName) {
        $this->companyName = $companyName;
    }

    /**
     * Get companyName
     *
     * @return string
     */
    public function getCompanyName() {
        return $this->companyName;
    }

    /**
     * Set companyUrl
     *
     * @param string $companyUrl
     */
    public function setCompanyUrl($companyUrl) {
        $this->companyUrl = $companyUrl;
    }

    /**
     * Get companyUrl
     *
     * @return string
     */
    public function getCompanyUrl() {
        return $this->companyUrl;
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
     * this function is used by symfony to validate employment time
     * @author Mahmoud
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isEndInCorrect(ExecutionContext $context) {
        if ($this->getEndedIn() && !$this->getIsCurrent() && ($this->getEndedIn() > new \DateTime())) {
            $propertyPath = $context->getPropertyPath() . '.endedIn';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('The end date must be less than the current date', array(), null);
        }
    }

    /**
     * this function is used by symfony to validate employment time
     * @author Mahmoud
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isEndInRequired(ExecutionContext $context) {
        if (!$this->getEndedIn() && !$this->getIsCurrent()) {
            $propertyPath = $context->getPropertyPath() . '.endedIn';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('You must set the end date if this is not your current job', array(), null);
        }
    }

    /**
     * this function is used by symfony to validate employment time
     * @author Mahmoud
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isStartedFromCorrect(ExecutionContext $context) {
        if ($this->getStartedFrom() && ($this->getStartedFrom() >= new \DateTime())) {
            $propertyPath = $context->getPropertyPath() . '.startedFrom';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('The start date must be less than the current date', array(), null);
        }
    }

    /**
     * this function is used by symfony to validate employment time
     * @author Mahmoud
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isEmploymentTimeCorrect(ExecutionContext $context) {
        if ($this->getEndedIn() && $this->getStartedFrom() && !$this->getIsCurrent() && $this->getEndedIn() <= $this->getStartedFrom()) {
            $propertyPath = $context->getPropertyPath() . '.startedFrom';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('The end date must be greater than the start date', array(), null);
        }
    }

    /**
     * Set isCurrent
     *
     * @param boolean $isCurrent
     */
    public function setIsCurrent($isCurrent) {
        $this->isCurrent = $isCurrent;
    }

    /**
     * Get isCurrent
     *
     * @return boolean
     */
    public function getIsCurrent() {
        return $this->isCurrent;
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function removeEndDate() {
        if ($this->isCurrent) {
            $this->endedIn = NULL;
        }
    }

    /**
     * this function is used to get the employment years count
     * @author Mahmoud
     * @return integer the number of years spent in this employment
     */
    public function getYearsCount() {
        $years = 0;
        if ($this->isCurrent) {
            //calculate the years from the current year
            $currentYear = new \DateTime();
            $dateInterval = $currentYear->diff($this->getStartedFrom());
            $years = $dateInterval->y;
        } else {
            $dateInterval = $this->getEndedIn()->diff($this->getStartedFrom());
            $years = $dateInterval->y;
        }
        return $years;
    }

    /**
     * Set industry
     *
     * @param Objects\InternJumpBundle\Entity\CVCategory $industry
     */
    public function setIndustry(\Objects\InternJumpBundle\Entity\CVCategory $industry) {
        $this->industry = $industry;
    }

    /**
     * Get industry
     *
     * @return Objects\InternJumpBundle\Entity\CVCategory
     */
    public function getIndustry() {
        return $this->industry;
    }

    /**
     * @author Mahmoud
     * @return string
     */
    public function getJobStatus() {
        if ($this->isCurrent) {
            return 'Yes';
        }
        return 'No';
    }

}
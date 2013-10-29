<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Task
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\TaskRepository")
 * @Assert\Callback(methods={"isStatusValid"})
 */
class Task {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the notes of the task
     * @var \Doctrine\Common\Collections\ArrayCollection $notes
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Note", mappedBy="task", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $notes;

    /**
     * the internship of the task
     * @var \Objects\InternJumpBundle\Entity\Internship $internship
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Internship", inversedBy="tasks")
     * @ORM\JoinColumn(name="internship_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $internship;

    /**
     * the company of the task
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company", inversedBy="tasks")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * the task assigned user
     * @var \Objects\UserBundle\Entity\User $user
     * @Assert\NotNull(groups={"new"})
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="tasks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var string $title
     * @Assert\NotNull(groups={"new"})
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $description
     * @Assert\NotNull(groups={"new"})
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string $status
     * @Assert\NotNull(groups={"new"})
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status="new";

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    /**
     * @var datetime $startedAt
     * @Assert\Date
     * @ORM\Column(name="startedAt", type="datetime", nullable=true)
     */
    private $startedAt;

    /**
     * @var datetime $endedAt
     * @Assert\Date
     * @ORM\Column(name="endedAt", type="datetime", nullable=true)
     */
    private $endedAt;

    public function __toString() {
        return "$this->title $this->id";
    }

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->startedAt = new \DateTime();
        $this->notes = new ArrayCollection();
        $this->subTasks = new ArrayCollection();
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
     * Set status
     *
     * @param string $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Get createdAt
     *
     * @return date 
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set startedAt
     *
     * @param datetime $startedAt
     */
    public function setStartedAt($startedAt) {
        $this->startedAt = $startedAt;
    }

    /**
     * Get startedAt
     *
     * @return datetime 
     */
    public function getStartedAt() {
        return $this->startedAt;
    }

    /**
     * Set endedAt
     *
     * @param datetime $endedAt
     */
    public function setEndedAt($endedAt) {
        $this->endedAt = $endedAt;
    }

    /**
     * Get endedAt
     *
     * @return datetime 
     */
    public function getEndedAt() {
        return $this->endedAt;
    }

    /**
     * @author Mahmoud
     * @return array of the valid statuses
     */
    public function getValidStatuses() {
        return array(
            'new' => 'new',
            'inprogress' => 'inprogress',
            'done' => 'done',
        );
    }

    /**
     * this function is used by symfony to validate the status text
     * @author Mahmoud
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isStatusValid(ExecutionContext $context) {
        //get the statuses array
        $statuses = $this->getValidStatuses();
        // check if the status is valid
        if (!isset($statuses[$this->getStatus()])) {
            $propertyPath = $context->getPropertyPath() . '.status';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('This is not a valid status for a task', array(), null);
        }
    }

    /**
     * Add notes
     *
     * @param Objects\InternJumpBundle\Entity\Note $notes
     */
    public function addNote(\Objects\InternJumpBundle\Entity\Note $notes) {
        $this->notes[] = $notes;
    }

    /**
     * Get notes
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getNotes() {
        return $this->notes;
    }

    /**
     * Set internship
     *
     * @param Objects\InternJumpBundle\Entity\Internship $internship
     */
    public function setInternship(\Objects\InternJumpBundle\Entity\Internship $internship) {
        $this->internship = $internship;
    }

    /**
     * Get internship
     *
     * @return Objects\InternJumpBundle\Entity\Internship 
     */
    public function getInternship() {
        return $this->internship;
    }

    /**
     * Set company
     *
     * @param Objects\InternJumpBundle\Entity\Company $company
     */
    public function setCompany(\Objects\InternJumpBundle\Entity\Company $company) {
        $this->company = $company;
    }

    /**
     * Get company
     *
     * @return Objects\InternJumpBundle\Entity\Company 
     */
    public function getCompany() {
        return $this->company;
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
     * Set createdAt
     *
     * @param date $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * this function will check if the task StartAt date is valid
     * @Assert\True(message = "The StartAt date must be greater than or equal the current datee",groups={"new"})
     */
    public function isStartAtCorrect() {
        $nowDate = new \DateTime('today');
        if ($this->getStartedAt() >= $nowDate ) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * this function will check if the task EndAt date is valid
     * @Assert\True(message = "The EndAt date must be greater than StartAt date",groups={"new"})
     */
    public function isEndedAtCorrect() {
        if ($this->getEndedAt() > $this->getStartedAt()) {
            return TRUE;
        }
        return FALSE;
    }
}
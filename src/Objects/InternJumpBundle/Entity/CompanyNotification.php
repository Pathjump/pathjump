<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\CompanyNotification
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\CompanyNotificationRepository")
 */
class CompanyNotification {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the company of the notification
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company", inversedBy="companyNotifications")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * the user of the notification
     * @var \Objects\UserBundle\Entity\User $user
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="companyNotifications")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * user_answer_question ,user_job_apply, user_accept_job, user_reject_job, user_accept_interest, user_reject_interest, user_accept_interview, user_reject_interview, user_edit_task, user_add_note, user_edit_note
     * @var string $type
     *  
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string $typeId
     *
     * @ORM\Column(name="typeId", type="string", length=255)
     */
    private $typeId;

    /**
     * @var boolean $isNew
     *
     * @ORM\Column(name="isNew", type="boolean")
     */
    private $isNew = TRUE;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    public function __toString() {
        return "Notification $this->id";
    }

    public function __construct() {
        $this->createdAt = new \DateTime();
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
     * Set type
     *
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Set typeId
     *
     * @param string $typeId
     */
    public function setTypeId($typeId) {
        $this->typeId = $typeId;
    }

    /**
     * Get typeId
     *
     * @return string 
     */
    public function getTypeId() {
        return $this->typeId;
    }

    /**
     * Set isNew
     *
     * @param boolean $isNew
     */
    public function setIsNew($isNew) {
        $this->isNew = $isNew;
    }

    /**
     * Get isNew
     *
     * @return boolean 
     */
    public function getIsNew() {
        return $this->isNew;
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
}
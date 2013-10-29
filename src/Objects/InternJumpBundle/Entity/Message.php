<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Message
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\MessageRepository")
 */
class Message {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the message user
     * @var \Objects\UserBundle\Entity\User $user
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * the message company
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company", inversedBy="messages")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * @var string $title
     * @Assert\NotNull
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $message
     * @Assert\NotNull
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var boolean $isRead
     *
     * @ORM\Column(name="isRead", type="boolean")
     */
    private $isRead = FALSE;

    /**
     * @var boolean $sentFromCompany
     *
     * @ORM\Column(name="sentFromCompany", type="boolean")
     */
    private $sentFromCompany = TRUE;

    /**
     * @var boolean $companyDeleted
     *
     * @ORM\Column(name="companyDeleted", type="boolean")
     */
    private $companyDeleted = FALSE;

    /**
     * @var boolean $userDeleted
     *
     * @ORM\Column(name="userDeleted", type="boolean")
     */
    private $userDeleted = FALSE;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    public function __toString() {
        return $this->title;
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
     * Set isRead
     *
     * @param boolean $isRead
     */
    public function setIsRead($isRead) {
        $this->isRead = $isRead;
    }

    /**
     * Get isRead
     *
     * @return boolean 
     */
    public function getIsRead() {
        return $this->isRead;
    }

    /**
     * Set message
     *
     * @param text $message
     */
    public function setMessage($message) {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return text 
     */
    public function getMessage() {
        return $this->message;
    }

    /**
     * Set sentFromCompany
     *
     * @param boolean $sentFromCompany
     */
    public function setSentFromCompany($sentFromCompany) {
        $this->sentFromCompany = $sentFromCompany;
    }

    /**
     * Get sentFromCompany
     *
     * @return boolean 
     */
    public function getSentFromCompany() {
        return $this->sentFromCompany;
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
     * Set companyDeleted
     *
     * @param boolean $companyDeleted
     */
    public function setCompanyDeleted($companyDeleted) {
        $this->companyDeleted = $companyDeleted;
    }

    /**
     * Get companyDeleted
     *
     * @return boolean 
     */
    public function getCompanyDeleted() {
        return $this->companyDeleted;
    }

    /**
     * Set userDeleted
     *
     * @param boolean $userDeleted
     */
    public function setUserDeleted($userDeleted) {
        $this->userDeleted = $userDeleted;
    }

    /**
     * Get userDeleted
     *
     * @return boolean 
     */
    public function getUserDeleted() {
        return $this->userDeleted;
    }

    /**
     * this function is used to check if we still need this message object
     * @return boolean
     */
    public function isMessageNeeded() {
        if ($this->companyDeleted && $this->userDeleted) {
            return FALSE;
        }
        return TRUE;
    }

}
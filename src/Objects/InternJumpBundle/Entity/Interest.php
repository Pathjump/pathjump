<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Interest
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\InterestRepository")
 */
class Interest {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the company of the interest
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company", inversedBy="interests")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * the user of the interest
     * @var \Objects\UserBundle\Entity\User $user
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="companiesInterests")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var string $accepted
     *
     * @ORM\Column(name="accepted", type="string", length=255)
     */
    private $accepted = 'pending';

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    /**
     * @var date $validTo
     * @Assert\NotNull
     * @Assert\Date
     * @ORM\Column(name="validTo", type="date")
     */
    private $validTo;

    /**
     * @var date $respondedAt
     *
     * @ORM\Column(name="respondedAt", type="date", nullable=true)
     */
    private $respondedAt;

    /**
     * @var integer $cvId
     * @Assert\NotNull
     * @ORM\Column(name="cvId", type="integer")
     */
    private $cvId;
    
    public function __toString() {
        return "interest $this->id";
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
     * Set accepted
     *
     * @param string $accepted
     */
    public function setAccepted($accepted) {
        $this->accepted = $accepted;
    }

    /**
     * Get accepted
     *
     * @return string 
     */
    public function getAccepted() {
        return $this->accepted;
    }

    /**
     * @author Ahmed
     * @return array of the valid statuses
     */
    public function getValidAcceptedStatuses() {
        return array(
            'pending' => 'pending',
            'rejected' => 'rejected',
            'accepted' => 'accepted',
        );
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
     * Set validTo
     *
     * @param date $validTo
     */
    public function setValidTo($validTo) {
        $this->validTo = $validTo;
    }

    /**
     * Get validTo
     *
     * @return date 
     */
    public function getValidTo() {
        return $this->validTo;
    }

    /**
     * Set respondedAt
     *
     * @param date $respondedAt
     */
    public function setRespondedAt($respondedAt) {
        $this->respondedAt = $respondedAt;
    }

    /**
     * Get respondedAt
     *
     * @return date 
     */
    public function getRespondedAt() {
        return $this->respondedAt;
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
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    /**
     * this function will check if the internship time is valid
     * @Assert\True(message = "The valid to date must be greater than or equal the current date")
     */
    public function isValidToDateCorrect() {
        $nowDate = new \DateTime('today');
        if ($this->getValidTo() >= $nowDate) {
            return TRUE;
        }
        return FALSE;
    }


    /**
     * Set cvId
     *
     * @param integer $cvId
     */
    public function setCvId($cvId)
    {
        $this->cvId = $cvId;
    }

    /**
     * Get cvId
     *
     * @return integer 
     */
    public function getCvId()
    {
        return $this->cvId;
    }
}
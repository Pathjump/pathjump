<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objects\InternJumpBundle\Entity\ReportedUser
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\ReportedUserRepository")
 */
class ReportedUser
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
     * @var \Objects\UserBundle\Entity\User $user
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="reports")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company", inversedBy="reports")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * the cv user
     * @var \Objects\InternJumpBundle\Entity\CV $cv
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\CV", inversedBy="reports")
     * @ORM\JoinColumn(name="cv_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $cv;

    /**
     * @var string $reason
     * @Assert\NotNull
     * @ORM\Column(name="reason", type="string", length=255)
     */
    private $reason;

    /**
     * @var text $reasonText
     *
     * @ORM\Column(name="reasonText", type="text",nullable=true)
     */
    private $reasonText;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

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
     * Set reason
     *
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set reasonText
     *
     * @param text $reasonText
     */
    public function setReasonText($reasonText)
    {
        $this->reasonText = $reasonText;
    }

    /**
     * Get reasonText
     *
     * @return text
     */
    public function getReasonText()
    {
        return $this->reasonText;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param Objects\UserBundle\Entity\User $user
     */
    public function setUser(\Objects\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Objects\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set company
     *
     * @param Objects\InternJumpBundle\Entity\Company $company
     */
    public function setCompany(\Objects\InternJumpBundle\Entity\Company $company)
    {
        $this->company = $company;
    }

    /**
     * Get company
     *
     * @return Objects\InternJumpBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set cv
     *
     * @param Objects\InternJumpBundle\Entity\CV $cv
     */
    public function setCv(\Objects\InternJumpBundle\Entity\CV $cv)
    {
        $this->cv = $cv;
    }

    /**
     * Get cv
     *
     * @return Objects\InternJumpBundle\Entity\CV
     */
    public function getCv()
    {
        return $this->cv;
    }
}
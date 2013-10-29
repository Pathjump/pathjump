<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\UserInternship
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\UserInternshipRepository")
 */
class UserInternship {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the cv of the user
     * @var \Objects\InternJumpBundle\Entity\CV $cv
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\CV", inversedBy="internships")
     * @ORM\JoinColumn(name="cv_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $cv;

    /**
     * the internship of the user
     * @var \Objects\InternJumpBundle\Entity\Internship $internship
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Internship", inversedBy="usersInternships")
     * @ORM\JoinColumn(name="internship_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $internship;

    /**
     * the user of the internship
     * @var \Objects\UserBundle\Entity\User $user
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="userInternships")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    public function __toString() {
        return "$this->id";
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
     * @author Mahmoud
     * @return array of the valid statuses
     */
    public function getValidStatuses() {
        return array(
            'apply' => 'apply',
            'pending' => 'pending',
            'rejected' => 'rejected',
            'accepted' => 'accepted',
        );
    }

    /**
     * Set cv
     *
     * @param Objects\InternJumpBundle\Entity\CV $cv
     */
    public function setCv(\Objects\InternJumpBundle\Entity\CV $cv) {
        $this->cv = $cv;
    }

    /**
     * Get cv
     *
     * @return Objects\InternJumpBundle\Entity\CV
     */
    public function getCv() {
        return $this->cv;
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
     * Get createdAt
     *
     * @return date
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
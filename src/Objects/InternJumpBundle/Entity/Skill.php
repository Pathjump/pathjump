<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Skill
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"title"})
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\SkillRepository")
 */
class Skill {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $title
     * @Assert\NotBlank(groups={"Default", "skill"})
     * @ORM\Column(name="title", type="string", length=255, unique=true)
     */
    private $title;

    /**
     * @var string $usersCount
     * @ORM\Column(name="usersCount", type="integer")
     */
    private $usersCount = 1;

    /**
     * @var boolean $isSystem
     *
     * @ORM\Column(name="isSystem", type="boolean")
     */
    private $isSystem = false;

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
        $this->title = trim($title);
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
     * Set usersCount
     *
     * @param string $usersCount
     */
    public function setUsersCount($usersCount) {
        $this->usersCount = $usersCount;
    }

    /**
     * Get usersCount
     *
     * @return string
     */
    public function getUsersCount() {
        return $this->usersCount;
    }

    /**
     * @ORM\PrePersist()
     */
    public function prePersist(){
        $this->title = strtolower($this->title);
    }


    /**
     * Set isSystem
     *
     * @param boolean $isSystem
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = $isSystem;
    }

    /**
     * Get isSystem
     *
     * @return boolean
     */
    public function getIsSystem()
    {
        return $this->isSystem;
    }
}
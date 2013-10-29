<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Objects\InternJumpBundle\Entity\Language
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\LanguageRepository")
 */
class Language
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
     * the internships for the language
     * @var \Doctrine\Common\Collections\ArrayCollection $internships
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\InternshipLanguage", mappedBy="language", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $internships;
    
    /**
     * the users for the language
     * @var \Doctrine\Common\Collections\ArrayCollection $users
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\UserLanguage", mappedBy="language", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $users;
    
    /**
     * @var string $name
     * @Assert\NotNull()
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function __toString(){
        return $this->name;
    }

        public function __construct()
    {
        $this->internships = new ArrayCollection();
        $this->users = new ArrayCollection();
    }
    
    /**
     * Add internships
     *
     * @param Objects\InternJumpBundle\Entity\InternshipLanguage $internships
     */
    public function addInternshipLanguage(\Objects\InternJumpBundle\Entity\InternshipLanguage $internships)
    {
        $this->internships[] = $internships;
    }

    /**
     * Get internships
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getInternships()
    {
        return $this->internships;
    }

    /**
     * Add users
     *
     * @param Objects\InternJumpBundle\Entity\UserLanguage $users
     */
    public function addUserLanguage(\Objects\InternJumpBundle\Entity\UserLanguage $users)
    {
        $this->users[] = $users;
    }

    /**
     * Get users
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}
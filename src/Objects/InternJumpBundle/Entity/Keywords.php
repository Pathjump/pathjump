<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Keywords
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\KeywordsRepository")
 */
class Keywords
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\Internship", mappedBy="keywords")
     */
    private $internships;

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
    public function __construct()
    {
        $this->internships = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function __toString(){
        return $this->name;
    }

        /**
     * Add internships
     *
     * @param Objects\InternJumpBundle\Entity\Internship $internships
     */
    public function addInternship(\Objects\InternJumpBundle\Entity\Internship $internships)
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
}
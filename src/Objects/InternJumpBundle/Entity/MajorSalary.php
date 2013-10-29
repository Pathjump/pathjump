<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\MajorSalary
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\MajorSalaryRepository")
 */
class MajorSalary
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
     * @var integer $salary
     *
     * @ORM\Column(name="salary", type="integer")
     */
    private $salary;
    
    /**
     * @var string $skills
     *
     * @ORM\Column(name="skills", type="string", length=255)
     */
    private $skills;
    
    /**
     * @var text $details
     *
     * @ORM\Column(name="details", type="text")
     */
    private $details;


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

    /**
     * Set salary
     *
     * @param integer $salary
     */
    public function setSalary($salary)
    {
        $this->salary = $salary;
    }

    /**
     * Get salary
     *
     * @return integer 
     */
    public function getSalary()
    {
        return $this->salary;
    }

    /**
     * Set skills
     *
     * @param string $skills
     */
    public function setSkills($skills)
    {
        $this->skills = $skills;
    }

    /**
     * Get skills
     *
     * @return string 
     */
    public function getSkills()
    {
        return $this->skills;
    }

    /**
     * Set details
     *
     * @param text $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * Get details
     *
     * @return text 
     */
    public function getDetails()
    {
        return $this->details;
    }
}
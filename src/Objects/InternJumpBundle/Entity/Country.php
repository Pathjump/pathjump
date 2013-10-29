<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Unique;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objects\InternJumpBundle\Entity\Country
 * @Unique\UniqueEntity(fields={"id"})
 * @Unique\UniqueEntity(fields={"name"})
 * @Unique\UniqueEntity(fields={"slug"})
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\CountryRepository")
 */
class Country {

    /**
     * @var string $id
     *
     * @ORM\Column(name="id", type="string", length=2, unique=true)
     * @ORM\Id
     * @Assert\Regex(pattern="/^[A-Z]+/", message="invalid country code")
     */
    private $id;

    /**
     * @var string $name
     * @Assert\NotNull
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string $slug
     * @Assert\NotNull
     * @Assert\Regex(pattern="/^\w+$/u", message="Only characters, numbers and _")
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var string $continentCode
     * 
     * @ORM\Column(name="continentCode", type="string", length=255, nullable=true)
     */
    private $continentCode;

    /**
     * @ORM\OneToMany(targetEntity="City", mappedBy="country", cascade={"persist","remove"})
     */
    private $cities;

    /**
     * @ORM\OneToMany(targetEntity="State", mappedBy="country", cascade={"persist","remove"})
     */
    private $states;

    public function __toString() {
        return $this->name;
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
     * Set name
     *
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set continentCode
     *
     * @param string $continentCode
     */
    public function setContinentCode($continentCode) {
        $this->continentCode = $continentCode;
    }

    /**
     * Get continentCode
     *
     * @return string 
     */
    public function getContinentCode() {
        return $this->continentCode;
    }

    public function __construct() {
        $this->cities = new ArrayCollection();
        $this->states = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Add cities
     *
     * @param Objects\InternJumpBundle\Entity\City $cities
     */
    public function addCity(\Objects\InternJumpBundle\Entity\City $cities) {
        $this->cities[] = $cities;
    }

    /**
     * Get cities
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCities() {
        return $this->cities;
    }

    /**
     * Add states
     *
     * @param Objects\InternJumpBundle\Entity\State $states
     */
    public function addState(\Objects\InternJumpBundle\Entity\State $states) {
        $this->states[] = $states;
    }

    /**
     * Get states
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getStates() {
        return $this->states;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug) {
        $this->slug = trim(preg_replace('/\W+/u', '_', $slug));
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug() {
        return $this->slug;
    }

}
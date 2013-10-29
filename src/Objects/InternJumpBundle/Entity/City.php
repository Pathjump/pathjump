<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as Unique;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objects\InternJumpBundle\Entity\City
 *
 * @Unique\UniqueEntity(fields={"slug"})
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\CityRepository")
 */
class City {

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
     * @Assert\NotNull
     * @ORM\Column(name="name", type="string", length=255)
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
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="cities")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=true)
     */
    private $country;

    /**
     * @var integer $priority
     * @ORM\Column(name="priority", type="integer")
     */
    private $priority = 0;

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
     * Set country
     *
     * @param Objects\InternJumpBundle\Entity\Country $country
     */
    public function setCountry(\Objects\InternJumpBundle\Entity\Country $country) {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return Objects\InternJumpBundle\Entity\Country
     */
    public function getCountry() {
        return $this->country;
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

    /**
     * Set priority
     *
     * @param integer $priority
     */
    public function setPriority($priority) {
        $this->priority = $priority;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority() {
        return $this->priority;
    }

}
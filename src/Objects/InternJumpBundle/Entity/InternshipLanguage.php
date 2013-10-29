<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objects\InternJumpBundle\Entity\InternshipLanguage
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\InternshipLanguageRepository")
 */
class InternshipLanguage {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the language of the internship
     * @var \Objects\InternJumpBundle\Entity\Internship $internship
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Internship", inversedBy="languages")
     * @ORM\JoinColumn(name="internship_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $internship;

    /**
     * the internship of the language
     * @Assert\NotNull()
     * @var \Objects\InternJumpBundle\Entity\Language $language
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Language", inversedBy="internships")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $language;

    /**
     * @var string $spokenFluency
     * @Assert\NotNull()
     * @ORM\Column(name="spokenFluency", type="string", length=255)
     */
    private $spokenFluency;

    /**
     * @var string $writtenFluency
     * @Assert\NotNull()
     * @ORM\Column(name="writtenFluency", type="string", length=255)
     */
    private $writtenFluency;

    /**
     * @var string $readFluency
     * @Assert\NotNull()
     * @ORM\Column(name="readFluency", type="string", length=255)
     */
    private $readFluency;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }
    
    public function __toString() {
        return $this->language. ' | Spoken : '.$this->spokenFluency. ' | Written : '.$this->writtenFluency. ' | Read : '.$this->readFluency;
    }

    /**
     * Set spokenFluency
     *
     * @param string $spokenFluency
     */
    public function setSpokenFluency($spokenFluency) {
        $this->spokenFluency = $spokenFluency;
    }

    /**
     * Get spokenFluency
     *
     * @return string
     */
    public function getSpokenFluency() {
        return $this->spokenFluency;
    }

    /**
     * Set writtenFluency
     *
     * @param string $writtenFluency
     */
    public function setWrittenFluency($writtenFluency) {
        $this->writtenFluency = $writtenFluency;
    }

    /**
     * Get writtenFluency
     *
     * @return string
     */
    public function getWrittenFluency() {
        return $this->writtenFluency;
    }

    /**
     * Set readFluency
     *
     * @param string $readFluency
     */
    public function setReadFluency($readFluency) {
        $this->readFluency = $readFluency;
    }

    /**
     * Get readFluency
     *
     * @return string
     */
    public function getReadFluency() {
        return $this->readFluency;
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
     * Set language
     *
     * @param Objects\InternJumpBundle\Entity\Language $language
     */
    public function setLanguage(\Objects\InternJumpBundle\Entity\Language $language) {
        $this->language = $language;
    }

    /**
     * Get language
     *
     * @return Objects\InternJumpBundle\Entity\Language
     */
    public function getLanguage() {
        return $this->language;
    }

}
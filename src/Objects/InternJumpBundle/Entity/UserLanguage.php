<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Objects\InternJumpBundle\Entity\UserLanguage
 * @UniqueEntity(fields={"user","language"})
 * @ORM\Table(uniqueConstraints={@UniqueConstraint(name="user_langauge_idx", columns={"user_id", "language_id"})})
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\UserLanguageRepository")
 */
class UserLanguage {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the language of the user
     * @var \Objects\UserBundle\Entity\User $user
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="languages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * the internship of the language
     * @Assert\NotNull(groups={"language", "Default"})
     * @var \Objects\InternJumpBundle\Entity\Language $language
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Language", inversedBy="users")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $language;

    /**
     * @var string $spokenFluency
     * @Assert\NotNull(groups={"language", "Default"})
     * @ORM\Column(name="spokenFluency", type="string", length=255)
     */
    private $spokenFluency;

    /**
     * @var string $writtenFluency
     * @Assert\NotNull(groups={"language", "Default"})
     * @ORM\Column(name="writtenFluency", type="string", length=255)
     */
    private $writtenFluency;

    /**
     * @var string $readFluency
     * @Assert\NotNull(groups={"language", "Default"})
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
     * Set language
     *
     * @param Objects\InternJumpBundle\Entity\Language $language
     */
    public function setLanguage(\Objects\InternJumpBundle\Entity\Language $language = null) {
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

    public function getValidLanguagesOptions() {
        return array(
            'None' => 'None',
            'Novice' => 'Novice',
            'Intermediate' => 'Intermediate',
            'Advanced' => 'Advanced'
        );
    }

}
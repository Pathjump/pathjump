<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objects\InternJumpBundle\Entity\PersonalQuestion
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\PersonalQuestionRepository")
 */
class PersonalQuestion
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
     * @var text $question
     * @Assert\NotNull
     * @ORM\Column(name="question", type="text")
     */
    private $question;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $personalQuestionAnswers
     * @ORM\OneToMany(targetEntity="PersonalQuestionAnswer", mappedBy="question", cascade={"remove"}, orphanRemoval=true)
     */
    private $personalQuestionAnswers;

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
     * Set question
     *
     * @param text $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * Get question
     *
     * @return text 
     */
    public function getQuestion()
    {
        return $this->question;
    }
    public function __construct()
    {
        $this->personalQuestionAnswers = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add personalQuestionAnswers
     *
     * @param Objects\InternJumpBundle\Entity\PersonalQuestionAnswer $personalQuestionAnswers
     */
    public function addPersonalQuestionAnswer(\Objects\InternJumpBundle\Entity\PersonalQuestionAnswer $personalQuestionAnswers)
    {
        $this->personalQuestionAnswers[] = $personalQuestionAnswers;
    }

    /**
     * Get personalQuestionAnswers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPersonalQuestionAnswers()
    {
        return $this->personalQuestionAnswers;
    }
}
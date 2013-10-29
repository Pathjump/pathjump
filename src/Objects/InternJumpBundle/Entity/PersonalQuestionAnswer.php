<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objects\InternJumpBundle\Entity\PersonalQuestionAnswer
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\PersonalQuestionAnswerRepository")
 */
class PersonalQuestionAnswer
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
     * @var text $answer
     * @Assert\NotNull
     * @ORM\Column(name="answer", type="text")
     */
    private $answer;

    /**
     * @var \Objects\UserBundle\Entity\User $user
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="personalQuestionAnswers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;
    
    /**
     * @var PersonalQuestion $question
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="PersonalQuestion", inversedBy="personalQuestionAnswers")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $question;

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
     * Set answer
     *
     * @param text $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;
    }

    /**
     * Get answer
     *
     * @return text 
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Set user
     *
     * @param Objects\UserBundle\Entity\User $user
     */
    public function setUser(\Objects\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Objects\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set question
     *
     * @param Objects\InternJumpBundle\Entity\PersonalQuestion $question
     */
    public function setQuestion(\Objects\InternJumpBundle\Entity\PersonalQuestion $question)
    {
        $this->question = $question;
    }

    /**
     * Get question
     *
     * @return Objects\InternJumpBundle\Entity\PersonalQuestion 
     */
    public function getQuestion()
    {
        return $this->question;
    }
}
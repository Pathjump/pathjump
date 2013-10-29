<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Quiz
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\QuizRepository")
 * @UniqueEntity(fields={"question"})
 */
class Quiz {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the answers of the quiz
     * @var \Doctrine\Common\Collections\ArrayCollection $answers
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\QuizAnswer", mappedBy="quiz", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $answers;

    /**
     * @var string $question
     * @Assert\NotNull
     * @ORM\Column(name="question", type="string", length=255, unique=true)
     */
    private $question;

    public function __construct() {
        $this->answers = new ArrayCollection();
    }

    public function __toString() {
        return $this->question;
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
     * Set question
     *
     * @param string $question
     */
    public function setQuestion($question) {
        $this->question = $question;
    }

    /**
     * Get question
     *
     * @return string 
     */
    public function getQuestion() {
        return $this->question;
    }

    /**
     * Add answers
     *
     * @param Objects\InternJumpBundle\Entity\QuizAnswer $answers
     */
    public function addQuizAnswer(\Objects\InternJumpBundle\Entity\QuizAnswer $answers) {
        $this->answers[] = $answers;
    }

    /**
     * Add answers
     * alias for addQuizAnswer required by sonata
     * @param Objects\InternJumpBundle\Entity\QuizAnswer $answers
     */
    public function addAnswers(\Objects\InternJumpBundle\Entity\QuizAnswer $answers) {
        $this->addQuizAnswer($answers);
    }

    /**
     * Get answers
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getAnswers() {
        return $this->answers;
    }

    /**
     * this function is required in the sonata backend to set the quiz object to it is answers
     * @author Mahmoud
     */
    public function setRequiredData() {
        $answers = $this->getAnswers();
        foreach ($answers as $answer) {
            $answer->setQuiz($this);
        }
    }

}
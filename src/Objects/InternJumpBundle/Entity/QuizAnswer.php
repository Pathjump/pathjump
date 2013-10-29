<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\QuizAnswer
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\QuizAnswerRepository")
 */
class QuizAnswer {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the quiz of the answer
     * @var \Objects\InternJumpBundle\Entity\Quiz $quiz
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Quiz", inversedBy="answers")
     * @ORM\JoinColumn(name="quiz_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $quiz;

    /**
     * @var string $answer
     * @Assert\NotNull
     * @ORM\Column(name="answer", type="string", length=255)
     */
    private $answer;

    /**
     * @var integer $score
     * @Assert\NotNull
     * @ORM\Column(name="score", type="integer")
     */
    private $score;

    public function __toString() {
        return "$this->answer $this->id";
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
     * Set answer
     *
     * @param string $answer
     */
    public function setAnswer($answer) {
        $this->answer = $answer;
    }

    /**
     * Get answer
     *
     * @return string 
     */
    public function getAnswer() {
        return $this->answer;
    }

    /**
     * Set score
     *
     * @param integer $score
     */
    public function setScore($score) {
        $this->score = $score;
    }

    /**
     * Get score
     *
     * @return integer 
     */
    public function getScore() {
        return $this->score;
    }

    /**
     * Set quiz
     *
     * @param Objects\InternJumpBundle\Entity\Quiz $quiz
     */
    public function setQuiz(\Objects\InternJumpBundle\Entity\Quiz $quiz) {
        $this->quiz = $quiz;
    }

    /**
     * Get quiz
     *
     * @return Objects\InternJumpBundle\Entity\Quiz 
     */
    public function getQuiz() {
        return $this->quiz;
    }

}
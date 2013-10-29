<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\CompanyQuestion
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\CompanyQuestionRepository")
 */
class CompanyQuestion {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the company of the question
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company", inversedBy="questions")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * the user asked the question
     * @var \Objects\UserBundle\Entity\User $user
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="companiesQuestions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var text $question
     * @Assert\NotNull
     * @ORM\Column(name="question", type="text")
     */
    private $question;

    /**
     * @var text $answer
     *
     * @ORM\Column(name="answer", type="text", nullable=true)
     */
    private $answer;

    /**
     * @var boolean $showOnCV
     *
     * @ORM\Column(name="showOnCV", type="boolean")
     */
    private $showOnCV = TRUE;
    
    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    public function __construct() {
        $this->createdAt = new \DateTime();
    }
    
    public function __toString() {
        return "question $this->id";
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
     * @param text $question
     */
    public function setQuestion($question) {
        $this->question = $question;
    }

    /**
     * Get question
     *
     * @return text 
     */
    public function getQuestion() {
        return $this->question;
    }

    /**
     * Set answer
     *
     * @param text $answer
     */
    public function setAnswer($answer) {
        $this->answer = $answer;
    }

    /**
     * Get answer
     *
     * @return text 
     */
    public function getAnswer() {
        return $this->answer;
    }

    /**
     * Set showOnCV
     *
     * @param boolean $showOnCV
     */
    public function setShowOnCV($showOnCV) {
        $this->showOnCV = $showOnCV;
    }

    /**
     * Get showOnCV
     *
     * @return boolean 
     */
    public function getShowOnCV() {
        return $this->showOnCV;
    }

    /**
     * Set company
     *
     * @param Objects\InternJumpBundle\Entity\Company $company
     */
    public function setCompany(\Objects\InternJumpBundle\Entity\Company $company) {
        $this->company = $company;
    }

    /**
     * Get company
     *
     * @return Objects\InternJumpBundle\Entity\Company 
     */
    public function getCompany() {
        return $this->company;
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
     * Set createdAt
     *
     * @param date $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return date 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
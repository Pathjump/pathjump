<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Note
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\NoteRepository")
 */
class Note {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the task of the note
     * @var \Objects\InternJumpBundle\Entity\Task $task
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Task", inversedBy="notes")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $task;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var text $note
     *
     * @ORM\Column(name="note", type="text")
     */
    private $note;

    /**
     * @var boolean $type
     * 
     * @ORM\Column(name="type", type="boolean") 
     */
    private $type;
    
    public function __toString() {
        return "Note $this->id";
    }

    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get createdAt
     *
     * @return date 
     */
    public function getCreatedAt() {
        return $this->createdAt;
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
     * Set note
     *
     * @param text $note
     */
    public function setNote($note) {
        $this->note = $note;
    }

    /**
     * Get note
     *
     * @return text 
     */
    public function getNote() {
        return $this->note;
    }

    /**
     * Set task
     *
     * @param Objects\InternJumpBundle\Entity\Task $task
     */
    public function setTask(\Objects\InternJumpBundle\Entity\Task $task) {
        $this->task = $task;
    }

    /**
     * Get task
     *
     * @return Objects\InternJumpBundle\Entity\Task 
     */
    public function getTask() {
        return $this->task;
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
     * Set type
     *
     * @param boolean $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return boolean 
     */
    public function getType()
    {
        return $this->type;
    }
}
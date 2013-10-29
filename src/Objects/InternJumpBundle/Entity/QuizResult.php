<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objects\InternJumpBundle\Entity\QuizResult
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table()
 * @ORM\Entity
 */
class QuizResult
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
     * @var integer $score
     *
     * @ORM\Column(name="score", type="integer")
     */
    private $score;

    /**
     * @var string $message
     *
     * @ORM\Column(name="message", type="string", length=255)
     */
    private $message;
    
    /**
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=255)
     */
    private $image = 'default';

    /**
     * a temp variable for storing the old image name to delete the old image after the update
     * @var string $temp
     */
    private $temp;

    /**
     * this flag is for detecting if the image has been handled
     * @var boolean $imageHandeled
     */
    private $imageHandeled = FALSE;

    /**
     * @Assert\Image
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public $file;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __toString() {
        return $this->message;
    }

    /**
     * this function is used to delete the current image
     */
    public function removeImage() {
        //check if we have an old image
        if ($this->image) {
            //store the old name to delete the image on the upadate
            $this->temp = $this->image;
            //delete the current image
            $this->setImage(NULL);
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (NULL !== $this->file && !$this->imageHandeled) {
            //get the image extension
            $extension = $this->file->guessExtension();
            //generate a random image name
            $img = uniqid();
            //get the image upload directory
            $uploadDir = $this->getUploadRootDir();
            //check if the upload directory exists
            if (!@is_dir($uploadDir)) {
                //get the old umask
                $oldumask = umask(0);
                //not a directory probably the first time for this category try to create the directory
                $success = @mkdir($uploadDir, 0755, TRUE);
                //reset the umask
                umask($oldumask);
                //check if we created the folder
                if (!$success) {
                    //could not create the folder throw an exception to stop the insert
                    throw new \Exception("Can not create the image directory $uploadDir");
                }
            }
            //check that the file name does not exist
            while (@file_exists("$uploadDir/$img.$extension")) {
                //try to find a new unique name
                $img = uniqid();
            }
            //check if we have an old image
            if ($this->image) {
                //store the old name to delete the image on the upadate
                $this->temp = $this->image;
            }
            //set the image new name
            $this->image = "$img.$extension";
            //set the flag to indecate that the image has been handled
            $this->imageHandeled = TRUE;
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (NULL !== $this->file) {
            // you must throw an exception here if the file cannot be moved
            // so that the entity is not persisted to the database
            // which the UploadedFile move() method does
            $this->file->move($this->getUploadRootDir(), $this->image);
            //remove the file as you do not need it any more
            $this->file = NULL;
        }
        //check if we have an old image
        if ($this->temp) {
            //try to delete the old image
            @unlink($this->getUploadRootDir() . '/' . $this->temp);
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function postRemove() {
        //check if we have an image
        if ($this->image) {
            //try to delete the image
            @unlink($this->getAbsolutePath());
        }
    }

    /**
     * @return string the path of image starting of root
     */
    public function getAbsolutePath() {
        return $this->getUploadRootDir() . '/' . $this->image;
    }

    /**
     * @return string the relative path of image starting from web directory 
     */
    public function getWebPath() {
        return NULL === $this->image ? null : '/' . $this->getUploadDir() . '/' . $this->image;
    }

    /**
     * @return string the path of upload directory starting of root
     */
    public function getUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    /**
     * @author Mahmoud
     * @param $width the desired image width
     * @param $height the desired image height
     * @return string the htaccess file url pattern which map to timthumb url
     */
    public function getTimThumbUrl($width = 50, $height = 50) {
        return NULL === $this->image ? null : "/quiz-result-image/$width/$height/$this->image";
    }

    /**
     * @return string the document upload directory path starting from web folder
     */
    private function getUploadDir() {
        return 'images/quiz-results-images';
    }
    
    /**
     * Set score
     *
     * @param integer $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * Get score
     *
     * @return integer 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set message
     *
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    
    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text 
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    
    /**
     * Set image
     *
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }
}
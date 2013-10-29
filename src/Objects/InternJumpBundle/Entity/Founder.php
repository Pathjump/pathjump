<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objects\InternJumpBundle\Entity\Founder
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\FounderRepository")
 */
class Founder
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
     * @var string $founderName
     *
     * @ORM\Column(name="founderName", type="string", length=255)
     */
    private $founderName;

    /**
     * @var text $founderEmail
     * @Assert\Email
     * @ORM\Column(name="founderEmail", type="text")
     */
    private $founderEmail;

    /**
     * @var string $founderImage
     *
     * @ORM\Column(name="founderImage", type="string", length=255)
     */
    private $founderImage;

    /**
     * @var text $founderPosition
     *
     * @ORM\Column(name="founderPosition", type="text")
     */
    private $founderPosition;


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
     * Set founderName
     *
     * @param string $founderName
     */
    public function setFounderName($founderName)
    {
        $this->founderName = $founderName;
    }

    /**
     * Get founderName
     *
     * @return string 
     */
    public function getFounderName()
    {
        return $this->founderName;
    }

    /**
     * Set founderEmail
     *
     * @param text $founderEmail
     */
    public function setFounderEmail($founderEmail)
    {
        $this->founderEmail = $founderEmail;
    }

    /**
     * Get founderEmail
     *
     * @return text 
     */
    public function getFounderEmail()
    {
        return $this->founderEmail;
    }

    /**
     * Set founderImage
     *
     * @param string $founderImage
     */
    public function setFounderImage($founderImage)
    {
        $this->founderImage = $founderImage;
    }

    /**
     * Get founderImage
     *
     * @return string 
     */
    public function getFounderImage()
    {
        return $this->founderImage;
    }

    /**
     * Set founderPosition
     *
     * @param text $founderPosition
     */
    public function setFounderPosition($founderPosition)
    {
        $this->founderPosition = $founderPosition;
    }

    /**
     * Get founderPosition
     *
     * @return text 
     */
    public function getFounderPosition()
    {
        return $this->founderPosition;
    }
    
    
      /****************file upload********************/
    /**********************************************/
    
    /**
     * @Assert\Image
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public $file;
    
    /**
     *
     * a temp variable  for storing the old image name to delete the old image after the update
     * @var string $temp
     */
    private $temp;
    
    /**
     * this flag is for detecting if the image has been handled
     * @var boolean $imageHandeled
     */
    private $imageHandeled = FALSE;
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (NULL !== $this->file && !$this->imageHandeled) {
            //get the slideImage extension
            $extension = $this->file->guessExtension();
            //generate a random slideImage name
            $img = uniqid();
            //get the slideImage upload directory
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
                    throw new \Exception("Can not create the slideImage directory $uploadDir");
                }
            }
            //check that the file name does not exist
            while (@file_exists("$uploadDir/$img.$extension")) {
                //try to find a new unique name
                $img = uniqid();
            }
            //check if we have an old founderImage
            if ($this->founderImage) {
                //store the old name to delete the image on the upadate
                $this->temp = $this->founderImage;
            }
            //set the image new name
            $this->founderImage = "$img.$extension";
            //set the flag to indecate that the image has been handled
            $this->imageHandeled = TRUE;
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload() {
        if (NULL === $this->file) {
            return;
        }
        // you must throw an exception here if the file cannot be moved
        // so that the entity is not persisted to the database
        // which the UploadedFile move() method does
        $this->file->move($this->getUploadRootDir(), $this->founderImage);
        //remove the file as you do not need it any more
        $this->file = NULL;
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
        if ($this->founderImage) {
            //try to delete the image
            @unlink($this->getAbsolutePath());
        }
    }

    /**
     * @return string the path of image starting of root
     */
    public function getAbsolutePath() {
        return $this->getUploadRootDir() . '/' . $this->founderImage;
    }

    /**
     * @return string the relative path of image starting from web directory 
     */
    public function getWebPath() {
        return NULL === $this->founderImage ? NULL : '/' . $this->getUploadDir() . '/' . $this->founderImage;
    }

    /**
     * @return string the path of upload directory starting of root
     */
    public function getUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    /**
     * @param $width the desired image width
     * @param $height the desired image height
     * @return string the htaccess file url pattern which map to timthumb url
     */
    public function getTimThumbUrl($width = 50, $height = 50) {
        return NULL === $this->founderImage ? NULL : "/founders-Image/$width/$height/$this->founderImage";
    }

    /**
     * @return string the document upload directory path starting from web folder
     */
    public function getUploadDir() {
        return 'images/founders-Images';
    }

    
    /***************end file upload****************/
   /**********************************************/
    
    
    /**Tostring**/
    public function __toString() {
        return $this->founderImage;
    }
}
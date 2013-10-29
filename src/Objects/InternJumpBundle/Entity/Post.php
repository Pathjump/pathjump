<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Validator\Constraints as Unique;

/**
 * Objects\InternJumpBundle\Entity\Post
 * @Unique\UniqueEntity(fields={"slug"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\PostRepository")
 */
class Post
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
     * @var string $postTitle
     *
     * @ORM\Column(name="postTitle", type="string", length=255)
     */
    private $postTitle;

    /**
     * @var text $postBody
     *
     * @ORM\Column(name="postBody", type="text")
     */
    private $postBody;

    /**
     * @var string $postImage
     *
     * @ORM\Column(name="postImage", type="string", length=255,nullable=true)
     */
    private $postImage;

    /**
     * @var datetime $createdAt
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var boolean $isPublished
     *
     * @ORM\Column(name="isPublished", type="boolean")
     */
    private $isPublished= true;

    /**
     * @var string $slug
     * @var string $slug
     * @Assert\NotNull
     * @Assert\Regex(pattern="/^[\w-]+$/u", message="Only characters, numbers and _")
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;


    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $categories
     * @Assert\NotNull
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\postCategory")
     * @ORM\JoinTable(name="post_category",
     *     joinColumns={@ORM\JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $categories;

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
     * Set postTitle
     *
     * @param string $postTitle
     */
    public function setPostTitle($postTitle)
    {
        $this->postTitle = $postTitle;
    }

    /**
     * Get postTitle
     *
     * @return string
     */
    public function getPostTitle()
    {
        return $this->postTitle;
    }

    /**
     * Set postBody
     *
     * @param text $postBody
     */
    public function setPostBody($postBody)
    {
        $this->postBody = $postBody;
    }

    /**
     * Get postBody
     *
     * @return text
     */
    public function getPostBody()
    {
        return $this->postBody;
    }

    /**
     * Set postImage
     *
     * @param string $postImage
     */
    public function setPostImage($postImage)
    {
        $this->postImage = $postImage;
    }

    /**
     * Get postImage
     *
     * @return string
     */
    public function getPostImage()
    {
        return $this->postImage;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set isPublished
     *
     * @param boolean $isPublished
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
    }

    /**
     * Get isPublished
     *
     * @return boolean
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

        /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        // replace all non letters or digits by - then remove any extra white spaces
        $this->slug = trim(preg_replace('/\W+/u', '-', $slug));
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

   /**ToString**/
    public function __toString() {
        return (string) $this->postTitle;
    }

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->categories = new ArrayCollection();
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
            //generate a random postImage name
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
            if ($this->postImage) {
                //store the old name to delete the image on the upadate
                $this->temp = $this->postImage;
            }
            //set the image new name
            $this->postImage = "$img.$extension";
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
        $this->file->move($this->getUploadRootDir(), $this->postImage);
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
        if ($this->postImage) {
            //try to delete the image
            @unlink($this->getAbsolutePath());
        }
    }

    /**
     * @return string the path of image starting of root
     */
    public function getAbsolutePath() {
        return $this->getUploadRootDir() . '/' . $this->postImage;
    }

    /**
     * @return string the relative path of image starting from web directory
     */
    public function getWebPath() {
        return NULL === $this->postImage ? NULL : '/' . $this->getUploadDir() . '/' . $this->postImage;
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
        return NULL === $this->postImage ? NULL : "/posts-Images/$width/$height/$this->postImage";
    }

    /**
     * @return string the document upload directory path starting from web folder
     */
    public function getUploadDir() {
        return 'images/posts-Images';
    }


    /***************end file upload****************/
   /**********************************************/


    /**
     * Add categories
     *
     * @param Objects\InternJumpBundle\Entity\postCategory $categories
     */
    public function addpostCategory(\Objects\InternJumpBundle\Entity\postCategory $categories)
    {
        $this->categories[] = $categories;
    }

    /**
     * Get categories
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set categories
     *
     * @param Doctrine\Common\Collections\Collection $categories
     */
    public function setCategories($categories) {
        $this->categories = $categories;
    }
}
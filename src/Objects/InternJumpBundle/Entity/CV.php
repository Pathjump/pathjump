<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\CV
 *
 * @ORM\Table()
 * @Assert\Callback(methods={"isCategoriesCorrect"})
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\CVRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CV {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the cv's internships
     * @var \Doctrine\Common\Collections\ArrayCollection $internships
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\UserInternship", mappedBy="cv", cascade={"persist"})
     */
    private $internships;

    /**
     * the cv user
     * @var \Objects\UserBundle\Entity\User $user
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="cvs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * the cv skills
     * @var \Doctrine\Common\Collections\ArrayCollection $skills
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\Skill")
     * @ORM\JoinTable(name="cv_skills",
     *     joinColumns={@ORM\JoinColumn(name="cv_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="skill_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $skills;

    /**
     * the cv employment history
     * @var \Doctrine\Common\Collections\ArrayCollection $employmentHistory
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\EmploymentHistory")
     * @ORM\JoinTable(name="cv_employment_history",
     *     joinColumns={@ORM\JoinColumn(name="cv_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="employment_history_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $employmentHistory;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $categories
     * @Assert\NotNull(message="You must select at least one category")
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\CVCategory", inversedBy="cvs")
     * @ORM\JoinTable(name="cv_category",
     *     joinColumns={@ORM\JoinColumn(name="cv_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $categories;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    /**
     * @var integer $skillsPoints
     *
     * @ORM\Column(name="skillsPoints", type="integer")
     */
    private $skillsPoints = 0;

    /**
     * @var integer $employmentHistoryPoints
     *
     * @ORM\Column(name="employmentHistoryPoints", type="integer")
     */
    private $employmentHistoryPoints = 0;

    /**
     * @var integer $totalPoints
     *
     * @ORM\Column(name="totalPoints", type="integer")
     */
    private $totalPoints = 0;

    /**
     * @var integer $viewsCount
     *
     * @ORM\Column(name="viewsCount", type="integer")
     */
    private $viewsCount = 0;

    /**
     * @var text $objective
     * @Assert\NotNull
     * @ORM\Column(name="objective", type="text")
     */
    private $objective;

    /**
     * @var text $describeYourself
     * @ORM\Column(name="describeYourself", type="text", nullable=true)
     */
    private $describeYourself;

    /**
     * @var boolean $isActive
     * @ORM\Column(name="isActive", type="boolean")
     */
    private $isActive = False;

    /**
     * @var string $name
     * @Assert\NotNull
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string $cvFile
     *
     * @ORM\Column(name="cvFile", type="string", length=255, nullable=true)
     */
    private $cvFile;

    /**
     * a temp variable for storing the old image name to delete the old image after the update
     * @var string $temp
     */
    private $temp;

    /**
     * this flag is for detecting if the image has been handled
     * @var boolean $imageHandeled
     */
    private $cvFileHandeled = FALSE;

    /**
     * @Assert\File(groups={"cvFile"},mimeTypes = {"application/vnd.ms-office","application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", " application/rtf"})
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public $file;

    public function __toString() {
        return $this->name;
    }

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->categories = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->employmentHistory = new ArrayCollection();
        $this->internships = new ArrayCollection();
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
     * Set viewsCount
     *
     * @param integer $viewsCount
     */
    public function setViewsCount($viewsCount) {
        $this->viewsCount = $viewsCount;
    }

    /**
     * Get viewsCount
     *
     * @return integer
     */
    public function getViewsCount() {
        return $this->viewsCount;
    }

    /**
     * Set objective
     *
     * @param text $objective
     */
    public function setObjective($objective) {
        $this->objective = $objective;
    }

    /**
     * Get objective
     *
     * @return text
     */
    public function getObjective() {
        return $this->objective;
    }

    /**
     * Add internships
     *
     * @param Objects\InternJumpBundle\Entity\UserInternship $internships
     */
    public function addUserInternship(\Objects\InternJumpBundle\Entity\UserInternship $internships) {
        $this->internships[] = $internships;
    }

    /**
     * Get internships
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getInternships() {
        return $this->internships;
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
     * Add skills
     *
     * @param Objects\InternJumpBundle\Entity\Skill $skills
     */
    public function addSkill(\Objects\InternJumpBundle\Entity\Skill $skills) {
        $this->skills[] = $skills;
    }

    /**
     * Set skills
     *
     * @return Doctrine\Common\Collections\Collection $skills
     */
    public function setSkills($skills) {
        $this->skills = $skills;
    }

    /**
     * Get skills
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSkills() {
        return $this->skills;
    }

    /**
     * Add employmentHistory
     *
     * @param Objects\InternJumpBundle\Entity\EmploymentHistory $employmentHistory
     */
    public function addEmploymentHistory(\Objects\InternJumpBundle\Entity\EmploymentHistory $employmentHistory) {
        $this->employmentHistory[] = $employmentHistory;
    }

    /**
     * Set employmentHistory
     *
     * @return Doctrine\Common\Collections\Collection $employmentHistories
     */
    public function setEmploymentHistory($employmentHistories) {
        $this->employmentHistory = $employmentHistories;
    }

    /**
     * Get employmentHistory
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getEmploymentHistory() {
        return $this->employmentHistory;
    }

    /**
     * Add categories
     *
     * @param Objects\InternJumpBundle\Entity\CVCategory $categories
     */
    public function addCVCategory(\Objects\InternJumpBundle\Entity\CVCategory $categories) {
        $this->categories[] = $categories;
    }

    public function setCategories($categories) {
        foreach ($categories as $category) {
            $this->addCVCategory($category);
        }
    }

    /**
     * Get categories
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCategories() {
        return $this->categories;
    }

    /**
     * Set describeYourself
     *
     * @param text $describeYourself
     */
    public function setDescribeYourself($describeYourself) {
        $this->describeYourself = $describeYourself;
    }

    /**
     * Get describeYourself
     *
     * @return text
     */
    public function getDescribeYourself() {
        return $this->describeYourself;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive() {
        return $this->isActive;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set skillsPoints
     *
     * @param integer $skillsPoints
     */
    public function setSkillsPoints($skillsPoints) {
        $this->skillsPoints = $skillsPoints;
    }

    /**
     * Get skillsPoints
     *
     * @return integer
     */
    public function getSkillsPoints() {
        return $this->skillsPoints;
    }

    /**
     * Set employmentHistoryPoints
     *
     * @param integer $employmentHistoryPoints
     */
    public function setEmploymentHistoryPoints($employmentHistoryPoints) {
        $this->employmentHistoryPoints = $employmentHistoryPoints;
    }

    /**
     * Get employmentHistoryPoints
     *
     * @return integer
     */
    public function getEmploymentHistoryPoints() {
        return $this->employmentHistoryPoints;
    }

    /**
     * Set totalPoints
     *
     * @param integer $totalPoints
     */
    public function setTotalPoints() {
        $this->totalPoints = $this->getSkillsPoints() + $this->getEmploymentHistoryPoints() + $this->getUser()->getEducationsPoints();
    }

    /**
     * Get totalPoints
     *
     * @return integer
     */
    public function getTotalPoints() {
        return $this->totalPoints;
    }

    /**
     * this function will check if the cv has more than one category
     * @author Mahmoud
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isCategoriesCorrect(ExecutionContext $context) {
        if ($this->categories && count($this->categories) == 0) {
            $propertyPath = $context->getPropertyPath() . '.categories';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('You must select at least one category', array(), NULL);
        }
    }

    /**
     * Set cvFile
     *
     * @param string $cvFile
     */
    public function setCvFile($cvFile) {
        $this->cvFile = $cvFile;
    }

    /**
     * Get cvFile
     *
     * @return string
     */
    public function getCvFile() {
        return $this->cvFile;
    }

    /**
     * this function is used to delete the current cv file
     */
    public function removeCVFile() {
        //check if we have an old image
        if ($this->cvFile) {
            //store the old name to delete the image on the upadate
            $this->temp = $this->cvFile;
            //delete the current image
            $this->setCvFile(NULL);
        }
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload() {
        if (NULL !== $this->file && !$this->cvFileHandeled) {
            //get the image extension
            $extension = 'doc';
            //generate a random image name
            $cvFile = uniqid();
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
                    throw new \Exception("Can not create the cv file directory $uploadDir");
                }
            }
            //check that the file name does not exist
            while (@file_exists("$uploadDir/$cvFile.$extension")) {
                //try to find a new unique name
                $cvFile = uniqid();
            }
            //check if we have an old image
            if ($this->cvFile) {
                //store the old name to delete the image on the upadate
                $this->temp = $this->cvFile;
            }
            //set the image new name
            $this->cvFile = "$cvFile.$extension";
            //set the flag to indecate that the image has been handled
            $this->cvFileHandeled = TRUE;
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
            $this->file->move($this->getUploadRootDir(), $this->cvFile);
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
        if ($this->cvFile) {
            //try to delete the image
            @unlink($this->getAbsolutePath());
        }
    }

    /**
     * @return string the path of image starting of root
     */
    public function getAbsolutePath() {
        return $this->getUploadRootDir() . '/' . $this->cvFile;
    }

    /**
     * @return string the relative path of image starting from web directory
     */
    public function getWebPath() {
        return NULL === $this->cvFile ? NULL : '/' . $this->getUploadDir() . '/' . $this->cvFile;
    }

    /**
     * @return string the path of upload directory starting of root
     */
    public function getUploadRootDir() {
        // the absolute directory path where uploaded documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    /**
     * @return string the document upload directory path starting from web folder
     */
    private function getUploadDir() {
        return 'cvFiles';
    }

}
<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Objects\InternJumpBundle\Validator\Constrains\GlobalUnique;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Locale\Locale;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Company
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\CompanyRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"name"}, groups={"signup", "edit", "adminsignup", "adminedit"})
 * @UniqueEntity(fields={"loginName"}, groups={"loginName", "adminsignup", "adminedit"})
 * @UniqueEntity(fields={"email"}, groups={"signup", "edit", "adminsignup", "adminedit"})
 * @GlobalUnique(groups={"signup", "edit", "adminsignup", "adminedit"})
 */
class Company implements AdvancedUserInterface {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $companyRoles
     * @ORM\ManyToMany(targetEntity="\Objects\UserBundle\Entity\Role")
     * @ORM\JoinTable(name="company_role",
     *     joinColumns={@ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $companyRoles;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $categories
     * @Assert\NotNull(message="You must select at least one category")
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\CVCategory")
     * @ORM\JoinTable(name="company_category",
     *     joinColumns={@ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $professions;

    /**
     * @ORM\ManyToMany(targetEntity="\Objects\UserBundle\Entity\User")
     * @ORM\JoinTable(name="favorite_user",
     *     joinColumns={@ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $favoriteUsers;

    /**
     * the tasks of the company
     * @var \Doctrine\Common\Collections\ArrayCollection $tasks
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Task", mappedBy="company", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $tasks;

    /**
     * the messages of the company
     * @var \Doctrine\Common\Collections\ArrayCollection $messages
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Message", mappedBy="company", cascade={"remove"}, orphanRemoval=true)
     */
    private $messages;

    /**
     * the company's notifications
     * @var \Doctrine\Common\Collections\ArrayCollection $companyNotifications
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\CompanyNotification", mappedBy="company", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $companyNotifications;

    /**
     * the user's notifications
     * @var \Doctrine\Common\Collections\ArrayCollection $userNotifications
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\UserNotification", mappedBy="company", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $userNotifications;

    /**
     * the company's questions
     * @var \Doctrine\Common\Collections\ArrayCollection $questions
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\CompanyQuestion", mappedBy="company", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $questions;

    /**
     * the company's interests
     * @var \Doctrine\Common\Collections\ArrayCollection $interests
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Interest", mappedBy="company", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $interests;

    /**
     * the company's interviews
     * @var \Doctrine\Common\Collections\ArrayCollection $interviews
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Interview", mappedBy="company", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $interviews;

    /**
     * the company's $internships
     * @var \Doctrine\Common\Collections\ArrayCollection $internships
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Internship", mappedBy="company", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $internships;

    /**
     * @var string $name
     * @Assert\NotNull(groups={"signup", "edit", "adminsignup", "adminedit"})
     * @Assert\MinLength(limit=2, groups={"signup", "edit", "adminsignup", "adminedit"})
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string $loginName
     *
     * @ORM\Column(name="loginName", type="string", length=255, nullable=true, unique=true)
     * @Assert\NotNull(groups={"loginName", "adminsignup", "adminedit"})
     * @Assert\Regex(pattern="/^\w+$/u", groups={"loginName", "adminsignup", "adminedit"}, message="Only characters, numbers and _")
     */
    private $loginName;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string $userPassword
     * @Assert\MinLength(limit=6, groups={"signup", "password", "adminsignup"})
     * @Assert\NotNull(groups={"signup", "password", "adminsignup"})
     */
    private $userPassword;

    /**
     * @Assert\NotNull(groups={"oldPassword"})
     * @var string $oldPassword
     */
    private $oldPassword;

    /**
     * @var string $confirmationCode
     *
     * @ORM\Column(name="confirmationCode", type="string", length=64)
     */
    private $confirmationCode;

    /**
     * @var boolean $locked
     * @ORM\Column(name="locked", type="boolean")
     */
    private $locked = FALSE;

    /**
     * @var boolean $enabled
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled = TRUE;

    /**
     * @var boolean $isHome
     * @ORM\Column(name="isHome", type="boolean")
     */
    private $isHome = FALSE;

    /**
     * @var boolean $notification
     * @ORM\Column(name="notification", type="boolean")
     */
    private $notification = TRUE;

    /**
     * @ORM\Column(type="string", length="255")
     *
     * @var string salt
     */
    private $salt;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    /**
     * @var date $activatedAt
     *
     * @ORM\Column(name="activatedAt", type="date", nullable=true)
     */
    private $activatedAt;

    /**
     * @var string $country
     * @ORM\Column(name="country", type="string", length=255, nullable=true)
     */
    private $country;

    /**
     * @var string $city
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @var string $state
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=true)
     */
    private $state;

    /**
     * @var text $address
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    /**
     * @var date $establishmentDate
     * @ORM\Column(name="establishmentDate", type="date", nullable=true)
     */
    private $establishmentDate;

    /**
     * @var string $email
     * @Assert\NotNull(groups={"signup", "edit", "adminsignup", "adminedit"})
     * @Assert\Email(groups={"signup", "edit", "adminsignup", "adminedit"})
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string $telephone
     *
     * @ORM\Column(name="telephone", type="string", length=255, nullable=true)
     */
    private $telephone;

    /**
     * @var string $fax
     *
     * @ORM\Column(name="fax", type="string", length=255, nullable=true)
     */
    private $fax;

    /**
     * @var string $url
     * @Assert\Url(groups={"signup", "edit"})
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var string $facebookUrl
     * @Assert\Url(groups={"signup", "edit"})
     * @ORM\Column(name="facebookUrl", type="string", length=255, nullable=true)
     */
    private $facebookUrl;

    /**
     * @var string $twitterUrl
     * @Assert\Url(groups={"signup", "edit"})
     * @ORM\Column(name="twitterUrl", type="string", length=255, nullable=true)
     */
    private $twitterUrl;

    /**
     * @var string $googlePlusUrl
     * @Assert\Url(groups={"signup", "edit"})
     * @ORM\Column(name="googlePlusUrl", type="string", length=255, nullable=true)
     */
    private $googlePlusUrl;

    /**
     * @var string $linkedInUrl
     * @Assert\Url(groups={"signup", "edit"})
     * @ORM\Column(name="linkedInUrl", type="string", length=255, nullable=true)
     */
    private $linkedInUrl;

    /**
     * @var string $youtubeUrl
     * @Assert\Url(groups={"signup", "edit"})
     * @ORM\Column(name="youtubeUrl", type="string", length=255, nullable=true)
     */
    private $youtubeUrl;

    /**
     * @var string $zipcode
     * @Assert\MinLength(limit = "3", message = "zipcode must be {{ limit }} or more charcters", groups={"signup", "edit", "adminsignup", "adminedit"})
     * @ORM\Column(name="zipcode", type="string", length=10, nullable=true)
     */
    private $zipcode;

    /**
     * @var decimal $Latitude
     *
     * @ORM\Column(name="Latitude", type="decimal", precision=18, scale=12, nullable=true)
     */
    private $Latitude;

    /**
     * @var decimal $Longitude
     *
     * @ORM\Column(name="Longitude", type="decimal", precision=18, scale=12, nullable=true)
     */
    private $Longitude;

    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=20, nullable=true)
     */
    private $image;

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
     * @Assert\Image(maxSize = "1024k")
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public $file;

    public function __construct() {
        $this->confirmationCode = md5(uniqid(rand()));
        $this->salt = md5(time());
        $this->createdAt = new \DateTime();
        $this->tasks = new ArrayCollection();
        $this->companyRoles = new ArrayCollection();
        $this->professions = new ArrayCollection();
        $this->companyNotifications = new ArrayCollection();
        $this->userNotifications = new ArrayCollection();
        $this->questions = new ArrayCollection();
        $this->interests = new ArrayCollection();
        $this->interviews = new ArrayCollection();
        $this->internships = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->favoriteUsers = new ArrayCollection();
    }

    /**
     * this function is used by php to know which attributes to serialize
     * the returned array must not contain any one to one or one to many relation object
     * @return array
     */
    public function __sleep() {
        return array(
            'id', 'loginName', 'email', 'password', 'confirmationCode', 'establishmentDate',
            'createdAt', 'name', 'url', 'city', 'country', 'state', 'address', 'fax',
            'locked', 'enabled', 'salt', 'image', 'zipcode', 'Latitude', 'Longitude', 'telephone', 'youtubeUrl',
            'linkedInUrl', 'googlePlusUrl', 'twitterUrl', 'facebookUrl'
        );
    }

    public function __toString() {
        return $this->name;
    }

    /**
     * Set image
     *
     * @param string $image
     */
    public function setImage($image) {
        $this->image = $image;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage() {
        return $this->image;
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
        return NULL === $this->image ? '/img/company-default-img.jpg' : '/' . $this->getUploadDir() . '/' . $this->image;
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
        return NULL === $this->image ? "/default-company-logo/$width/$height" : "/company-logo/$width/$height/$this->image";
    }

    /**
     * @return string the document upload directory path starting from web folder
     */
    private function getUploadDir() {
        return 'images/companies-logos';
    }

    /**
     * this function will set a valid random password for the user
     */
    public function setRandomPassword() {
        $this->setUserPassword(rand());
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
     * Set country
     *
     * @param string $country
     */
    public function setCountry($country) {
        $this->country = $country;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry() {
        return $this->country;
    }

    /**
     * Set city
     *
     * @param string $city
     */
    public function setCity($city) {
        $this->city = $city;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity() {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     */
    public function setState($state) {
        $this->state = $state;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Set address
     *
     * @param text $address
     */
    public function setAddress($address) {
        $this->address = $address;
    }

    /**
     * Get address
     *
     * @return text
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Set establishmentDate
     *
     * @param date $establishmentDate
     */
    public function setEstablishmentDate($establishmentDate) {
        $this->establishmentDate = $establishmentDate;
    }

    /**
     * Get establishmentDate
     *
     * @return date
     */
    public function getEstablishmentDate() {
        return $this->establishmentDate;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * Set telephone
     *
     * @param string $telephone
     */
    public function setTelephone($telephone) {
        $this->telephone = $telephone;
    }

    /**
     * Get telephone
     *
     * @return string
     */
    public function getTelephone() {
        return $this->telephone;
    }

    /**
     * Set fax
     *
     * @param string $fax
     */
    public function setFax($fax) {
        $this->fax = $fax;
    }

    /**
     * Get fax
     *
     * @return string
     */
    public function getFax() {
        return $this->fax;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     */
    public function setZipcode($zipcode) {
        $this->zipcode = $zipcode;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode() {
        return $this->zipcode;
    }

    /**
     * Add companyRoles
     *
     * @param Objects\UserBundle\Entity\Role $companyRoles
     */
    public function addRole(\Objects\UserBundle\Entity\Role $companyRoles) {
        $this->companyRoles[] = $companyRoles;
    }

    /**
     * Get companyRoles
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCompanyRoles() {
        return $this->companyRoles;
    }

    /**
     * Set companyRoles
     *
     * @param Doctrine\Common\Collections\Collection $companyRoles
     */
    public function setCompanyRoles($companyRoles) {
        $this->companyRoles = $companyRoles;
    }

    /**
     * Add tasks
     *
     * @param Objects\InternJumpBundle\Entity\Task $tasks
     */
    public function addTask(\Objects\InternJumpBundle\Entity\Task $tasks) {
        $this->tasks[] = $tasks;
    }

    /**
     * Get tasks
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getTasks() {
        return $this->tasks;
    }

    /**
     * Add companyNotifications
     *
     * @param Objects\InternJumpBundle\Entity\CompanyNotification $companyNotifications
     */
    public function addCompanyNotification(\Objects\InternJumpBundle\Entity\CompanyNotification $companyNotifications) {
        $this->companyNotifications[] = $companyNotifications;
    }

    /**
     * Get companyNotifications
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCompanyNotifications() {
        return $this->companyNotifications;
    }

    /**
     * Add userNotifications
     *
     * @param Objects\InternJumpBundle\Entity\UserNotification $userNotifications
     */
    public function addUserNotification(\Objects\InternJumpBundle\Entity\UserNotification $userNotifications) {
        $this->userNotifications[] = $userNotifications;
    }

    /**
     * Get userNotifications
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getUserNotifications() {
        return $this->userNotifications;
    }

    /**
     * Add questions
     *
     * @param Objects\InternJumpBundle\Entity\CompanyQuestion $questions
     */
    public function addCompanyQuestion(\Objects\InternJumpBundle\Entity\CompanyQuestion $questions) {
        $this->questions[] = $questions;
    }

    /**
     * Get questions
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getQuestions() {
        return $this->questions;
    }

    /**
     * Add interests
     *
     * @param Objects\InternJumpBundle\Entity\Interest $interests
     */
    public function addInterest(\Objects\InternJumpBundle\Entity\Interest $interests) {
        $this->interests[] = $interests;
    }

    /**
     * Get interests
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getInterests() {
        return $this->interests;
    }

    /**
     * Add interviews
     *
     * @param Objects\InternJumpBundle\Entity\Interview $interviews
     */
    public function addInterview(\Objects\InternJumpBundle\Entity\Interview $interviews) {
        $this->interviews[] = $interviews;
    }

    /**
     * Get interviews
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getInterviews() {
        return $this->interviews;
    }

    /**
     * Add internships
     *
     * @param Objects\InternJumpBundle\Entity\Internship $internships
     */
    public function addInternship(\Objects\InternJumpBundle\Entity\Internship $internships) {
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
     * Set Latitude
     *
     * @param decimal $latitude
     */
    public function setLatitude($latitude) {
        $this->Latitude = $latitude;
    }

    /**
     * Get Latitude
     *
     * @return decimal
     */
    public function getLatitude() {
        return $this->Latitude;
    }

    /**
     * Set Longitude
     *
     * @param decimal $longitude
     */
    public function setLongitude($longitude) {
        $this->Longitude = $longitude;
    }

    /**
     * Get Longitude
     *
     * @return decimal
     */
    public function getLongitude() {
        return $this->Longitude;
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
     * Implementation of getRoles for the UserInterface.
     *
     * @return array An array of Roles
     */
    public function getRoles() {
        return $this->getCompanyRoles()->toArray();
    }

    /**
     * this function will set the valid password for the user
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setValidPassword() {
        //check if we have a password
        if ($this->getUserPassword()) {
            //hash the password
            $this->setPassword($this->hashPassword($this->getUserPassword()));
        } else {
            //check if the object is new
            if ($this->getId() === NULL) {
                //new object set a random password
                $this->setRandomPassword();
                //hash the password
                $this->setPassword($this->hashPassword($this->getUserPassword()));
            }
        }
    }

    /**
     * this function will hash a password and return the hashed value
     * the encoding has to be the same as the one in the project security.yml file
     * @param string $password the password to return it is hash
     */
    private function hashPassword($password) {
        //create an encoder object
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        //return the hashed password
        return $encoder->encodePassword($password, $this->getSalt());
    }

    /**
     * this function will check if the user entered a valid old password
     * @Assert\True(message = "the old password is wrong", groups={"oldPassword"})
     */
    public function isOldPasswordCorrect() {
        if ($this->hashPassword($this->getOldPassword()) == $this->getPassword()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Set oldPassword
     *
     * @param string $oldPassword
     */
    public function setOldPassword($oldPassword) {
        $this->oldPassword = $oldPassword;
    }

    /**
     * Get oldPassword
     *
     * @return string
     */
    public function getOldPassword() {
        return $this->oldPassword;
    }

    /**
     * Set userPassword
     *
     * @param string $password
     */
    public function setUserPassword($password) {
        $this->userPassword = $password;
    }

    /**
     * @return string the user password
     */
    public function getUserPassword() {
        return $this->userPassword;
    }

    /**
     * Implementation of eraseCredentials for the UserInterface.
     */
    public function eraseCredentials() {
        //remove the user password
        $this->setUserPassword(NULL);
        $this->setOldPassword(NULL);
    }

    /**
     * Implementation of equals for the UserInterface.
     * Compares this user to another to determine if they are the same.
     * @param UserInterface $user The user to compare with this user
     * @return boolean True if equal, false othwerwise.
     */
    public function equals(UserInterface $user) {
        return md5($this->getUserName()) == md5($user->getUserName());
    }

    /**
     * Implementation of getPassword for the UserInterface.
     * @return string the hashed user password
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Implementation of getSalt for the UserInterface.
     * @return string the user salt
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * Implementation of getUsername for the UserInterface.
     * check security.yml to know the used column by the firewall
     * @return string the user name used by the firewall configurations.
     */
    public function getUsername() {
        if ($this->loginName) {
            return $this->loginName;
        } else {
            return $this->email;
        }
    }

    /**
     * Implementation of isAccountNonExpired for the AdvancedUserInterface.
     * @return boolean
     */
    public function isAccountNonExpired() {
        return TRUE;
    }

    /**
     * Implementation of isCredentialsNonExpired for the AdvancedUserInterface.
     * @return boolean
     */
    public function isCredentialsNonExpired() {
        return TRUE;
    }

    /**
     * Implementation of isAccountNonLocked for the AdvancedUserInterface.
     * @return boolean
     */
    public function isAccountNonLocked() {
        return !$this->locked;
    }

    /**
     * Implementation of isEnabled for the AdvancedUserInterface.
     * @return boolean
     */
    public function isEnabled() {
        return $this->enabled;
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
     * Set loginName
     *
     * @param string $loginName
     */
    public function setLoginName($loginName) {
        // replace all non letters or digits by - then remove any extra white spaces
        $this->loginName = $loginName;
    }

    /**
     * Get loginName
     *
     * @return string
     */
    public function getLoginName() {
        return $this->loginName;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Set confirmationCode
     *
     * @param string $confirmationCode
     */
    public function setConfirmationCode($confirmationCode) {
        $this->confirmationCode = $confirmationCode;
    }

    /**
     * Get confirmationCode
     *
     * @return string
     */
    public function getConfirmationCode() {
        return $this->confirmationCode;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     */
    public function setLocked($locked) {
        $this->locked = $locked;
    }

    /**
     * Get locked
     *
     * @return boolean
     */
    public function getLocked() {
        return $this->locked;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     */
    public function setEnabled($enabled) {
        $this->enabled = $enabled;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled() {
        return $this->enabled;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt) {
        $this->salt = $salt;
    }

    /**
     * this function will check if the admin selected at least one role for the company
     * @Assert\True(message = "the company must have at least one role", groups={"adminsignup", "adminedit"})
     */
    public function isRolesCorrect() {
        if (count($this->companyRoles) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Add messages
     *
     * @param Objects\InternJumpBundle\Entity\Message $messages
     */
    public function addMessage(\Objects\InternJumpBundle\Entity\Message $messages) {
        $this->messages[] = $messages;
    }

    /**
     * Get messages
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * this function will return the user country name
     * @return NULL|string the country name
     */
    public function getCountryName() {
        //check if we have a country code
        if ($this->country) {
            //return the country name
            return Locale::getDisplayRegion('en_' . $this->country);
        }
        return NULL;
    }

    /**
     * Set isHome
     *
     * @param boolean $isHome
     */
    public function setIsHome($isHome) {
        $this->isHome = $isHome;
    }

    /**
     * Get isHome
     *
     * @return boolean
     */
    public function getIsHome() {
        return $this->isHome;
    }

    /**
     * Add professions
     *
     * @param Objects\InternJumpBundle\Entity\CVCategory $professions
     */
    public function addCVCategory(\Objects\InternJumpBundle\Entity\CVCategory $professions) {
        $this->professions[] = $professions;
    }

    /**
     * Get professions
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getProfessions() {
        return $this->professions;
    }

    /**
     * Set createdAt
     *
     * @param date $createdAt
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    /**
     * Set facebookUrl
     *
     * @param string $facebookUrl
     */
    public function setFacebookUrl($facebookUrl) {
        $this->facebookUrl = $facebookUrl;
    }

    /**
     * Get facebookUrl
     *
     * @return string
     */
    public function getFacebookUrl() {
        return $this->facebookUrl;
    }

    /**
     * Set twitterUrl
     *
     * @param string $twitterUrl
     */
    public function setTwitterUrl($twitterUrl) {
        $this->twitterUrl = $twitterUrl;
    }

    /**
     * Get twitterUrl
     *
     * @return string
     */
    public function getTwitterUrl() {
        return $this->twitterUrl;
    }

    /**
     * Set googlePlusUrl
     *
     * @param string $googlePlusUrl
     */
    public function setGooglePlusUrl($googlePlusUrl) {
        $this->googlePlusUrl = $googlePlusUrl;
    }

    /**
     * Get googlePlusUrl
     *
     * @return string
     */
    public function getGooglePlusUrl() {
        return $this->googlePlusUrl;
    }

    /**
     * Set linkedInUrl
     *
     * @param string $linkedInUrl
     */
    public function setLinkedInUrl($linkedInUrl) {
        $this->linkedInUrl = $linkedInUrl;
    }

    /**
     * Get linkedInUrl
     *
     * @return string
     */
    public function getLinkedInUrl() {
        return $this->linkedInUrl;
    }

    /**
     * Set youtubeUrl
     *
     * @param string $youtubeUrl
     */
    public function setYoutubeUrl($youtubeUrl) {
        $this->youtubeUrl = $youtubeUrl;
    }

    /**
     * Get youtubeUrl
     *
     * @return string
     */
    public function getYoutubeUrl() {
        return $this->youtubeUrl;
    }


    /**
     * Add favoriteUsers
     *
     * @param Objects\UserBundle\Entity\User $favoriteUsers
     */
    public function addUser(\Objects\UserBundle\Entity\User $favoriteUsers)
    {
        $this->favoriteUsers[] = $favoriteUsers;
    }

    /**
     * Get favoriteUsers
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getFavoriteUsers()
    {
        return $this->favoriteUsers;
    }

    /**
     * Set activatedAt
     *
     * @param date $activatedAt
     */
    public function setActivatedAt($activatedAt)
    {
        $this->activatedAt = $activatedAt;
    }

    /**
     * Get activatedAt
     *
     * @return date
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    /**
     * Set notification
     *
     * @param boolean $notification
     */
    public function setNotification($notification)
    {
        $this->notification = $notification;
    }

    /**
     * Get notification
     *
     * @return boolean
     */
    public function getNotification()
    {
        return $this->notification;
    }
}
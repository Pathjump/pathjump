<?php

namespace Objects\UserBundle\Entity;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Objects\InternJumpBundle\Validator\Constrains\GlobalUnique;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Locale\Locale;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\UserBundle\Entity\User
 *
 * @Assert\Callback(methods={"isDateOfBirthCorrect"}, groups={"dateOfBirth", "signup_second", "edit", "adminsignup", "adminedit"})
 * @UniqueEntity(fields={"loginName"}, groups={"loginName", "adminsignup", "adminedit"})
 * @UniqueEntity(fields={"email"}, groups={"signup", "email", "edit", "adminsignup", "adminedit"})
 * @GlobalUnique(groups={"signup", "edit", "adminsignup", "adminedit"})
 * @ORM\Table(indexes={@ORM\Index(name="search_user_name", columns={"loginName"})})
 * @ORM\Entity(repositoryClass="Objects\UserBundle\Entity\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @author Mahmoud
 */
class User implements AdvancedUserInterface {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\Company")
     * @ORM\JoinTable(name="favorite_company",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $favoriteComapnies;

    /**
     * @var integer $currentWorth
     *
     * @ORM\Column(name="currentWorth", type="integer")
     */
    private $currentWorth = 1;

    /**
     * @var integer $netWorth
     *
     * @ORM\Column(name="netWorth", type="integer")
     */
    private $netWorth = 0;

    /**
     * the required languages for the internship
     * @var \Doctrine\Common\Collections\ArrayCollection $languages
     * @Assert\Valid(groups={"language"})
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\UserLanguage", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $languages;

    /**
     * @var integer $educationsPoints
     *
     * @ORM\Column(name="educationsPoints", type="integer")
     */
    private $educationsPoints = 0;

    /**
     * @var integer $score
     * @ORM\Column(name="score", type="integer", nullable=true)
     */
    private $score;

    /**
     * the messages of the user
     * @var \Doctrine\Common\Collections\ArrayCollection $messages
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Message", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $messages;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $personalQuestionAnswers
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\PersonalQuestionAnswer", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $personalQuestionAnswers;

    /**
     * the companies interests of the user
     * @var \Doctrine\Common\Collections\ArrayCollection $companiesInterests
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Interest", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $companiesInterests;

    /**
     * the users
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\Skill", cascade={"persist"})
     * @ORM\JoinTable(name="user_skills",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="skill_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $skills;

    /**
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\CV", mappedBy="user",cascade={"remove", "persist"})
     */
    private $cvs;

    /**
     * @Assert\Valid(groups={"education"})
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Education", mappedBy="user",cascade={"remove", "persist"})
     */
    private $educations;

    /**
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\UserInternship", mappedBy="user",cascade={"remove", "persist"})
     */
    private $userInternships;

    /**
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\EmploymentHistory", mappedBy="user",cascade={"remove", "persist"})
     */
    private $employmentHistories;

    /**
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\CompanyQuestion", mappedBy="user",cascade={"remove", "persist"})
     */
    private $companiesQuestions;

    /**
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Task", mappedBy="user",cascade={"remove", "persist"})
     */
    private $tasks;

    /**
     * @ORM\OneToOne(targetEntity="\Objects\UserBundle\Entity\SocialAccounts", mappedBy="user",cascade={"remove", "persist"})
     */
    private $socialAccounts;

    /**
     * @ORM\ManyToMany(targetEntity="\Objects\UserBundle\Entity\Role")
     * @ORM\JoinTable(name="user_role",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     * @var \Doctrine\Common\Collections\ArrayCollection $userRoles
     */
    private $userRoles;

    /**
     * @var string $loginName
     *
     * @ORM\Column(name="loginName", type="string", length=255, nullable=true, unique=true)
     * @Assert\NotNull(groups={"loginName", "adminsignup", "adminedit"})
     * @Assert\Regex(pattern="/^\w+$/u", groups={"loginName", "adminsignup", "adminedit"}, message="Only characters, numbers and _")
     */
    private $loginName;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     * @Assert\NotNull(groups={"signup", "email", "edit", "adminsignup", "adminedit"})
     * @Assert\Email(groups={"signup", "email", "edit", "adminsignup", "adminedit"})
     */
    private $email;

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
     * @var datetime $lastSeen
     *
     * @ORM\Column(name="lastSeen", type="datetime")
     */
    private $lastSeen;

    /**
     * @var string $firstName
     * @Assert\MinLength(limit=2, groups={"signup_second", "firstname", "edit", "adminsignup", "adminedit"})
     * @Assert\NotNull(groups={"signup_second", "firstname", "edit", "adminsignup", "adminedit"})
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string $lastName
     * @Assert\MinLength(limit=2, groups={"signup_second", "lastname", "edit", "adminsignup", "adminedit"})
     * @Assert\NotNull(groups={"signup_second", "lastname_second", "edit", "adminsignup", "adminedit"})
     * @ORM\Column(name="lastName", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var text $about
     *
     * @ORM\Column(name="about", type="text", nullable=true)
     */
    private $about;

    /**
     * @var boolean $gender
     * 0 female, 1 male
     * @ORM\Column(name="gender", type="boolean", nullable=true)
     */
    private $gender;

    /**
     * @var boolean $matchingJobEmail
     * @ORM\Column(name="matchingJobEmail", type="boolean")
     */
    private $matchingJobEmail = 1;

    /**
     * @var date $dateOfBirth
     * @Assert\Date(groups={"signup_second", "adminsignup", "adminedit", "edit"})
     * @ORM\Column(name="dateOfBirth", type="date", nullable=true)
     */
    private $dateOfBirth;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     * @Assert\Url(groups={"signup_second", "adminsignup", "adminedit", "edit"})
     */
    private $url;

    /**
     * @var string $country
     * @Assert\NotNull(groups={"signup_second", "adminsignup", "adminedit", "edit"})
     * @ORM\Column(name="country", type="string", length=2, nullable=true)
     */
    private $country = 'US';

    /**
     * @var string $city
     * @Assert\NotNull(groups={"signup_second", "adminsignup", "adminedit", "edit"})
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
     * @Assert\NotNull(groups={"signup_second", "adminsignup", "adminedit", "edit"})
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    /**
     * @var string $zipcode
     * @Assert\MinLength(limit = "3", message = "zipcode must be {{ limit }} or more charcters", groups={"signup_second", "edit", "adminsignup", "adminedit"})
     * @ORM\Column(name="zipcode", type="string", length=10, nullable=true)
     */
    private $zipcode;

    /**
     * @var string $suggestedLanguage
     *
     * @ORM\Column(name="suggested_language", type="string", length=2, nullable=true)
     */
    private $suggestedLanguage = 'en';

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
     * @ORM\Column(type="string", length="255")
     *
     * @var string salt
     */
    private $salt;

    /**
     * @var string $image
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
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
     * @Assert\Image(groups={"image", "signup_second", "edit", "adminsignup", "adminedit"}, maxSize = "1024k")
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public $file;

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
        return NULL === $this->image ? '/img/default_user_image.jpg' : '/' . $this->getUploadDir() . '/' . $this->image;
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
        return NULL === $this->image ? '/img/default_user_image.jpg' : "/user-profile-image/$width/$height/$this->image";
    }

    /**
     * @return string the document upload directory path starting from web folder
     */
    private function getUploadDir() {
        return 'images/users-profiles-images';
    }

    /**
     * initialize the main default attributes
     */
    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->lastSeen = new \DateTime();
        $this->confirmationCode = md5(uniqid(rand()));
        $this->salt = md5(time());
        $this->userRoles = new ArrayCollection();
        $this->cvs = new ArrayCollection();
        $this->educations = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->personalQuestionAnswers = new ArrayCollection();
        $this->languages = new ArrayCollection();
    }

    /**
     * @return string the object name
     */
    public function __toString() {
        if ($this->firstName) {
            if ($this->lastName) {
                return "$this->firstName $this->lastName";
            } else {
                return $this->firstName;
            }
        } else {
            if ($this->loginName) {
                return $this->loginName;
            } else {
                return $this->email;
            }
        }
    }

    /**
     * this function is used by php to know which attributes to serialize
     * the returned array must not contain any one to one or one to many relation object
     * @return array
     */
    public function __sleep() {
        return array(
            'id', 'loginName', 'email', 'password', 'confirmationCode',
            'createdAt', 'lastSeen', 'firstName', 'lastName', 'about',
            'gender', 'dateOfBirth', 'url', 'country', 'city', 'state', 'address', 'suggestedLanguage',
            'locked', 'enabled', 'salt', 'image'
        );
    }

    /**
     * @return string the site map xml files folder name it has to be the same
     * as the show user profile route prefix
     */
    public function getSiteMapWebFolder() {
        return 'user';
    }

    /**
     * this function will set a valid random password for the user
     */
    public function setRandomPassword() {
        $this->setUserPassword(rand());
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
     * Implementation of getRoles for the UserInterface.
     *
     * @return array An array of Roles
     */
    public function getRoles() {
        return $this->getUserRoles()->toArray();
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
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set loginName
     *
     * @param string $loginName
     */
    public function setLoginName($loginName) {
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
     * Get createdAt
     *
     * @return date
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * Set about
     *
     * @param text $about
     */
    public function setAbout($about) {
        $this->about = $about;
    }

    /**
     * Get about
     *
     * @return text
     */
    public function getAbout() {
        return $this->about;
    }

    /**
     * Set gender
     *
     * @param boolean $gender
     */
    public function setGender($gender) {
        $this->gender = $gender;
    }

    /**
     * Get gender
     *
     * @return boolean
     */
    public function getGender() {
        return $this->gender;
    }

    /**
     * this function will return the string representing the user gender
     * @return string gender type
     */
    public function getGenderString() {
        if ($this->gender == NULL) {
            return 'unknown';
        }
        if ($this->gender == 0) {
            return 'female';
        }
        if ($this->gender == 1) {
            return 'male';
        }
    }

    /**
     * this function will return the user country name
     * @return NULL|string the country name
     */
    public function getCountryName() {
        //check if we have a country code
        if ($this->country) {
            //return the country name
            return Locale::getDisplayRegion($this->suggestedLanguage . '_' . $this->country);
        }
        return NULL;
    }

    /**
     * Set dateOfBirth
     *
     * @param date $dateOfBirth
     */
    public function setDateOfBirth($dateOfBirth) {
        $this->dateOfBirth = $dateOfBirth;
    }

    /**
     * Get dateOfBirth
     *
     * @return date
     */
    public function getDateOfBirth() {
        return $this->dateOfBirth;
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
     * Set suggestedLanguage
     *
     * @param string $suggestedLanguage
     */
    public function setSuggestedLanguage($suggestedLanguage) {
        $this->suggestedLanguage = $suggestedLanguage;
    }

    /**
     * Get suggestedLanguage
     *
     * @return string
     */
    public function getSuggestedLanguage() {
        return $this->suggestedLanguage;
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
     * Add userRoles
     *
     * @param Objects\UserBundle\Entity\Role $userRoles
     */
    public function addRole(\Objects\UserBundle\Entity\Role $userRoles) {
        if (!$this->userRoles->contains($userRoles)) {
            $this->userRoles[] = $userRoles;
        }
    }

    /**
     * Get userRoles
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getUserRoles() {
        return $this->userRoles;
    }

    public function setUserRoles($userRoles) {
        foreach ($userRoles as $userRole) {
            $this->addRole($userRole);
        }
    }

    /**
     * Set socialAccounts
     *
     * @param Objects\UserBundle\Entity\socialAccounts $socialAccounts
     */
    public function setSocialAccounts(\Objects\UserBundle\Entity\socialAccounts $socialAccounts) {
        $this->socialAccounts = $socialAccounts;
    }

    /**
     * Get socialAccounts
     *
     * @return Objects\UserBundle\Entity\socialAccounts
     */
    public function getSocialAccounts() {
        return $this->socialAccounts;
    }

    /**
     * Set lastSeen
     *
     * @param datetime $lastSeen
     */
    public function setLastSeen($lastSeen) {
        $this->lastSeen = $lastSeen;
    }

    /**
     * Get lastSeen
     *
     * @return datetime
     */
    public function getLastSeen() {
        return $this->lastSeen;
    }

    /**
     * Add skills
     *
     * @param Objects\InternJumpBundle\Entity\Skill $skills
     */
    public function addSkill(\Objects\InternJumpBundle\Entity\Skill $skills) {
        if (!$this->skills->contains($skills)) {
            $this->skills[] = $skills;
        }
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
     * Add cvs
     *
     * @param Objects\InternJumpBundle\Entity\CV $cvs
     */
    public function addCvs(\Objects\InternJumpBundle\Entity\CV $cvs) {
        $this->cvs[] = $cvs;
    }

    public function setCvs($cvs) {
        foreach ($cvs as $cv) {
            $this->addCvs($cv);
        }
    }

    /**
     * Get cvs
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCvs() {
        return $this->cvs;
    }

    /**
     * Add cvs
     *
     * @param Objects\InternJumpBundle\Entity\CV $cvs
     */
    public function addCV(\Objects\InternJumpBundle\Entity\CV $cvs) {
        $this->cvs[] = $cvs;
    }

    /**
     * Add educations
     *
     * @param Objects\InternJumpBundle\Entity\Education $educations
     */
    public function addEducation(\Objects\InternJumpBundle\Entity\Education $educations) {
        $this->educations[] = $educations;
    }

    /**
     * Add educations
     * alias for addEducation required by sonata
     * @param Objects\InternJumpBundle\Entity\Education $educations
     */
    public function addEducations(\Objects\InternJumpBundle\Entity\Education $educations) {
        $this->addEducation($educations);
    }

    /**
     * Get educations
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getEducations() {
        return $this->educations;
    }

    /**
     * Set educations
     *
     * @param Doctrine\Common\Collections\Collection $educations
     */
    public function setEducations($educations) {
        $this->educations = $educations;
        foreach ($educations as $education) {
            $education->setUser($this);
        }
    }

    /**
     * Add employmentHistories
     *
     * @param Objects\InternJumpBundle\Entity\EmploymentHistory $employmentHistories
     */
    public function addEmploymentHistory(\Objects\InternJumpBundle\Entity\EmploymentHistory $employmentHistories) {
        $this->employmentHistories[] = $employmentHistories;
    }

    /**
     * Add employmentHistories
     * alias for addEmploymentHistory used by sonata
     * @param Objects\InternJumpBundle\Entity\EmploymentHistory $employmentHistories
     */
    public function addEmploymentHistories(\Objects\InternJumpBundle\Entity\EmploymentHistory $employmentHistories) {
        $this->addEmploymentHistory($employmentHistories);
    }

    /**
     * Get employmentHistories
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getEmploymentHistories() {
        return $this->employmentHistories;
    }

    /**
     * Set employmentHistories
     *
     * @param Doctrine\Common\Collections\Collection
     */
    public function setEmploymentHistories($employmentHistories) {
        $this->employmentHistories = $employmentHistories;
        foreach ($employmentHistories as $employmentHistory) {
            $employmentHistory->setUser($this);
        }
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
     * this function will check if the admin selected at least one role for the user
     * @Assert\True(message = "the company must have at least one role", groups={"adminsignup", "adminedit"})
     */
    public function isRolesCorrect() {
        if (count($this->userRoles) > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * Add userInternships
     *
     * @param Objects\InternJumpBundle\Entity\UserInternship $userInternships
     */
    public function addUserInternship(\Objects\InternJumpBundle\Entity\UserInternship $userInternships) {
        $this->userInternships[] = $userInternships;
    }

    /**
     * Get userInternships
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getUserInternships() {
        return $this->userInternships;
    }

    /**
     * Add companiesQuestions
     *
     * @param Objects\InternJumpBundle\Entity\CompanyQuestion $companiesQuestions
     */
    public function addCompanyQuestion(\Objects\InternJumpBundle\Entity\CompanyQuestion $companiesQuestions) {
        $this->companiesQuestions[] = $companiesQuestions;
    }

    /**
     * Get companiesQuestions
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCompaniesQuestions() {
        return $this->companiesQuestions;
    }

    /**
     * this function is required by sonata to set the user default data
     */
    public function setRequiredData() {
        foreach ($this->getEducations() as $education) {
            $education->setUser($this);
        }
        foreach ($this->getEmploymentHistories() as $employmentHistory) {
            $employmentHistory->setUser($this);
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
     * Add companiesInterests
     *
     * @param Objects\InternJumpBundle\Entity\Interest $companiesInterests
     */
    public function addInterest(\Objects\InternJumpBundle\Entity\Interest $companiesInterests) {
        $this->companiesInterests[] = $companiesInterests;
    }

    /**
     * Get companiesInterests
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCompaniesInterests() {
        return $this->companiesInterests;
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
     * this function is used by symfony to validate date of birth
     * @author Mahmoud
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isDateOfBirthCorrect(ExecutionContext $context) {
        $validationDate = new \DateTime();
        $validationDate->modify('-12 year');
        if ($this->getDateOfBirth()) {
            if ($this->getDateOfBirth() > $validationDate) {
                $propertyPath = $context->getPropertyPath() . '.dateOfBirth';
                $context->setPropertyPath($propertyPath);
                $context->addViolation('The date of birth must be less than ' . $validationDate->format('Y-m-d'), array(), null);
            } else {
                $validationDate->modify('-108 year');
                if ($this->getDateOfBirth() < $validationDate) {
                    $propertyPath = $context->getPropertyPath() . '.dateOfBirth';
                    $context->setPropertyPath($propertyPath);
                    $context->addViolation('The date of birth must be greater than ' . $validationDate->format('Y-m-d'), array(), null);
                }
            }
        }
    }

    /**
     * this function is used to get the years count for the user
     * @author Mahmoud
     * @return integer the number of years
     */
    public function getAge() {
        if ($this->getDateOfBirth()) {
            $currentDate = new \DateTime();
            return $currentDate->diff($this->getDateOfBirth())->y;
        }
    }

    /**
     * Set educationsPoints
     *
     * @param integer $educationsPoints
     */
    public function setEducationsPoints($educationsPoints) {
        $this->educationsPoints = $educationsPoints;
    }

    /**
     * Get educationsPoints
     *
     * @return integer
     */
    public function getEducationsPoints() {
        return $this->educationsPoints;
    }

    /**
     * Add personalQuestionAnswers
     *
     * @param Objects\InternJumpBundle\Entity\PersonalQuestionAnswer $personalQuestionAnswers
     */
    public function addPersonalQuestionAnswer(\Objects\InternJumpBundle\Entity\PersonalQuestionAnswer $personalQuestionAnswers) {
        $this->personalQuestionAnswers[] = $personalQuestionAnswers;
    }

    /**
     * Get personalQuestionAnswers
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getPersonalQuestionAnswers() {
        return $this->personalQuestionAnswers;
    }

    /**
     * Add languages
     *
     * @param Objects\InternJumpBundle\Entity\UserLanguage $languages
     */
    public function addUserLanguage(\Objects\InternJumpBundle\Entity\UserLanguage $languages) {
        $this->languages[] = $languages;
    }

    /**
     * Get languages
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getLanguages() {
        return $this->languages;
    }

    /**
     * Set languages
     *
     * @return Doctrine\Common\Collections\Collection $languages
     */
    public function setLanguages($languages) {
        $this->languages = $languages;
    }

    /**
     * Set currentWorth
     *
     * @param integer $currentWorth
     */
    public function setCurrentWorth($currentWorth) {
        $this->currentWorth = $currentWorth;
    }

    /**
     * Get currentWorth
     *
     * @return integer
     */
    public function getCurrentWorth() {
        return $this->currentWorth;
    }

    /**
     * Set netWorth
     *
     * @param integer $netWorth
     */
    public function setNetWorth($netWorth) {
        $this->netWorth = $netWorth;
    }

    /**
     * Get netWorth
     *
     * @return integer
     */
    public function getNetWorth() {
        return $this->netWorth;
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
     * Add favoriteComapnies
     *
     * @param Objects\InternJumpBundle\Entity\Company $favoriteComapnies
     */
    public function addCompany(\Objects\InternJumpBundle\Entity\Company $favoriteComapnies) {
        $this->favoriteComapnies[] = $favoriteComapnies;
    }

    /**
     * Get favoriteComapnies
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getFavoriteComapnies() {
        return $this->favoriteComapnies;
    }

    /**
     * Set activatedAt
     *
     * @param date $activatedAt
     */
    public function setActivatedAt($activatedAt) {
        $this->activatedAt = $activatedAt;
    }

    /**
     * Get activatedAt
     *
     * @return date
     */
    public function getActivatedAt() {
        return $this->activatedAt;
    }

    /**
     * Set matchingJobEmail
     *
     * @param boolean $matchingJobEmail
     */
    public function setMatchingJobEmail($matchingJobEmail) {
        $this->matchingJobEmail = $matchingJobEmail;
    }

    /**
     * Get matchingJobEmail
     *
     * @return boolean
     */
    public function getMatchingJobEmail() {
        return $this->matchingJobEmail;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $stringParts = explode(' ', $name, 2);
        if (isset($stringParts[0])) {
            $this->firstName = $stringParts[0];
        }
        if (isset($stringParts[1])) {
            $this->lastName = $stringParts[1];
        }
        $this->name = $name;
    }

}
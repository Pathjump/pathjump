<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Objects\InternJumpBundle\Validator\Constrains\GlobalUnique;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\Manager
 *
 * @ORM\Table()
 * @UniqueEntity(fields={"loginName"}, groups={"newmanager", "editmanager"})
 * @UniqueEntity(fields={"email"}, groups={"newmanager", "editmanager"})
 * @GlobalUnique(groups={"newmanager", "editmanager"})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\ManagerRepository")
 */
class Manager implements AdvancedUserInterface {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $managerRole
     * @ORM\ManyToMany(targetEntity="\Objects\UserBundle\Entity\Role")
     * @ORM\JoinTable(name="manager_role",
     *     joinColumns={@ORM\JoinColumn(name="manager_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $managerRoles;

    /**
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string $loginName
     * @Assert\NotBlank(groups={"newmanager", "editmanager"})
     * @Assert\Regex(pattern="/^\w+$/u", groups={"newmanager", "editmanager"}, message="Only characters, numbers and _")
     * @ORM\Column(name="loginName", type="string", length=255, unique=true)
     */
    private $loginName;

    /**
     * @var string $email
     * @Assert\NotBlank(groups={"newmanager", "editmanager"})
     * @Assert\Email(groups={"newmanager", "editmanager"})
     * @ORM\Column(name="email", type="string", length=255, unique=true)
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
     * @Assert\MinLength(limit=6, groups={"newmanager"})
     * @Assert\NotNull(groups={"newmanager"})
     */
    private $userPassword;

    /**
     * @Assert\NotNull(groups={"oldPassword"})
     * @var string $oldPassword
     */
    private $oldPassword;

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
     * @var datetime $createdAt
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    public function __construct() {
        $this->salt = md5(time());
        $this->createdAt = new \DateTime();
        $this->managerRoles = new ArrayCollection();
    }

    /**
     * this function is used by php to know which attributes to serialize
     * the returned array must not contain any one to one or one to many relation object
     * @return array
     */
    public function __sleep() {
        return array(
            'id', 'loginName', 'password', 'createdAt', 'name', 'locked', 'enabled', 'salt'
        );
    }

    public function __toString() {
        if ($this->name)
            return (string) $this->name;
        else
            return (string) $this->loginName;
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
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set createdAt
     *
     * @param datetime $createdAt
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return datetime
     */
    public function getCreatedAt() {
        return $this->createdAt;
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
     * Get salt
     *
     * @return string
     */
    public function getSalt() {
        return $this->salt;
    }

    /**
     * this function will set a valid random password for the user
     */
    public function setRandomPassword() {
        $this->setUserPassword(rand());
    }

    /**
     * Implementation of getRoles for the UserInterface.
     *
     * @return array An array of Roles
     */
    public function getRoles() {
        return $this->getManagerRoles()->toArray();
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

    public function getUsername() {
        return $this->loginName;
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
     * Add managerRoles
     *
     * @param Objects\UserBundle\Entity\Role $managerRoles
     */
    public function addRole(\Objects\UserBundle\Entity\Role $managerRoles)
    {
        $this->managerRoles[] = $managerRoles;
    }

    /**
     * Get managerRoles
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getManagerRoles()
    {
        return $this->managerRoles;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
}
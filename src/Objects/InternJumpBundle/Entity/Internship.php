<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\ExecutionContext;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Locale\Locale;

/**
 * Objects\InternJumpBundle\Entity\Internship
 *
 * @ORM\Table()
 * @Assert\Callback(methods={"isCategoriesCorrect"},groups={"newInternship","editInternship"})
 * @Assert\Callback(methods={"isLanguagesCorrect"},groups={"newInternship","editInternship"})
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\InternshipRepository")
 */
class Internship {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public $otherSkills;

    /**
     * the company of the internship
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company", inversedBy="internships")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $categories
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\CVCategory")
     * @ORM\JoinTable(name="internship_category",
     *     joinColumns={@ORM\JoinColumn(name="internship_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $categories;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection $keywords
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\Keywords")
     * @ORM\JoinTable(name="internship_keyword",
     *     joinColumns={@ORM\JoinColumn(name="internship_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="keyword_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $keywords;

    /**
     * the interviews of the internship
     * @var \Doctrine\Common\Collections\ArrayCollection $interviews
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Interview", mappedBy="internship", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $interviews;

    /**
     * the required languages for the internship
     * @var \Doctrine\Common\Collections\ArrayCollection $languages
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\InternshipLanguage", mappedBy="internship", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $languages;

    /**
     * the internships of the users
     * @var \Doctrine\Common\Collections\ArrayCollection $usersInternships
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\UserInternship", mappedBy="internship", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $usersInternships;

    /**
     * the tasks of the internship
     * @var \Doctrine\Common\Collections\ArrayCollection $tasks
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\Task", mappedBy="internship", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $tasks;

    /**
     * @var decimal $Latitude
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="Latitude", type="decimal", precision=18, scale=12)
     */
    private $Latitude;

    /**
     * @var decimal $Longitude
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="Longitude", type="decimal", precision=18, scale=12)
     */
    private $Longitude;

    /**
     * @var decimal $numberOfOpenings
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="numberOfOpenings", type="integer")
     */
    private $numberOfOpenings;

    /**
     * @var decimal $minimumGPA
     * @Assert\Max(limit = 4, message = "GPA must be 4 or less.", groups={"newInternship","editInternship"})
     * @ORM\Column(name="minimumGPA", type="decimal", precision=3, scale=2, nullable=true)
     */
    private $minimumGPA;

    /**
     * @var string $positionType
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="positionType", type="string", length=255)
     */
    private $positionType;

    /**
     * the cv skills
     * @var \Doctrine\Common\Collections\ArrayCollection $skills
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\Skill")
     * @ORM\JoinTable(name="internship_skills",
     *     joinColumns={@ORM\JoinColumn(name="internship_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="skill_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)}
     * )
     */
    private $skills;

    /**
     * @var string $compensation
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="compensation", type="string", length=255)
     */
    private $compensation;

    /**
     * @var string $workLocation
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="workLocation", type="string", length=255)
     */
    private $workLocation;

    /**
     * @var string $sessionPeriod
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="sessionPeriod", type="string", length=255)
     */
    private $sessionPeriod;

    /**
     * @var string $zipcode
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="zipcode", type="string", length=255)
     */
    private $zipcode;

    /**
     * @var date $createdAt
     *
     * @ORM\Column(name="createdAt", type="date")
     */
    private $createdAt;

    /**
     * @var date $activeFrom
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @Assert\Date
     * @ORM\Column(name="activeFrom", type="date")
     */
    private $activeFrom;

    /**
     * @var date $activeTo
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @Assert\Date
     * @ORM\Column(name="activeTo", type="date")
     */
    private $activeTo;

    /**
     * @var boolean $active
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = TRUE;

    /**
     * @var string $title
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $description
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var text $requirements
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="requirements", type="text")
     */
    private $requirements;

    /**
     * @var string $country
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var string $city
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="city", type="string", length=255)
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
     * @Assert\NotNull(groups={"newInternship","editInternship"})
     * @ORM\Column(name="address", type="text")
     */
    private $address;

    public function __toString() {
        return $this->title;
    }

    public function __construct() {
        $this->createdAt = new \DateTime();
        $this->activeFrom = new \DateTime();
        $this->tasks = new ArrayCollection();
        $this->interviews = new ArrayCollection();
        $this->usersInternships = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->keywords = new ArrayCollection();
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
     * Get createdAt
     *
     * @return date
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set activeFrom
     *
     * @param date $activeFrom
     */
    public function setActiveFrom($activeFrom) {
        $this->activeFrom = $activeFrom;
    }

    /**
     * Get activeFrom
     *
     * @return date
     */
    public function getActiveFrom() {
        return $this->activeFrom;
    }

    /**
     * Set activeTo
     *
     * @param date $activeTo
     */
    public function setActiveTo($activeTo) {
        $this->activeTo = $activeTo;
    }

    /**
     * Get activeTo
     *
     * @return date
     */
    public function getActiveTo() {
        return $this->activeTo;
    }

    /**
     * Set active
     *
     * @param boolean $active
     */
    public function setActive($active) {
        $this->active = $active;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive() {
        return $this->active;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param text $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return text
     */
    public function getDescription() {
        return $this->description;
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
     * Add usersInternships
     *
     * @param Objects\InternJumpBundle\Entity\UserInternship $usersInternships
     */
    public function addUserInternship(\Objects\InternJumpBundle\Entity\UserInternship $usersInternships) {
        $this->usersInternships[] = $usersInternships;
    }

    /**
     * Get usersInternships
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getUsersInternships() {
        return $this->usersInternships;
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
     * this function will check if the internship time is valid
     * @Assert\True(groups={"newInternship","editInternship"},message = "The active to date must be greater than the active from date")
     */
    public function isActiveToCorrect() {
        if ($this->getActiveTo() > $this->getActiveFrom()) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * this function will check if the internship time is valid
     * @Assert\True(groups={"newInternship"},message = "The active from date must be greater than or equal the current date")
     */
    public function isFromDateCorrect() {
        $nowDate = new \DateTime('today');
        if ($this->getActiveFrom() >= $nowDate) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * this function will check if the cv has more than one category
     * @author Ahmed
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
     * this function will check if the cv has more than one category
     * @author Ahmed
     * @param \Symfony\Component\Validator\ExecutionContext $context
     */
    public function isLanguagesCorrect(ExecutionContext $context) {
        if ($this->languages && count($this->languages) == 0) {
            $propertyPath = $context->getPropertyPath() . '.languages';
            $context->setPropertyPath($propertyPath);
            $context->addViolation('You must select at least one language', array(), NULL);
        }
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
     * Set requirements
     *
     * @param text $requirements
     */
    public function setRequirements($requirements) {
        $this->requirements = $requirements;
    }

    /**
     * Get requirements
     *
     * @return text
     */
    public function getRequirements() {
        return $this->requirements;
    }

    /**
     * Add categories
     *
     * @param Objects\InternJumpBundle\Entity\CVCategory $categories
     */
    public function addCVCategory(\Objects\InternJumpBundle\Entity\CVCategory $categories) {
        $this->categories[] = $categories;
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
     * Set categories
     *
     * @param Doctrine\Common\Collections\Collection $categories
     */
    public function setCategories($categories) {
        $this->categories = $categories;
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
     * Set educations
     *
     * @param Doctrine\Common\Collections\Collection $languages
     */
    public function setLanguages($languages) {
        $this->languages = $languages;
        foreach ($languages as $language) {
            $language->setInternship($this);
        }
    }

    /**
     * Add languages
     *
     * @param Objects\InternJumpBundle\Entity\InternshipLanguage $languages
     */
    public function addInternshipLanguage(\Objects\InternJumpBundle\Entity\InternshipLanguage $languages) {
        $this->languages[] = $languages;
    }

    public function addLanguages(\Objects\InternJumpBundle\Entity\InternshipLanguage $languages){
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
     * Set positionType
     *
     * @param string $positionType
     */
    public function setPositionType($positionType)
    {
        $this->positionType = $positionType;
    }

    /**
     * Get positionType
     *
     * @return string
     */
    public function getPositionType()
    {
        return $this->positionType;
    }

    /**
     * Set minimumGPA
     *
     * @param decimal $minimumGPA
     */
    public function setMinimumGPA($minimumGPA)
    {
        $this->minimumGPA = $minimumGPA;
    }

    /**
     * Get minimumGPA
     *
     * @return decimal
     */
    public function getMinimumGPA()
    {
        return $this->minimumGPA;
    }

    /**
     * Set workLocation
     *
     * @param string $workLocation
     */
    public function setWorkLocation($workLocation)
    {
        $this->workLocation = $workLocation;
    }

    /**
     * Get workLocation
     *
     * @return string
     */
    public function getWorkLocation()
    {
        return $this->workLocation;
    }

    /**
     * Set numberOfOpenings
     *
     * @param integer $numberOfOpenings
     */
    public function setNumberOfOpenings($numberOfOpenings)
    {
        $this->numberOfOpenings = $numberOfOpenings;
    }

    /**
     * Get numberOfOpenings
     *
     * @return integer
     */
    public function getNumberOfOpenings()
    {
        return $this->numberOfOpenings;
    }

    /**
     * Set compensation
     *
     * @param string $compensation
     */
    public function setCompensation($compensation)
    {
        $this->compensation = $compensation;
    }

    /**
     * Get compensation
     *
     * @return string
     */
    public function getCompensation()
    {
        return $this->compensation;
    }

    /**
     * Set sessionPeriod
     *
     * @param string $sessionPeriod
     */
    public function setSessionPeriod($sessionPeriod)
    {
        $this->sessionPeriod = $sessionPeriod;
    }

    /**
     * Get sessionPeriod
     *
     * @return string
     */
    public function getSessionPeriod()
    {
        return $this->sessionPeriod;
    }

    /**
     * Add keywords
     *
     * @param Objects\InternJumpBundle\Entity\Keywords $keywords
     */
    public function addKeywords(\Objects\InternJumpBundle\Entity\Keywords $keywords)
    {
        $this->keywords[] = $keywords;
    }

    /**
     * Set educations
     *
     * @param Doctrine\Common\Collections\Collection $keywords
     */
    public function setKeywords($keywords) {
        $this->keywords = $keywords;
    }

    /**
     * Get keywords
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * delete keywords
     *
     */
    public function deleteKeywords() {
            $this->keywords = new ArrayCollection();
    }

    /**
     * Add skills
     *
     * @param Objects\InternJumpBundle\Entity\Skill $skills
     */
    public function addSkill(\Objects\InternJumpBundle\Entity\Skill $skills)
    {
        $this->skills[] = $skills;
    }

    public function setSkills($skills){
        $this->skills = $skills;
    }

    /**
     * Get skills
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSkills()
    {
        return $this->skills;
    }
}
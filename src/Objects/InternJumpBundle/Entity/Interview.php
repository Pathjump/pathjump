<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Locale\Locale;

/**
 * Objects\InternJumpBundle\Entity\Interview
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\InterviewRepository")
 */
class Interview {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the internship of the interview
     * @var \Objects\InternJumpBundle\Entity\Internship $internship
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Internship", inversedBy="interviews")
     * @ORM\JoinColumn(name="internship_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $internship;

    /**
     * the company of the interview
     * @var \Objects\InternJumpBundle\Entity\Company $company
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\Company", inversedBy="interviews")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $company;

    /**
     * the user of the interview
     * @var \Objects\UserBundle\Entity\User $user
     * @ORM\ManyToOne(targetEntity="\Objects\UserBundle\Entity\User", inversedBy="interviews")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE", nullable=false)
     */
    private $user;

    /**
     * @var string $country
     * @Assert\NotNull(groups={"interview"})
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var string $city
     * @Assert\NotNull(groups={"interview"})
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
     * @var decimal $Latitude
     * @Assert\NotNull(groups={"interview"})
     * @ORM\Column(name="Latitude", type="decimal", precision=18, scale=12)
     */
    private $Latitude;

    /**
     * @var decimal $Longitude
     * @Assert\NotNull(groups={"interview"})
     * @ORM\Column(name="Longitude", type="decimal", precision=18, scale=12)
     */
    private $Longitude;
    
    /**
     * @var text $address
     * @Assert\NotNull(groups={"interview"})
     * @ORM\Column(name="address", type="text")
     */
    private $address;
    
    /**
     * @var text $details
     * @ORM\Column(name="details", type="text",nullable=true)
     */
    private $details;

    /**
     * @var datetime $interviewDate
     * @Assert\NotNull(groups={"interview"})
     * @Assert\DateTime(groups={"interview"})
     * @ORM\Column(name="interviewDate", type="datetime")
     */
    private $interviewDate;

    /**
     * @var string $accepted
     *
     * @ORM\Column(name="accepted", type="string", length=255)
     */
    private $accepted = 'pending';
    
    /**
     * @var string $zipcode
     * @Assert\NotNull(groups={"interview"})
     * @ORM\Column(name="zipcode", type="string", length=255)
     */
    private $zipcode;
    
    public function __toString() {
        return "interview $this->id";
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
     * Set interviewDate
     *
     * @param datetime $interviewDate
     */
    public function setInterviewDate($interviewDate) {
        $this->interviewDate = $interviewDate;
    }

    /**
     * Get interviewDate
     *
     * @return datetime 
     */
    public function getInterviewDate() {
        return $this->interviewDate;
    }

    /**
     * Set accepted
     *
     * @param string $accepted
     */
    public function setAccepted($accepted) {
        $this->accepted = $accepted;
    }

    /**
     * Get accepted
     *
     * @return string 
     */
    public function getAccepted() {
        return $this->accepted;
    }

    /**
     * @author Ahmed
     * @return array of the valid statuses
     */
    public function getValidAcceptedStatuses() {
        return array(
            'pending' => 'pending',
            'rejected' => 'rejected',
            'accepted' => 'accepted',
        );
    }
    
    /**
     * Set internship
     *
     * @param Objects\InternJumpBundle\Entity\Internship $internship
     */
    public function setInternship(\Objects\InternJumpBundle\Entity\Internship $internship) {
        $this->internship = $internship;
    }

    /**
     * Get internship
     *
     * @return Objects\InternJumpBundle\Entity\Internship 
     */
    public function getInternship() {
        return $this->internship;
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
     * Set Latitude
     *
     * @param decimal $latitude
     */
    public function setLatitude($latitude)
    {
        $this->Latitude = $latitude;
    }

    /**
     * Get Latitude
     *
     * @return decimal 
     */
    public function getLatitude()
    {
        return $this->Latitude;
    }

    /**
     * Set Longitude
     *
     * @param decimal $longitude
     */
    public function setLongitude($longitude)
    {
        $this->Longitude = $longitude;
    }

    /**
     * Get Longitude
     *
     * @return decimal 
     */
    public function getLongitude()
    {
        return $this->Longitude;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    /**
     * Get zipcode
     *
     * @return string 
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }
    
    /**
     * this function will check if the internship time is valid
     * @Assert\True(message = "The Interview date must be greater than or equal the current date",groups={"interview"})
     */
    public function isInterviewDateCorrect() {
        $nowDate = new \DateTime('today');
        if ($this->getInterviewDate() >= $nowDate) {
            return TRUE;
        }
        return FALSE;
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
     * Set details
     *
     * @param text $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * Get details
     *
     * @return text 
     */
    public function getDetails()
    {
        return $this->details;
    }
}
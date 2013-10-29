<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\postCategory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\postCategoryRepository")
 */
class postCategory
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
     * @var string $catName
     *
     * @ORM\Column(name="catName", type="string", length=255)
     */
    private $catName;

    /**
     * @var string $catSlug
     *
     * @ORM\Column(name="catSlug", type="string", length=255)
     */
    private $catSlug;


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
     * Set catName
     *
     * @param string $catName
     */
    public function setCatName($catName)
    {
        $this->catName = $catName;
    }

    /**
     * Get catName
     *
     * @return string 
     */
    public function getCatName()
    {
        return $this->catName;
    }

    /**
     * Set catSlug
     *
     * @param string $catSlug
     */
    public function setCatSlug($catSlug)
    {
        // replace all non letters or digits by - then remove any extra white spaces
        $this->catSlug = trim(preg_replace('/\W+/u', '-', $catSlug));
    }

    /**
     * Get catSlug
     *
     * @return string 
     */
    public function getCatSlug()
    {
        return $this->catSlug;
    }
    
    public function __toString() {
        return $this->catName;
    }


}
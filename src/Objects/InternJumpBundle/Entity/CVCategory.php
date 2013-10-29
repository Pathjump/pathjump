<?php

namespace Objects\InternJumpBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Objects\InternJumpBundle\Entity\CVCategory
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Objects\InternJumpBundle\Entity\CVCategoryRepository")
 * @UniqueEntity(fields={"name"})
 * @UniqueEntity(fields={"slug"})
 */
class CVCategory {

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * the parent category
     * @var \Objects\InternJumpBundle\Entity\CVCategory $parentCategory
     * @ORM\ManyToOne(targetEntity="\Objects\InternJumpBundle\Entity\CVCategory", inversedBy="subCategories")
     * @ORM\JoinColumn(name="parent_category_id", referencedColumnName="id", onDelete="CASCADE", onUpdate="CASCADE")
     */
    private $parentCategory;

    /**
     * the category sub categories
     * @var \Doctrine\Common\Collections\ArrayCollection $subCategories
     * @ORM\OneToMany(targetEntity="\Objects\InternJumpBundle\Entity\CVCategory", mappedBy="parentCategory", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $subCategories;

    /**
     * @var string $name
     * @Assert\NotNull
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string $slug
     * @Assert\NotNull
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="\Objects\InternJumpBundle\Entity\CV", mappedBy="categories")
     */
    private $cvs;

    public function __construct() {
        $this->subCategories = new ArrayCollection();
    }

    public function __toString() {
        return $this->name;
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
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug) {
        // replace all non letters or digits by - then remove any extra white spaces
        $this->slug = trim(preg_replace('/\W+/u', '-', $slug));
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug() {
        return $this->slug;
    }

    /**
     * Set parentCategory
     *
     * @param Objects\InternJumpBundle\Entity\CVCategory $parentCategory
     */
    public function setParentCategory(\Objects\InternJumpBundle\Entity\CVCategory $parentCategory = NULL) {
        $this->parentCategory = $parentCategory;
    }

    /**
     * Get parentCategory
     *
     * @return Objects\InternJumpBundle\Entity\CVCategory
     */
    public function getParentCategory() {
        return $this->parentCategory;
    }

    /**
     * Add subCategories
     *
     * @param Objects\InternJumpBundle\Entity\CVCategory $subCategories
     */
    public function addCVCategory(\Objects\InternJumpBundle\Entity\CVCategory $subCategories) {
        $this->subCategories[] = $subCategories;
    }

    /**
     * Get subCategories
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getSubCategories() {
        return $this->subCategories;
    }

    /**
     * @Assert\True(message = "You can not make a category a child to itself or to another child category")
     */
    public function isParentCategoryCorrect() {
        if ($this->getParentCategory() && ($this->getParentCategory()->getId() == $this->getId() || $this->getParentCategory()->getParentCategory())) {
            return FALSE;
        }
        return TRUE;
    }


    /**
     * Add cvs
     *
     * @param Objects\InternJumpBundle\Entity\CV $cvs
     */
    public function addCV(\Objects\InternJumpBundle\Entity\CV $cvs)
    {
        $this->cvs[] = $cvs;
    }

    /**
     * Get cvs
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getCvs()
    {
        return $this->cvs;
    }
}
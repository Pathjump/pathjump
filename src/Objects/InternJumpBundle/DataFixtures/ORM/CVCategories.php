<?php

/**
 * @author ahmed
 */

namespace Objects\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Objects\InternJumpBundle\Entity\CVCategory;

class CVCategories implements FixtureInterface {

    public function load(ObjectManager $manager) {
        $categories = array(
            "Advertising/PR" => "Advertising/PR",
            "Advocacy/Civil Rights" => "Advocacy/Civil Rights",
            "Architecture" => "Architecture",
            "Art" => "Art",
            "Business" => "Business",
            "Consumer Goods" => "Consumer Goods",
            "Engineering" => "Engineering",
            "Fashion" => "Fashion",
            "Film" => "Film",
            "Finance" => "Finance",
            "Hospitality" => "Hospitality",
            "Human Resources" => "Human Resources",
            "IT/Software Engineering" => "IT/Software Engineering",
            "Music" => "Music",
            "Nonprofit" => "Nonprofit",
            "Other" => "Other",
            "Politics" => "Politics",
            "Publishing" => "Publishing",
            "Sales" => "Sales",
            "Sports" => "Sports",
            "Technology" => "Technology",
            "Theatre" => "Theatre",
            "Start-Ups" => "Start-Ups"
        );
        foreach ($categories as $category) {
            $cvCategory = new CVCategory();
            $cvCategory->setName($category);
            $cvCategory->setSlug($category);
            $manager->persist($cvCategory);
        }
        $manager->flush();
    }

}

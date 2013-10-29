<?php

namespace Objects\AdminBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Objects\AdminBundle\Form\Config
 *
 */
class Config {
    //api bundle config

    /**
     * @Assert\MinLength(21)
     * @var string
     */
    public $consumer_key;

    /**
     * @Assert\MinLength(42)
     * @var string
     */
    public $consumer_secret;

    /**
     * @Assert\MinLength(15)
     * @var string
     */
    public $facebook_app_id;

    /**
     * @Assert\MinLength(32)
     * @var string
     */
    public $facebook_app_secret;

    /**
     * @Assert\MinLength(3)
     * @var string
     */
    public $fb_page_name;
    
    /**
     * @Assert\Type(type="bool")
     * @var boolean 
     */
    public $post_twitter = TRUE;
    
    /**
     * @Assert\Type(type="bool")
     * @var boolean 
     */
    public $post_facebook = TRUE;
    
    //indexing bundle config
    
    /**
     * @Assert\Type(type="integer")
     * @Assert\Min(limit = "1")
     * @var integer 
     */
    public $site_links_stall_time;
    
    /**
     * @Assert\Type(type="integer")
     * @Assert\Min(limit = "1")
     * @var integer 
     */
    public $site_links_fault_times;

}
<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//require_once __DIR__ . '/../vendor/FacebookSDK/src/facebook.php';
use Objects\APIBundle\Controller\FacebookController;
use Objects\UserBundle\Entity\SocialAccounts;
use Objects\UserBundle\Entity\User;

class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        $facebook = new \Facebook(array(
                    'appId' => '282137608565990',
                    'secret' => 'c0e978a64bc520a8b677dfa9e57f4746',
                ));

        // Get User ID
        $user = $facebook->getUser();
        if ($user) {
            try {
                // Proceed knowing you have a logged in user who's authenticated.
                $user_profile = $facebook->api('/me');
    
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }
        if ($user) {
            $logoutUrl = $facebook->getLogoutUrl();
        } else {
            $loginUrl = $facebook->getLoginUrl();
        }
        
        //postOnUserWallAndFeedAction($accountId, $accessToken, $message, $name, $description, $link, $picture)
        return $this->render('ObjectsInternJumpBundle:Default:index.html.twig', array('name' => $name));
    }
    
    public function testAction()
    {
        return $this->render('ObjectsInternJumpBundle:Default:test.html.twig');
    }
    
}

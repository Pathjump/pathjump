<?php

namespace Objects\UserBundle\Controller;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Objects\APIBundle\Controller\TwitterController;
use Objects\APIBundle\Controller\LinkedinController;
use Objects\APIBundle\Controller\FacebookController;
use Objects\UserBundle\Entity\SocialAccounts;
use Objects\UserBundle\Entity\User;
use Objects\InternJumpBundle\Entity\City;

require_once __DIR__ . '/../../../../vendor/FacebookSDK/src/facebook.php';

class FacebookUserController extends Controller {

    /**
     * For Facebook App
     * this action used to logout the user and REMEMBERME cookie before facebook login action
     * @author Ahmed
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function executeLogoutAction() {
        $this->get('security.context')->setToken(null);
        //invalidate the current user session
        $this->getRequest()->getSession()->invalidate();
        $response = new Response();
        $response->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie('REMEMBERME', null, new \DateTime('-1 day')));
        $response->sendHeaders();
        return $response;
    }

    /**
     * the edit action
     * @author Mahmoud
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction() {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the request object
        $request = $this->getRequest();
        //get the session object
        $session = $request->getSession();
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the container object
        $container = $this->container;
        //get the translator object
        $translator = $this->get('translator');
        //get the user object from the firewall
        $loggedInUser = $this->get('security.context')->getToken()->getUser();
        //get the user social accounts object
        $socialAccounts = $loggedInUser->getSocialAccounts();
        //initialize the success message
        $message = FALSE;
        //initialize the redirect flag
        $redirect = FALSE;
        //initialize the form validation groups array
        $formValidationGroups = array('edit');
        //initialize the old password to not required
        $oldPassword = FALSE;
        //initialize the change user name to false
        $changeUserName = FALSE;
        //check if the user is logged in from the login form
        if (FALSE === $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY')) {
            //mark the old password as required
            $oldPassword = TRUE;
        }
        //check if the user can change the login name
        if ($container->getParameter('login_name_required') && TRUE === $this->get('security.context')->isGranted('ROLE_UPDATABLE_USERNAME')) {
            //make the user able to change his user name
            $changeUserName = TRUE;
        }
        //check if the old password is required
        if ($oldPassword) {
            //add the old password group to the form validation array
            $formValidationGroups [] = 'oldPassword';
        }
        //check if the user can change his user name
        if ($changeUserName) {
            //add the login name group to the form validation array
            $formValidationGroups [] = 'loginName';
        }
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray[$value['id']] = $value['name'];
        }
        //get the old user email
        $oldEmail = $loggedInUser->getEmail();
        //get the old user name
        $oldLoginName = $loggedInUser->getLoginName();
        //create a password form
        $formBuilder = $this->createFormBuilder($loggedInUser, array(
                    'validation_groups' => $formValidationGroups
                ))
                ->add('userPassword', 'repeated', array(
                    'type' => 'password',
                    'first_name' => "Password",
                    'second_name' => "RePassword",
                    'invalid_message' => "The passwords don't match",
                    'required' => false
                ))
                ->add('gender', 'choice', array(
                    'choices' => array('1' => 'Male', '0' => 'Female'),
                    'multiple' => false
                ))
                ->add('dateOfBirth', 'birthday', array('widget' => 'single_text', 'format' => 'yyyy-MM-dd', 'required' => FALSE, 'attr' => array('class' => 'dateOfBirth')))
                ->add('firstName')
                ->add('lastName')
                ->add('about')
                ->add('address')
                ->add('zipcode')
                ->add('url', 'url', array('required' => false))
                ->add('country', 'choice', array('choices' => $allCountriesArray, 'required' => false))
                ->add('city')
                ->add('state', 'choice', array('required' => false))
                ->add('email')
                ->add('file', 'file', array('required' => false, 'label' => 'image', 'attr' => array('onchange' => 'readURL(this);')))
        ;
        //check if the old password is required
        if ($oldPassword) {
            //add the old password field
            $formBuilder->add('oldPassword', 'password');
        }
        //check if the user can change his user name
        if ($changeUserName) {
            //add the login name field
            $formBuilder->add('loginName');
        }
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //get the user object from the form
                $user = $form->getData();

                //check if we need to change the user to not active
                if ($user->getEmail() != $oldEmail && !$container->getParameter('auto_active')) {
                    //remove the role user
                    foreach ($user->getUserRoles() as $key => $roleObject) {
                        //check if this is the wanted role
                        if ($roleObject->getName() == 'ROLE_USER') {
                            //remove the role from the user
                            $user->getUserRoles()->remove($key);
                            //stop the search
                            break;
                        }
                    }
                    //get the not active role object
                    $role = $em->getRepository('ObjectsUserBundle:Role')->findOneByName('ROLE_NOTACTIVE');
                    //check if the user already has the role
                    if (!$user->getUserRoles()->contains($role)) {
                        //add the role to the user
                        $user->addRole($role);
                    }
                    //prepare the body of the email
                    $body = $this->renderView('ObjectsUserBundle:User:Emails\activate_email.txt.twig', array('user' => $user));
                    //prepare the message object
                    $message = \Swift_Message::newInstance()
                            ->setSubject($translator->trans('activate your account'))
                            ->setFrom($container->getParameter('mailer_user'))
                            ->setTo($user->getEmail())
                            ->setBody($body)
                    ;
                    //send the activation mail to the user
                    $this->get('mailer')->send($message);
                }
                //check if the user changed his login name
                if ($changeUserName && $oldLoginName != $user->getLoginName()) {
                    //remove the update user name role
                    foreach ($user->getUserRoles() as $key => $roleObject) {
                        //check if this is the wanted role
                        if ($roleObject->getName() == 'ROLE_UPDATABLE_USERNAME') {
                            //remove the role from the user
                            $user->getUserRoles()->remove($key);
                            //stop the search
                            break;
                        }
                    }
                    //redirect the user to remove the login name from the form and refresh his roles
                    $redirect = TRUE;
                }
                //set the password for the user if changed
                $user->setValidPassword();
                //save the data
                $em->flush();
                //check if the user set a valid old password
                if ($oldPassword) {
                    //redirect the user to remove the old password from the form
                    $redirect = TRUE;
                }
                //check if we need to redirect the user
                if ($redirect) {
                    //set the success flash
                    $session->setFlash('success', $translator->trans('Done'));
                    //make the user fully authenticated and refresh his roles
                    try {
                        // create the authentication token
                        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                        // give it to the security context
                        $this->get('security.context')->setToken($token);
                    } catch (\Exception $e) {
                        //can not reload the user object log out the user
                        $this->get('security.context')->setToken(null);
                        //invalidate the current user session
                        $this->getRequest()->getSession()->invalidate();
                        //redirect to the login page
                        return $this->redirect($this->generateUrl('site_fb_homepage'));
                    }
                    //redirect the user
                    return $this->redirect($this->generateUrl('fb_user_edit'));
                }
                //set the success message
                $message = $translator->trans('Done');
            }
        }
        return $this->render('ObjectsUserBundle:FacebookUser:edit.html.twig', array(
                    'form' => $form->createView(),
                    'oldPassword' => $oldPassword,
                    'changeUserName' => $changeUserName,
                    'message' => $message,
                    'socialAccounts' => $socialAccounts,
                    'user' => $loggedInUser,
                    'formName' => $this->container->getParameter('studentEditAccount_FormName'),
                    'formDesc' => $this->container->getParameter('studentEditAccount_FormDesc'),
        ));
    }

    /**
     * this function used to resend activation mail to not actice user
     * @author ahmed
     */
    public function reActivationAction() {
        //get the container object
        $container = $this->container;
        //get the translator object
        $translator = $this->get('translator');
        //get the session object
        $session = $this->getRequest()->getSession();
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //check if we have a logged in user or company
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $session->setFlash('note', $translator->trans('You need to Login first.'));
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }

        //check if the user is already active
        if (TRUE === $this->get('security.context')->isGranted('ROLE_USER')) {
            //set a notice flag
            $session->setFlash('notice', $translator->trans('Your acount is active.'));
            return $this->redirect($this->generateUrl('fb_user_edit'));
        }

        //get the logedin user
        $user = $this->get('security.context')->getToken()->getUser();

        //get the not active role object
        $role = $em->getRepository('ObjectsUserBundle:Role')->findOneByName('ROLE_NOTACTIVE');
        //check if the user already has the role
        if (!$user->getUserRoles()->contains($role)) {
            //add the role to the user
            $user->addRole($role);
        }
        //prepare the body of the email
        $body = $this->renderView('ObjectsUserBundle:User:Emails\activate_email.txt.twig', array('user' => $user));
        //prepare the message object
        $message = \Swift_Message::newInstance()
                ->setSubject($translator->trans('activate your account'))
                ->setFrom($container->getParameter('mailer_user'))
                ->setTo($user->getEmail())
                ->setBody($body)
        ;
        //send the activation mail to the user
        $this->get('mailer')->send($message);
        //set the success flag in the session
        $session->setFlash('success', $this->get('translator')->trans('check your email for your activation link'));
        //redirect the user to portal
        return $this->redirect($this->generateUrl('fb_student_task', array('loginName' => $user->getLoginName())));
    }

    /**
     * this action will link the user account to his twitter account
     * @author Mahmoud
     */
    public function twitterLinkAction() {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //reload the user object from the database
        $user = $em->getRepository('ObjectsUserBundle:User')->getUserWithSocialAccounts($this->get('security.context')->getToken()->getUser()->getId());
        //get the request object
        $request = $this->getRequest();
        //get the translator object
        $translator = $this->get('translator');
        //get the session object
        $session = $request->getSession();
        //get the oauth token from the session
        $oauth_token = $session->get('oauth_token', FALSE);
        //get the oauth token secret from the session
        $oauth_token_secret = $session->get('oauth_token_secret', FALSE);
        //get the twtiter id from the session
        $twitterId = $session->get('twitterId', FALSE);
        //get the screen name from the session
        $screen_name = $session->get('screen_name', FALSE);
        //check if we got twitter data
        if ($oauth_token && $oauth_token_secret && $twitterId && $screen_name) {
            //get the user social account object
            $socialAccounts = $user->getSocialAccounts();
            //check if the user does not have a social account object
            if (!$socialAccounts) {
                //create new social account for the user
                $socialAccounts = new SocialAccounts();
                $socialAccounts->setUser($user);
                $user->setSocialAccounts($socialAccounts);
                $em->persist($socialAccounts);
            }
            //set the user twitter data
            $socialAccounts->setTwitterId($twitterId);
            $socialAccounts->setOauthToken($oauth_token);
            $socialAccounts->setOauthTokenSecret($oauth_token_secret);
            $socialAccounts->setScreenName($screen_name);
            //save the data for the user
            $em->flush();
            //set the success flag in the session
            $session->setFlash('success', $translator->trans('Your account is now linked to twitter'));
        } else {
            //something went wrong clear the session and set a flash to try again
            $session->clear();
            //set the error flag in the session
            $session->setFlash('error', $translator->trans('twitter connection error') . ' <a href="' . $this->generateUrl('twitter_authentication', array('redirectRoute' => 'fb_twitter_link'), TRUE) . '">' . $translator->trans('try again') . '</a>');
        }
        //twitter data not found go to the edit page
        return $this->redirect($this->generateUrl('fb_user_edit'));
    }

    /**
     * this action will unlink the user social data data
     * @author Mahmoud
     * @param string $social twitter | facebook
     */
    public function socialUnlinkAction($social) {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the logged in user object
        $user = $this->get('security.context')->getToken()->getUser();
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the user social account object
        $socialAccounts = $user->getSocialAccounts();
        if ($social == 'facebook') {
            //unlink the facebook account data
            $socialAccounts->unlinkFacebook();
        }
        if ($social == 'twitter') {
            //unlink the facebook account data
            $socialAccounts->unlinkTwitter();
        }
        if ($social == 'linkedin') {
            //unlink the linkedin account data
            $socialAccounts->unlinkLinkedIn();
        }
        //check if we still need the object
        if (!$socialAccounts->isNeeded()) {
            //remove the object
            $em->remove($socialAccounts);
        }
        //save the changes
        $em->flush();
        //set a success flag in the session
        $this->getRequest()->getSession()->setFlash('success', $this->get('translator')->trans('Unlinked successfully'));
        //redirect the user to the edit page
        return $this->redirect($this->generateUrl('fb_user_edit'));
    }

    /**
     * this action will give the user the ability to delete his account
     * it will not actually delete the account it will simply disable it
     * @author Mahmoud
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAccountAction() {
        //get the request object
        $request = $this->getRequest();
        //check if form is posted
        if ($request->getMethod() == 'POST') {
            //get the session object
            $session = $request->getSession();
            //get the user object from the firewall
            $user = $this->get('security.context')->getToken()->getUser();
            //set the delete flag
            $user->setEnabled(FALSE);
            //get the entity manager
            $em = $this->getDoctrine()->getEntityManager();
            //get the social accounts object
            $socialAccounts = $user->getSocialAccounts();
            //remove the social accounts object if exist
            if ($socialAccounts) {
                $em->remove($socialAccounts);
            }
            //save the changes
            $em->flush();
            //logout the user
            $this->get('security.context')->setToken(null);
            //invalidate the current user session
            $session->invalidate();
            //set the success flag
            $session->setFlash('success', $this->get('translator')->trans('Your account has been deleted'));
            //redirect to the login page
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        return $this->render('ObjectsUserBundle:FacebookUser:delete_account.html.twig');
    }

    /**
     * this function will check the login name againest the database if the name
     * does not exist the function will return the name otherwise it will try to return
     * a valid login Name
     * @author Alshimaa edited by Mahmoud
     * @param string $loginName
     * @return string a valid login name to use
     */
    private function suggestLoginName($loginName) {
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the user repo
        $userRepository = $em->getRepository('ObjectsUserBundle:User');
        //try to check if the given name does not exist
        $user = $userRepository->findOneByLoginName($loginName);
        if (!$user) {
            //valid login name
            return $loginName;
        }
        //get a valid one from the database
        return $userRepository->getValidLoginName($loginName);
    }

    /**
     * this action will link the user account to his linkedin account
     * @author Ahmed
     */
    public function linkedinLinkAction() {
        if (false === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //reload the user object from the database
        $user = $em->getRepository('ObjectsUserBundle:User')->getUserWithSocialAccounts($this->get('security.context')->getToken()->getUser()->getId());
        //get the request object
        $request = $this->getRequest();
        //get the translator object
        $translator = $this->get('translator');
        //get the session object
        $session = $request->getSession();
        //get the oauth token from the session
        $oauth_token = $session->get('oauth_token', FALSE);
        //get the oauth token secret from the session
        $oauth_token_secret = $session->get('oauth_token_secret', FALSE);
        //get linkedIn oauth array from the session
        $linkedIn_oauth = $session->get('oauth_linkedin', FALSE);
        //check if we got linkedin data
        if ($oauth_token && $oauth_token_secret) {
            //get the user data
            $userData = LinkedinController::getUserData($this->container->getParameter('linkedin_api_key'), $this->container->getParameter('linkedin_secret_key'), $linkedIn_oauth);
            //check if we get the user data
            if ($userData) {
                $userData = $userData['linkedin'];
                $userData = json_decode(json_encode((array) simplexml_load_string($userData)), 1);

                //get the user social account object
                $socialAccounts = $user->getSocialAccounts();
                //check if the user does not have a social account object
                if (!$socialAccounts) {
                    //create new social account for the user
                    $socialAccounts = new SocialAccounts();
                    $socialAccounts->setUser($user);
                    $user->setSocialAccounts($socialAccounts);
                    $em->persist($socialAccounts);
                }

                $socialAccounts->setLinkedinOauthToken($oauth_token);
                $socialAccounts->setLinkedinOauthTokenSecret($oauth_token_secret);
                $socialAccounts->setLinkedInId($userData['id']);


                //save the data for the user
                $em->flush();
                //set the success flag in the session
                $session->setFlash('success', $translator->trans('Your account is now linked to linkedin'));
            } else {
                //linkedIn data not found go to the edit page
                return $this->redirect($this->generateUrl('fb_user_edit'));
            }
        } else {
            //something went wrong clear the session and set a flash to try again
            $session->clear();
            //set the error flag in the session
            $session->setFlash('error', $translator->trans('linkedin connection error') . ' <a href="' . $this->generateUrl('linkedInButton', array('redirectRoute' => 'fb_linkedin_link'), TRUE) . '">' . $translator->trans('try again') . '</a>');
        }
        //linkedIn data not found go to the edit page
        return $this->redirect($this->generateUrl('fb_user_edit'));
    }

    /**
     * this function will receive user data and ask user to enter his email in case new user
     * or will signin the user in case linkedIn user
     * @author Ahmed <a.ibrahim@objects.ws>
     */
    public function linkedInUserDataAction() {
        //check that a logged in user can not access this action
        if (TRUE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            //go to the home page
            return $this->redirect('/');
        }

        //get the request object
        $request = $this->getRequest();
        //get the session object
        $session = $request->getSession();
        //get the translator object
        $translator = $this->get('translator');
        //get the oauth token from the session
        $oauth_token = $session->get('oauth_token', FALSE);
        //get the oauth token secret from the session
        $oauth_token_secret = $session->get('oauth_token_secret', FALSE);
        //get linkedIn oauth array from the session
        $linkedIn_oauth = $session->get('oauth_linkedin', FALSE);
        //check if we got linkedin data
        if ($oauth_token && $oauth_token_secret) {
            //get the user data
            $userData = LinkedinController::getUserData($this->container->getParameter('linkedin_api_key'), $this->container->getParameter('linkedin_secret_key'), $linkedIn_oauth);
            //check if we get the user data
            if ($userData) {
                $userData = $userData['linkedin'];
                $userData = json_decode(json_encode((array) simplexml_load_string($userData)), 1);
//print_r($userData);exit;
                //get the entity manager
                $em = $this->getDoctrine()->getEntityManager();
                $skillRepo = $em->getRepository('ObjectsInternJumpBundle:Skill');
                //check if the user linkedId id is in our database
                $socialAccounts = $em->getRepository('ObjectsUserBundle:SocialAccounts')->getSocialById($userData['id']);
                //check if we found the user
                if ($socialAccounts) {
                    //user found check if the access tokens have changed
                    if ($socialAccounts->getLinkedinOauthToken() != $oauth_token) {
                        //tokens changed update the tokens
                        $socialAccounts->setLinkedinOauthToken($oauth_token);
                        $socialAccounts->setLinkedinOauthTokenSecret($oauth_token_secret);
                        //save the new access tokens
                        $em->flush();
                    }
                    //get the user object
                    $user = $socialAccounts->getUser();
//                    var_dump($user);exit;
                    //try to login the user
                    try {
                        // create the authentication token
                        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                        // give it to the security context
                        $this->container->get('security.context')->setToken($token);
                        //redirect the user
                        return $this->redirectUserAction();
                    } catch (\Exception $e) {
                        //failed to login the user go to the login page
                        return $this->redirect($this->generateUrl('site_fb_homepage'));
                    }
                }

                /**
                 *
                 * the account of the same email as linkedin account maybe exist but not linked so we will link it
                 * and directly logging the user
                 * if the account is not active we automatically activate it
                 * else will create the account ,sign up the user
                 *
                 * */
                $userRepository = $this->getDoctrine()->getRepository('ObjectsUserBundle:User');
                $roleRepository = $this->getDoctrine()->getRepository('ObjectsUserBundle:Role');
                $user = $userRepository->findOneByEmail($userData['email-address']);
                //if user exist only add linkedin account to social accounts record if user have one
                //if not create new record
                if ($user) {
                    $socialAccounts = $user->getSocialAccounts();
                    if (empty($socialAccounts)) {
                        $socialAccounts = new SocialAccounts();
                        $socialAccounts->setUser($user);
                    }
                    $socialAccounts->setLinkedinOauthToken($oauth_token);
                    $socialAccounts->setLinkedinOauthTokenSecret($oauth_token_secret);
                    $socialAccounts->setLinkedInId($userData['id']);
                    $user->setSocialAccounts($socialAccounts);

                    //activate user if is not activated
                    //get object of notactive Role
                    $notActiveRole = $roleRepository->findOneByName('ROLE_NOTACTIVE');
                    if ($user->getUserRoles()->contains($notActiveRole)) {
                        //get a user role object
                        $userRole = $roleRepository->findOneByName('ROLE_USER');
                        //remove notactive Role from user in exist
                        $user->getUserRoles()->removeElement($notActiveRole);

                        $user->getUserRoles()->add($userRole);

                        $linkedInActivatedmessage = $this->get('translator')->trans('Your LinkedIN account was successfully Linked to your account') . ' ' . $this->get('translator')->trans('your account is now active');
                        //set flash message to tell user that him/her account has been successfully activated
                        $session->setFlash('notice', $linkedInActivatedmessage);
                    } else {
                        $linkedInDmessage = $this->get('translator')->trans('Your LinkedIN account was successfully Linked to your account');
                        //set flash message to tell user that him/her account has been successfully linked
                        $session->setFlash('notice', $linkedInDmessage);
                    }
                    $em->persist($user);
                    $em->flush();

                    //try to login the user
                    try {
                        // create the authentication token
                        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                        // give it to the security context
                        $this->get('security.context')->setToken($token);
                        //redirect the user
                        return $this->redirectUserAction();
                    } catch (\Exception $e) {
                        //can not reload the user object log out the user
                        $this->get('security.context')->setToken(null);
                        //invalidate the current user session
                        $this->getRequest()->getSession()->invalidate();
                        //redirect to the login page
                        return $this->redirect($this->generateUrl('site_fb_homepage'));
                    }
                }


                //create a new user object
                $user = new User();
                //get the container object
                $container = $this->container;
                $newUserName = '';
                //set the name
                if (isset($userData['first-name'])) {
                    $user->setFirstName($userData['first-name']);
                    $newUserName = $userData['first-name'];
                }
                if (isset($userData['last-name'])) {
                    $user->setLastName($userData['last-name']);
                    $newUserName .= '_' . $userData['last-name'];
                }
                //set a valid login name
                $user->setLoginName($this->suggestLoginName(strtolower($newUserName)));
                //set the profile url
                if (isset($userData['site-standard-profile-request']['url'])) {
                    $user->setUrl($userData['site-standard-profile-request']['url']);
                }
                //set the about text
                if (isset($userData['summary'])) {
                    $user->setAbout($userData['summary']);
                }
                //set user country code
                if (isset($userData['location']['country']['code'])) {
                    $user->setCountry(strtoupper($userData['location']['country']['code']));
                }
                //try to download the user image from linkedIn if user has one
                if (isset($userData['picture-url'])) {
                    $image = LinkedinController::downloadLinkedInImage($userData['picture-url'], $user->getUploadRootDir());
                    //check if we got an image
                    if ($image) {
                        //add the image to the user
                        $user->setImage($image);
                    }
                }
                //set the user email
                if (isset($userData['email-address'])) {
                    $user->setEmail($userData['email-address']);
                }
                //set the user dateOfBirth
                if (isset($userData['date-of-birth']) && isset($userData['date-of-birth']['year']) && isset($userData['date-of-birth']['month']) && isset($userData['date-of-birth']['day'])) {
                    $user->setDateOfBirth(new \DateTime($userData['date-of-birth']['year'] . '-' . $userData['date-of-birth']['month'] . '-' . $userData['date-of-birth']['day']));
                }
                //set the user adress
                if (isset($userData['main-address'])) {
                    $user->setAddress($userData['main-address']);
                }

                //check if the user have Employment History
                if (isset($userData['positions']['position']) && sizeof($userData['positions']['position']) > 0) {
                    if ($userData['positions']['@attributes']['total'] > 1) {
                        foreach ($userData['positions']['position'] as $position) {
                            $newPosition = new \Objects\InternJumpBundle\Entity\EmploymentHistory();
                            $newPosition->setUser($user);
                            $newPosition->setCompanyName($position['company']['name']);
                            $newPosition->setTitle($position['title']);
                            $newPosition->setStartedFrom(new \DateTime($position['start-date']['year'] . '-' . $position['start-date']['month']));

                            if (isset($position['end-date'])) {
                                $newPosition->setEndedIn(new \DateTime($position['end-date']['year'] . '-' . $position['start-date']['month']));
                            } else {
                                $newPosition->setIsCurrent(TRUE);
                            }

                            if (isset($position['company']['industry'])) {
                                //check if this category exist
                                $cvCategoryRepository = $this->getDoctrine()->getRepository('ObjectsInternJumpBundle:CVCategory');
                                $cat = $cvCategoryRepository->findOneBy(array('name' => $position['company']['industry']));
                                if ($cat)
                                    $newPosition->setIndustry($cat);
                            }

                            if (isset($position['summary'])) {
                                $newPosition->setDescription($position['summary']);
                            }

                            $em->persist($newPosition);
                        }
                    } elseif ($userData['positions']['@attributes']['total'] > 0) {
                        $newPosition = new \Objects\InternJumpBundle\Entity\EmploymentHistory();
                        $newPosition->setUser($user);
                        $newPosition->setCompanyName($userData['positions']['position']['company']['name']);
                        $newPosition->setTitle($userData['positions']['position']['title']);
                        $newPosition->setStartedFrom(new \DateTime($userData['positions']['position']['start-date']['year'] . '-' . $userData['positions']['position']['start-date']['month']));

                        if (isset($userData['positions']['position']['end-date'])) {
                            $newPosition->setEndedIn(new \DateTime($userData['positions']['position']['end-date']['year'] . '-' . $userData['positions']['position']['start-date']['month']));
                        } else {
                            $newPosition->setIsCurrent(TRUE);
                        }

                        if (isset($userData['positions']['position']['company']['industry'])) {
                            //check if this category exist
                            $cvCategoryRepository = $this->getDoctrine()->getRepository('ObjectsInternJumpBundle:CVCategory');
                            $cat = $cvCategoryRepository->findOneBy(array('name' => $userData['positions']['position']['company']['industry']));
                            if ($cat)
                                $newPosition->setIndustry($cat);
                        }

                        if (isset($userData['positions']['position']['summary'])) {
                            $newPosition->setDescription($userData['positions']['position']['summary']);
                        }

                        $em->persist($newPosition);
                    }
                }


                //check if the user have skills
                if (isset($userData['skills']['skill']) && sizeof($userData['skills']['skill']) > 0) {
                    if ($userData['skills']['@attributes']['total'] > 1) {
                        foreach ($userData['skills']['skill'] as $skill) {
                            //check if this skill exist
                            $skillObject = $skillRepo->findOneBy(array('title' => strtolower($skill['skill']['name'])));
                            if ($skillObject) {
                                $user->addSkill($skillObject);
                            } else {
                                //create new skill
                                $newSkill = new \Objects\InternJumpBundle\Entity\Skill();
                                $newSkill->setTitle(strtolower($skill['skill']['name']));
                                $user->addSkill($newSkill);
                            }
                        }
                    } elseif ($userData['skills']['@attributes']['total'] > 0) {
                        //check if this skill exist
                        $skillObject = $skillRepo->findOneBy(array('title' => strtolower($userData['skills']['skill']['skill']['name'])));
                        if ($skillObject) {
                            $user->addSkill($skillObject);
                        } else {
                            //create new skill
                            $newSkill = new \Objects\InternJumpBundle\Entity\Skill();
                            $newSkill->setTitle(strtolower($userData['skills']['skill']['skill']['name']));

                            $user->addSkill($newSkill);
                        }
                    }
                }


                //check if the user have educations
                if (isset($userData['educations']['education']) && sizeof($userData['educations']['education']) > 0) {
                    if ($userData['educations']['@attributes']['total'] > 1) {
                        foreach ($userData['educations']['education'] as $education) {
                            //create new eduction
                            $newEducation = new \Objects\InternJumpBundle\Entity\Education();
                            $newEducation->setSchoolName($education['school-name']);

                            if (isset($education['degree'])) {
                                $newEducation->setGraduateDegree($education['degree']);
                            }

                            if (isset($education['start-date'])) {
                                $newEducation->setStartDate($education['start-date']['year']);
                            }

                            if (isset($education['end-date'])) {
                                $newEducation->setEndDate($education['end-date']['year']);
                            }

                            $newEducation->setUser($user);
                            $user->addEducation($newEducation);
                        }
                    } elseif ($userData['educations']['@attributes']['total'] > 0) {
                        //create new eduction
                        $newEducation = new \Objects\InternJumpBundle\Entity\Education();
                        $newEducation->setSchoolName($userData['educations']['education']['school-name']);

                        if (isset($userData['educations']['education']['degree'])) {
                            $newEducation->setGraduateDegree($userData['educations']['education']['degree']);
                        }

                        if (isset($userData['educations']['education']['start-date'])) {
                            $newEducation->setStartDate($userData['educations']['education']['start-date']['year']);
                        }

                        if (isset($userData['educations']['education']['end-date'])) {
                            $newEducation->setEndDate($userData['educations']['education']['end-date']['year']);
                        }

                        $newEducation->setUser($user);
                        $user->addEducation($newEducation);
                    }
                }

                //create social accounts object
                $socialAccounts = new SocialAccounts();
                $socialAccounts->setLinkedinOauthToken($oauth_token);
                $socialAccounts->setLinkedinOauthTokenSecret($oauth_token_secret);
                $socialAccounts->setLinkedInId($userData['id']);
                $socialAccounts->setUser($user);
                //set the user linkedIn info
                $user->setSocialAccounts($socialAccounts);

                $em->persist($user);
                //set the user password in the session
                $session->set('user_password', $user->getUserPassword());
                //add user role
                //get a user role object
                $role = $roleRepository->findOneByName('ROLE_ACTIVE_SOCIAL');
                //get a update userName role object
                $roleUpdateUserName = $roleRepository->findOneByName('ROLE_UPDATABLE_USERNAME');
                //set user roles
                $user->addRole($role);
                $user->addRole($roleUpdateUserName);
                //store the object in the database
                $em->flush();
                //try to login the user
                try {
                    // create the authentication token
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    // give it to the security context
                    $this->get('security.context')->setToken($token);
                } catch (\Exception $e) {
                    //can not reload the user object log out the user
                    $this->get('security.context')->setToken(null);
                    //invalidate the current user session
                    $this->getRequest()->getSession()->invalidate();
                    //redirect to the login page
                    return $this->redirect($this->generateUrl('site_fb_homepage'));
                }

                return $this->redirect($this->generateUrl('fb_user_signup_second_step', array(), TRUE));
            } else {
                //linkedIn data not found go to the login page
                return $this->redirect($this->generateUrl('site_fb_homepage'));
            }
        } else {
            //linkedIn data not found go to the login page
            return $this->redirect($this->generateUrl('site_fb_homepage'));
        }
    }

    /**
     * this function will used to check if loginName is exist
     * @author Ahmed
     * @param string $loginName
     */
    public function loginNameCheckAction($loginName) {
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //reload the user object from the database
        $user = $em->getRepository('ObjectsUserBundle:User');

        $userObject = $user->findOneBy(array('loginName' => $loginName));
        if ($userObject) {
            return new Response('exist');
        } else {
            return new Response('not-exist');
        }
    }

    /**
     * this function is used to insert a new city or update the city priority if the city was found
     * @author Mahmoud
     * @param string $cityName
     * @param string $countryName
     * @param boolean $updateCity
     */
    private function insertCityIfNotFound($cityName, $countryName, $updateCity = false) {
        $em = $this->getDoctrine()->getEntityManager();
        $city = $em->getRepository('ObjectsInternJumpBundle:City')->findOneBy(array('name' => $cityName, 'country' => $countryName));
        if (!$city) {
            $country = $em->getRepository('ObjectsInternJumpBundle:Country')->find($countryName);
            if ($country) {
                $city = new City();
                $city->setCountry($country);
                $city->setName($cityName);
                $city->setSlug($cityName);
                $em->persist($city);
            }
        } else {
            if ($updateCity) {
                $em->getRepository('ObjectsInternJumpBundle:City')->increasePriority($city->getId());
            }
        }
        try {
            $em->flush();
        } catch (\Exception $e) {

        }
    }

    /**
     * the second step in the signup
     * @author Mahmoud
     * @return type
     */
    public function secondSignupAction() {
        $em = $this->getDoctrine()->getEntityManager();
        //get the request object
        $request = $this->getRequest();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        $countryRepo = $em->getRepository('ObjectsInternJumpBundle:Country');
        //get countries array
        $allCountries = $countryRepo->getAllCountries();
        $allCountriesArray = array();
        foreach ($allCountries as $value) {
            $allCountriesArray[$value['id']] = $value['name'];
        }
        //create a signup form
        $formBuilder = $this->createFormBuilder($user, array(
                    'validation_groups' => 'signup_second'
                ))
                ->add('firstName')
                ->add('lastName')
                ->add('gender', 'choice', array(
                    'choices' => array('1' => 'Male', '0' => 'Female'),
                    'multiple' => false,
                ))
                ->add('about')
                ->add('file', 'file', array('required' => false, 'label' => 'image', 'attr' => array('onchange' => 'readURL(this);')))
                ->add('url', 'url', array('required' => false))
                ->add('dateOfBirth', 'birthday', array('widget' => 'single_text', 'format' => 'yyyy-MM-dd', 'required' => FALSE, 'attr' => array('class' => 'dateOfBirth')))
                ->add('zipcode')
                ->add('country', 'choice', array('choices' => $allCountriesArray, 'required' => false))
                ->add('city')
                ->add('state', 'choice', array('required' => false))
                ->add('address');
        //create the form
        $form = $formBuilder->getForm();
        //check if this is the user posted his data
        if ($request->getMethod() == 'POST') {
            //fill the form data from the request
            $form->bindRequest($request);
            //check if the form values are correct
            if ($form->isValid()) {
                //get the user object from the form
                $user = $form->getData();

                //get the current user roles
                $userRoles = $user->getUserRoles();
                //define the role to remove
                $roleName = 'ROLE_NOTACTIVE_SOCIAL';
                $active = FALSE;
                if (TRUE === $this->get('security.context')->isGranted('ROLE_ACTIVE_SOCIAL')) {
                    //update the removed role name
                    $roleName = 'ROLE_ACTIVE_SOCIAL';
                    $active = TRUE;
                    //get a user role object
                    $roleUser = $em->getRepository('ObjectsUserBundle:Role')->findOneByName('ROLE_USER');
                    $user->addRole($roleUser);
                } else {
                    //get a user not active role object
                    $roleNotActive = $em->getRepository('ObjectsUserBundle:Role')->findOneByName('ROLE_NOTACTIVE');
                    $user->addRole($roleNotActive);
                }
                //try to fine the role to remove
                foreach ($userRoles as $key => $userRole) {
                    //check if this role is the not active role
                    if ($userRole->getName() == $roleName) {
                        //remove the not active role
                        $userRoles->remove($key);
                        //end the search
                        break;
                    }
                }
                //get the session object
                $session = $request->getSession();
                //get the user password from the session
                $userPassword = $session->get('user_password');
                //remove the user password form the session
                $session->remove('user_password');
                //save the data
                $em->flush();
                //prepare the body of the email
                $body = $this->renderView('ObjectsUserBundle:User:Emails\welcome_to_site.txt.twig', array(
                    'user' => $user,
                    'password' => $userPassword,
                    'active' => $active
                ));
                //prepare the message object
                $message = \Swift_Message::newInstance()
                        ->setSubject($this->get('translator')->trans('welcome'))
                        ->setFrom($this->container->getParameter('mailer_user'))
                        ->setTo($user->getEmail())
                        ->setBody($body)
                ;
                //send the email
                $this->get('mailer')->send($message);
                //check if user have social acounts
                $userSocialAccounts = $user->getSocialAccounts();
                $container = $this->container;
                if ($userSocialAccounts) {
                    $status = 'I just installed the InternJump App, a platform to find internships/entry-level jobs!';
                    $link = $this->generateUrl('site_fb_homepage', array(), true);

                    // check if have facebook
                    if ($userSocialAccounts->isFacebookLinked()) {
                        FacebookController::postOnUserWallAndFeedAction($userSocialAccounts->getFacebookId(), $userSocialAccounts->getAccessToken(), $status, null, null, $link, NULL);
                    }

                    // check if have twitter
                    if ($userSocialAccounts->isTwitterLinked()) {
                        TwitterController::twitt($status . ' ' . $link, $container->getParameter('consumer_key'), $container->getParameter('consumer_secret'), $userSocialAccounts->getOauthToken(), $userSocialAccounts->getOauthTokenSecret());
                    }

                    // check if have linkedin
                    if ($userSocialAccounts->isLinkedInLinked()) {
                        LinkedinController::linkedInShare($container->getParameter('linkedin_api_key'), $container->getParameter('linkedin_secret_key'), $userSocialAccounts->getLinkedinOauthToken(), $userSocialAccounts->getLinkedinOauthTokenSecret(), $status, $status, $status, $link, NULL);
                    }
                }
                //try to login the user
                try {
                    // create the authentication token
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    // give it to the security context
                    $this->get('security.context')->setToken($token);
                } catch (\Exception $e) {
                    //can not reload the user object log out the user
                    $this->get('security.context')->setToken(null);
                    //invalidate the current user session
                    $this->getRequest()->getSession()->invalidate();
                    //redirect to the login page
                    return $this->redirect($this->generateUrl('site_fb_homepage'));
                }
                return $this->redirect($this->generateUrl('fb_signup_education'));
            }
        }
        return $this->render('ObjectsUserBundle:FacebookUser:signup_second_step.html.twig', array(
                    'form' => $form->createView(),
                    'user' => $user,
                    'formName' => $this->container->getParameter('studentSignUp_FormName'),
                    'formDesc' => $this->container->getParameter('studentSignUp_FormDesc'),
        ));
    }

}

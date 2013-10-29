<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Objects\InternJumpBundle\Entity\Message;

/**
 * FacebookUserMessageController.
 * @author Mahmoud
 */
class FacebookUserMessageController extends Controller {

    /**
     * the inbox messages page action
     * @param string $box inbox|outbox
     * @param integer $page
     */
    public function messagesBoxAction($box, $page = 1) {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //get the messages per page count
        $itemsPerPage = $this->getRequest()->cookies->get('user_messages_per_page_' . $user->getId(), 10);
        return $this->render('ObjectsInternJumpBundle:FBMessage:user_box.html.twig', array(
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'box' => $box,
                    'type' => 'box'
                ));
    }

    /**
     * this function will get the user inbox messages
     * @param string $box inbox|outbox
     * @param integer $page
     * @param integer $itemsPerPage
     */
    public function getMessagesAction($box, $page, $itemsPerPage) {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //check if we do not have the items per page number
        if (!$itemsPerPage) {
            //get the items per page from cookie or the default value
            $itemsPerPage = $this->getRequest()->cookies->get('user_messages_per_page_' . $user->getId(), 10);
        }
        //initialize the messages box to the inbox
        $sentFromCompany = TRUE;
        //check if the requested box is the outbox
        if ($box == 'outbox') {
            $sentFromCompany = FALSE;
        }
        //get the user messages
        $data = $this->getDoctrine()->getEntityManager()->getRepository('ObjectsInternJumpBundle:Message')->getUserMessagesData($user->getId(), $sentFromCompany, $page, $itemsPerPage);
        $entities = $data['entities'];
        $count = $data['count'];
        //calculate the last page number
        $lastPageNumber = (int) ($count / $itemsPerPage);
        if (($count % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }
        return $this->render('ObjectsInternJumpBundle:FBMessage:user_messages.html.twig', array(
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'count' => $count,
                    'lastPageNumber' => $lastPageNumber,
                    'box' => $box,
                    'entities' => $entities,
                    'userId' => $user->getId()
                ));
    }

    /**
     * this function will execute the batch actions on the current user selected messages
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function messagesBatchAction() {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //get the request object
        $request = $this->getRequest();
        //get all the messages
        $messagesId = $request->get('userMessages');
        //get the current box
        $box = $request->get('box');
        //check if we have any messages
        if (!$messagesId || count($messagesId) == 0) {
            if ($request->isXmlHttpRequest()) {
                return new Response('Nothing to do');
            }
            return $this->redirect($this->generateUrl('fb_user_box', array('box' => $box)));
        }
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the messages objects
        $messages = $em->getRepository('ObjectsInternJumpBundle:Message')->getUserMessagesByIds($messagesId, $user->getId());
        //check if we have any messages
        if (count($messages) == 0) {
            if ($request->isXmlHttpRequest()) {
                return new Response('Nothing to do');
            }
            return $this->redirect($this->generateUrl('fb_user_box', array('box' => $box)));
        }
        //determine the batch action to use
        if ($request->get('readAllMessages')) {
            foreach ($messages as $message) {
                //mark only the inbox messages as read
                if ($message->getSentFromCompany()) {
                    $message->setIsRead(TRUE);
                }
            }
        } else {
            if ($request->get('deleteAllMessages')) {
                foreach ($messages as $message) {
                    //delete the message from the user part
                    $message->setUserDeleted(TRUE);
                    //check if we still need the message object
                    if (!$message->isMessageNeeded()) {
                        //remove the message from the database
                        $em->remove($message);
                    }
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    return new Response('Nothing to do');
                }
                return $this->redirect($this->generateUrl('fb_user_box', array('box' => $box)));
            }
        }
        //save to the database
        $em->flush();
        if ($request->isXmlHttpRequest()) {
            return new Response('Done');
        }
        return $this->redirect($this->generateUrl('fb_user_box', array('box' => $box)));
    }

    /**
     * this function is used to generate a delete form for a message
     * @param integer $id
     * @return type
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

    /**
     * this function is used to get a valid message object from the database and mark it as read
     * @param integer $id
     * @return $entity \Objects\InternJumpBundle\Entity\Message
     * @throws NotFoundException
     * @throws AccessDeniedHttpException
     */
    private function getValidMessageObject($id) {
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //try to select the message from the database
        try {
            $entity = $em->getRepository('ObjectsInternJumpBundle:Message')->getUserMessage($id);
        } catch (\Exception $e) {
            $message = $this->container->getParameter('user_message_not_found_error_msg');
            throw $this->createNotFoundException($message);
        }
        //check if the message is deleted
        if ($entity->getUserDeleted()) {
            $message = $this->container->getParameter('user_message_not_found_error_msg');
            throw $this->createNotFoundException($message);
        }
        //check if the user can see the message
        if ($entity->getUser()->getId() != $user->getId()) {
            throw new AccessDeniedHttpException('You can not see a message that is not yours');
        }
        //mark the message as readed if not readed and if it is sent to the user
        if (!$entity->getIsRead() && $entity->getSentFromCompany()) {
            //mark the message as readed and save the flag in database
            $entity->setIsRead(TRUE);
            $em->flush();
        }
        return $entity;
    }

    /**
     * show Message page.
     * @param integer $id
     */
    public function showAction($id) {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the message object if valid
        $entity = $this->getValidMessageObject($id);
        //initialize the go back url to inbox
        $box = 'inbox';
        //check if this message is from outbox
        if (!$entity->getSentFromCompany()) {
            $box = 'outbox';
        }
        return $this->render('ObjectsInternJumpBundle:FBMessage:user_box.html.twig', array(
                    'box' => $box,
                    'messageId' => $entity->getId(),
                    'type' => 'show'
                ));
    }

    /**
     * Finds and displays a Message entity.
     * @param integer $id
     */
    public function getMessageAction($id) {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the message object if valid
        $entity = $this->getValidMessageObject($id);
        //create a delete form
        $deleteForm = $this->createDeleteForm($id);
        //initialize the go back url to inbox
        $box = 'inbox';
        //check if this message is from outbox
        if (!$entity->getSentFromCompany()) {
            $box = 'outbox';
        }
        return $this->render('ObjectsInternJumpBundle:FBMessage:user_message.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
                    'box' => $box
                ));
    }

    /**
     * Deletes a Message entity.
     * @param integer $id
     */
    public function deleteAction($id) {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //try to select the message from the database
        try {
            $entity = $em->getRepository('ObjectsInternJumpBundle:Message')->getUserMessage($id);
        } catch (\Exception $e) {
            $message = $this->container->getParameter('user_message_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }
        //check if the message is deleted
        if ($entity->getUserDeleted()) {
            $message = $this->container->getParameter('user_message_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
        }
        //check if the user can delete the message
        if ($entity->getUser()->getId() != $user->getId()) {
            throw new AccessDeniedHttpException('You can not see a message that is not yours');
        }
        //create the delete form
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();
        $form->bindRequest($request);
        if ($form->isValid()) {
            //delete the message from the user part
            $entity->setUserDeleted(TRUE);
            //check if we still need the message object
            if (!$entity->isMessageNeeded()) {
                //remove the message from the database
                $em->remove($entity);
            }
            $em->flush();
        }
        if ($request->isXmlHttpRequest()) {
            return new Response('Done');
        }
        $request->getSession()->setFlash('success', 'Deleted Successfuly');
        return $this->redirect($this->generateUrl('fb_user_messages', array('box' => 'inbox')));
    }

    /**
     * this function is used to get the valid companies that the user can send to
     * @param integer $userId
     * @return array
     */
    private function getValidCompaniesToSendTo($userId) {
        //get the company interests
        $interests = $this->getDoctrine()->getRepository('ObjectsInternJumpBundle:Interest')->getCompanyInterests($userId);
        $companies = array();
        foreach ($interests as $interest) {
            $companies [$interest->getCompany()->getId()] = $interest->getCompany();
        }
        return $companies;
    }

    /**
     * this function is used to generate a message form
     * @param type $entity
     * @param type $userName
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws 404 if the user to send to is not found
     */
    private function createNewMessageForm($entity, $userName = NULL) {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the user object
        $user = $this->get('security.context')->getToken()->getUser();
        //get the valid companies to send to
        $companies = $this->getValidCompaniesToSendTo($user->getId());
        //check if the user can send messages to any one
        if (count($companies) == 0) {
            return new Response('You can not send messages to companies until they show interest in you');
        }
        $entity->setUser($user);
        $entity->setSentFromCompany(FALSE);
        //check if we have a user to send to
        if ($userName) {
            //check if the requested user exist
            $company = $em->getRepository('ObjectsInternJumpBundle:Company')->findOneByLoginName($userName);
            if (!$company) {
                $message = $this->container->getParameter('company_not_found_error_msg');
                return $this->render('ObjectsInternJumpBundle:Internjump:fb_general.html.twig', array(
                        'message' => $message,));
            }
            //check if the user can send this company messages
            if (!isset($companies[$company->getId()])) {
                throw new AccessDeniedHttpException('You can not send messages to this company until it show interest in you');
            }
            //set the default company
            $entity->setCompany($company);
        }
        return $this->createFormBuilder($entity)
                        ->add('company', 'entity', array('class' => 'ObjectsInternJumpBundle:Company', 'choices' => $companies, 'attr' => array('class' => 'chzn-select')))
                        ->add('title')
                        ->add('message')
                        ->getForm();
    }

    /**
     * the create new message page
     * @param string $userName
     * @return type
     */
    public function newAction($userName = NULL) {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //check if we need to throw an exception
        $this->createNewMessageForm(new Message(), $userName);
        return $this->render('ObjectsInternJumpBundle:FBMessage:user_box.html.twig', array(
                    'box' => 'compose message',
                    'type' => 'compose',
                    'userName' => $userName
                ));
    }

    /**
     * Displays a form to create a new Message entity.
     * @param string $userName the user name to send to
     */
    public function newMessageFormAction($userName = NULL) {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //create a default object
        $entity = new Message();
        $form = $this->createNewMessageForm($entity, $userName);
        //check if we have a response object
        if ($form instanceof Response) {
            //return the response
            return $form;
        }
        return $this->render('ObjectsInternJumpBundle:FBMessage:user_new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
                ));
    }

    /**
     * Creates a new Message entity.
     * @param string $userName the user name to send to
     */
    public function createAction($userName = NULL) {
        if (FALSE === $this->get('security.context')->isGranted('ROLE_NOTACTIVE')) {
            $this->getRequest()->getSession()->set('redirectUrl', $this->getRequest()->getRequestUri());
            return $this->redirect($this->generateUrl('login'));
        }
        //create a default object
        $entity = new Message();
        $form = $this->createNewMessageForm($entity, $userName);
        //check if we have a response object
        if ($form instanceof Response) {
            return $form;
        }
        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();
                InternjumpController::companyNotificationMail($this->container, $entity->getUser(), $entity->getCompany(), 'user_message', $entity->getId());
                return $this->redirect($this->generateUrl('fb_show_user_message', array('id' => $entity->getId())));
            }
        }
        return $this->render('ObjectsInternJumpBundle:FBMessage:user_new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
                ));
    }

}

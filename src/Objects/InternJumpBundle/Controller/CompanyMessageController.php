<?php

namespace Objects\InternJumpBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Objects\InternJumpBundle\Entity\Message;

/**
 * CompanyMessage controller.
 * @author Mahmoud
 */
class CompanyMessageController extends Controller {

    /**
     * the inbox messages page action
     * @param string $box inbox|outbox
     * @param integer $page
     */
    public function messagesBoxAction($box, $page = 1) {
        //get the company object
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }
        //get the messages per page count
        $itemsPerPage = $this->getRequest()->cookies->get('company_messages_per_page_' . $company->getId(), 10);
        return $this->render('ObjectsInternJumpBundle:Message:box.html.twig', array(
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'box' => $box,
                    'type' => 'box'
        ));
    }

    /**
     * this function will get the company inbox messages
     * @param string $box inbox|outbox
     * @param integer $page
     * @param integer $itemsPerPage
     */
    public function getMessagesAction($box, $page, $itemsPerPage) {
        //get the company object
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }
        //check if we do not have the items per page number
        if (!$itemsPerPage) {
            //get the items per page from cookie or the default value
            $itemsPerPage = $this->getRequest()->cookies->get('company_messages_per_page_' . $company->getId(), 10);
        }
        //initialize the messages box to the inbox
        $sentFromCompany = FALSE;
        //check if the requested box is the outbox
        if ($box == 'outbox') {
            $sentFromCompany = TRUE;
        }
        //get the company messages
        $data = $this->getDoctrine()->getEntityManager()->getRepository('ObjectsInternJumpBundle:Message')->getCompanyMessagesData($company->getId(), $sentFromCompany, $page, $itemsPerPage);
        $entities = $data['entities'];
        $count = $data['count'];
        //calculate the last page number
        $lastPageNumber = (int) ($count / $itemsPerPage);
        if (($count % $itemsPerPage) > 0) {
            $lastPageNumber++;
        }
        return $this->render('ObjectsInternJumpBundle:Message:messages.html.twig', array(
                    'page' => $page,
                    'itemsPerPage' => $itemsPerPage,
                    'count' => $count,
                    'lastPageNumber' => $lastPageNumber,
                    'box' => $box,
                    'entities' => $entities,
                    'companyId' => $company->getId()
        ));
    }

    /**
     * this function will execute the batch actions on the current company selected messages
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function messagesBatchAction() {
        //get the company object
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }
        //get the request object
        $request = $this->getRequest();
        //get all the messages
        $messagesId = $request->get('companyMessages');
        //get the current box
        $box = $request->get('box');
        //check if we have any messages
        if (!$messagesId || count($messagesId) == 0) {
            if ($request->isXmlHttpRequest()) {
                return new Response('Nothing to do');
            }
            return $this->redirect($this->generateUrl('company_box', array('box' => $box)));
        }
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the messages objects
        $messages = $em->getRepository('ObjectsInternJumpBundle:Message')->getCompanyMessagesByIds($messagesId, $company->getId());
        //check if we have any messages
        if (count($messages) == 0) {
            if ($request->isXmlHttpRequest()) {
                return new Response('Nothing to do');
            }
            return $this->redirect($this->generateUrl('company_box', array('box' => $box)));
        }
        //determine the batch action to use
        if ($request->get('readAllMessages')) {
            foreach ($messages as $message) {
                //mark only the inbox messages as read
                if (!$message->getSentFromCompany()) {
                    $message->setIsRead(TRUE);
                }
            }
        } else {
            if ($request->get('deleteAllMessages')) {
                foreach ($messages as $message) {
                    //delete the message from the company part
                    $message->setCompanyDeleted(TRUE);
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
                return $this->redirect($this->generateUrl('company_box', array('box' => $box)));
            }
        }
        //save to the database
        $em->flush();
        if ($request->isXmlHttpRequest()) {
            return new Response('Done');
        }
        return $this->redirect($this->generateUrl('company_box', array('box' => $box)));
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
        //get the company object
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }
        //try to select the message from the database
        try {
            $entity = $em->getRepository('ObjectsInternJumpBundle:Message')->getMessage($id);
        } catch (\Exception $e) {
            $message = $this->container->getParameter('company_message_not_found_error_msg');
            throw $this->createNotFoundException($message);
        }
        //check if the message is deleted
        if ($entity->getCompanyDeleted()) {
            $message = $this->container->getParameter('company_message_not_found_error_msg');
            throw $this->createNotFoundException($message);
        }
        //check if the company can see the message
        if ($entity->getCompany()->getId() != $company->getId()) {
            throw new AccessDeniedHttpException('You can not see a message that is not yours');
        }
        //mark the message as readed if not readed and if it is sent to the company
        if (!$entity->getIsRead() && !$entity->getSentFromCompany()) {
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
        //get the message object if valid
        $entity = $this->getValidMessageObject($id);
        //initialize the go back url to inbox
        $box = 'inbox';
        //check if this message is from outbox
        if ($entity->getSentFromCompany()) {
            $box = 'outbox';
        }
        return $this->render('ObjectsInternJumpBundle:Message:box.html.twig', array(
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
        //get the message object if valid
        $entity = $this->getValidMessageObject($id);
        //create a delete form
        $deleteForm = $this->createDeleteForm($id);
        //initialize the go back url to inbox
        $box = 'inbox';
        //check if this message is from outbox
        if ($entity->getSentFromCompany()) {
            $box = 'outbox';
        }
        return $this->render('ObjectsInternJumpBundle:Message:message.html.twig', array(
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
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the company object
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }
        //try to select the message from the database
        try {
            $entity = $em->getRepository('ObjectsInternJumpBundle:Message')->getMessage($id);
        } catch (\Exception $e) {
            $message = $this->container->getParameter('company_message_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }
        //check if the message is deleted
        if ($entity->getCompanyDeleted()) {
            $message = $this->container->getParameter('company_message_not_found_error_msg');
            return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                        'message' => $message,));
        }
        //check if the company can delete the message
        if ($entity->getCompany()->getId() != $company->getId()) {
            throw new AccessDeniedHttpException('You can not see a message that is not yours');
        }
        //create the delete form
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();
        $form->bindRequest($request);
        if ($form->isValid()) {
            //delete the message from the company part
            $entity->setCompanyDeleted(TRUE);
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
        return $this->redirect($this->generateUrl('company_messages', array('box' => 'inbox')));
    }

    /**
     * this function is used to get the valid users that the company can send to
     * @param integer $companyId
     * @return array
     */
    private function getValidUsersToSendTo($companyId) {
        //get the company accepted interests
        $interests = $this->getDoctrine()->getRepository('ObjectsInternJumpBundle:Interest')->getCompanyAcceptedInterests($companyId);
        $users = array();
        foreach ($interests as $interest) {
            $users [$interest->getUser()->getId()] = $interest->getUser();
        }
        return $users;
    }

    /**
     * this function is used to generate a message form
     * @param type $entity
     * @param type $userName
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws 404 if the user to send to is not found
     */
    private function createNewMessageForm($entity, $userName = NULL) {
        //get the entity manager
        $em = $this->getDoctrine()->getEntityManager();
        //get the company object
        if (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY')) {
            $company = $this->get('security.context')->getToken()->getUser();
        } elseif (TRUE === $this->get('security.context')->isGranted('ROLE_COMPANY_MANAGER')) {
            $manager = $this->get('security.context')->getToken()->getUser();
            $company = $manager->getCompany();
        }
        //get the valid users to send to
        $users = $this->getValidUsersToSendTo($company->getId());
        //check if the company can send messages to any one
        if (count($users) == 0) {
            return new Response('You can not send messages to students until they accept your interest');
        }
        $entity->setCompany($company);
        //check if we have a user to send to
        if ($userName) {
            //check if the requested user exist
            $user = $em->getRepository('ObjectsUserBundle:User')->findOneByLoginName($userName);
            if (!$user) {
                $message = $this->container->getParameter('user_not_found_error_msg');
                return $this->render('ObjectsInternJumpBundle:Internjump:general.html.twig', array(
                            'message' => $message,));
            }
            //check if the company can send this user messages
            if (!isset($users[$user->getId()])) {
                throw new AccessDeniedHttpException('You can not send messages to this student until he/she accepts your interest');
            }
            //set the default user
            $entity->setUser($user);
        }
        return $this->createFormBuilder($entity)
                        ->add('user', 'entity', array('class' => 'ObjectsUserBundle:User', 'choices' => $users, 'attr' => array('class' => 'chzn-select')))
                        ->add('title')
                        ->add('message')
                        ->getForm();
    }

    /**
     * the create new message page
     * @param string $userName
     * @return type
     */
    public function newAction() {
        $userName = null;
        if ($this->getRequest()->get('usename'))
            $userName = $this->getRequest()->get('usename');
        //check if we need to throw an exception
        $this->createNewMessageForm(new Message(), $userName);
        return $this->render('ObjectsInternJumpBundle:Message:box.html.twig', array(
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
        //create a default object
        $entity = new Message();
        $form = $this->createNewMessageForm($entity, $userName);
        //check if we have a response object
        if ($form instanceof Response) {
            //return the response
            return $form;
        }
        return $this->render('ObjectsInternJumpBundle:Message:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    /**
     * Creates a new Message entity.
     * @param string $userName the user name to send to
     */
    public function createAction($userName = NULL) {
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
                InternjumpController::userNotificationMail($this->container, $entity->getUser(), $entity->getCompany(), 'company_message', $entity->getId());
                return $this->redirect($this->generateUrl('show_company_message', array('id' => $entity->getId())));
            }
        }
        return $this->render('ObjectsInternJumpBundle:Message:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

}

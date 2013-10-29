<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * MessageRepository
 * @author Mahmoud
 */
class MessageRepository extends EntityRepository {

        /**
     * This Query to Get User's latest 3 Messages
     * Used: in user portal page  [TaskController:studentAllTasksAction] AT studentTasks.html.twig
     * @author Ola
     */
    public function getLatestThree($userId) {

        $parmeters = array();

        $parmeters ['id'] = $userId;

        $query = "
                SELECT m
                FROM ObjectsInternJumpBundle:Message m
                JOIN m.user u
                WHERE u.id = :id
                ORDER BY m.createdAt DESC
                ";
        $query = $this->getEntityManager()->createQuery($query);
        $query->setParameters($parmeters);
        $query->setMaxResults(3);

        return $query->getResult();
    }

    /**
     * this function is used to get the company messages
     * the main use in CompanyMessageController:getMessagesAction
     * @param integer $companyId
     * @param boolean $sentFromCompany
     * @param integer $page
     * @param integer $maxResults
     * @return array the count index contains the count of returned objects, the entities index contains the objects
     */
    public function getCompanyMessagesData($companyId, $sentFromCompany = FALSE, $page = 1, $maxResults = 10) {
        $data = array(
            'count' => 0,
            'enities' => array()
        );
        if ($page >= 1) {
            $page--;
            $mainQuery = '
                FROM ObjectsInternJumpBundle:Message m
                JOIN m.user u
                JOIN m.company c
                WHERE c.id = :companyId
                AND m.companyDeleted = 0
                AND m.sentFromCompany = :sentFromCompany
                ORDER BY m.createdAt DESC
                ';
            $parameters = array('companyId' => $companyId, 'sentFromCompany' => $sentFromCompany);
            $query = $this->getEntityManager()->createQuery("SELECT m, u, c $mainQuery")->setParameters($parameters);
            $countQuery = $this->getEntityManager()->createQuery("SELECT COUNT(m) $mainQuery")->setParameters($parameters);
            $query->setFirstResult($page * $maxResults);
            $query->setMaxResults($maxResults);
            $result = $countQuery->getResult();
            $data['count'] = $result[0][1];
            $data['entities'] = $query->getResult();
        }
        return $data;
    }

    /**
     * this function is used to get the messages objects by it is ids
     * the main use in CompanyMessageController:messagesBatchAction
     * @param array $messagesIds
     * @param integer $companyId
     * @return array
     */
    public function getCompanyMessagesByIds(array $messagesIds, $companyId) {
        if (count($messagesIds) == 0) {
            return array();
        }
        $query = $this->getEntityManager()->createQuery("
            SELECT m
            FROM ObjectsInternJumpBundle:Message m
            JOIN m.company c
            WHERE c.id = :companyId
            AND m.id IN(:messagesIds)
            ")->setParameters(array('messagesIds' => $messagesIds, 'companyId' => $companyId));
        return $query->getResult();
    }

    /**
     * this function will get one message object from the database
     * the main use in CompanyMessageController:showAction
     * second use in CompanyMessageController:deleteAction
     * @param type $id
     * @return type
     * throw NoResultException
     */
    public function getMessage($id) {
        $query = $this->getEntityManager()->createQuery("
            SELECT m
            FROM ObjectsInternJumpBundle:Message m
            JOIN m.company c
            JOIN m.user u
            WHERE m.id = :id
            ")->setParameter('id', $id);
        return $query->getSingleResult();
    }

    /**
     * this function return the new messages count for a company
     * @param integer $companyId
     * @return integer the number of new messages
     */
    public function getCompanyNewMessagesCount($companyId) {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(m)
            FROM ObjectsInternJumpBundle:Message m
            JOIN m.company c
            WHERE m.isRead = 0
            AND m.sentFromCompany = 0
            AND c.id = :companyId
            ")->setParameter('companyId', $companyId);
        $result = $query->getResult();
        return $result[0][1];
    }

    /**
     * this function is used to get the user messages
     * the main use in UserMessageController:getMessagesAction
     * @param integer $userId
     * @param boolean $sentFromCompany
     * @param integer $page
     * @param integer $maxResults
     * @return array the count index contains the count of returned objects, the entities index contains the objects
     */
    public function getUserMessagesData($userId, $sentFromCompany = FALSE, $page = 1, $maxResults = 10) {
        $data = array(
            'count' => 0,
            'enities' => array()
        );
        if ($page >= 1) {
            $page--;
            $mainQuery = '
                FROM ObjectsInternJumpBundle:Message m
                JOIN m.user u
                JOIN m.company c
                WHERE u.id = :userId
                AND m.userDeleted = 0
                AND m.sentFromCompany = :sentFromCompany
                ORDER BY m.createdAt DESC
                ';
            $parameters = array('userId' => $userId, 'sentFromCompany' => $sentFromCompany);
            $query = $this->getEntityManager()->createQuery("SELECT m, u, c $mainQuery")->setParameters($parameters);
            $countQuery = $this->getEntityManager()->createQuery("SELECT COUNT(m) $mainQuery")->setParameters($parameters);
            $query->setFirstResult($page * $maxResults);
            $query->setMaxResults($maxResults);
            $result = $countQuery->getResult();
            $data['count'] = $result[0][1];
            $data['entities'] = $query->getResult();
        }
        return $data;
    }

    /**
     * this function is used to get the messages objects by it is ids
     * the main use in UserMessageController:messagesBatchAction
     * @param array $messagesIds
     * @param integer $userId
     * @return array
     */
    public function getUserMessagesByIds(array $messagesIds, $userId) {
        if (count($messagesIds) == 0) {
            return array();
        }
        $query = $this->getEntityManager()->createQuery("
            SELECT m
            FROM ObjectsInternJumpBundle:Message m
            JOIN m.user u
            WHERE u.id = :userId
            AND m.id IN(:messagesIds)
            ")->setParameters(array('messagesIds' => $messagesIds, 'userId' => $userId));
        return $query->getResult();
    }

    /**
     * this function will get one message object from the database
     * the main use in UserMessageController:showAction
     * second use in UserMessageController:deleteAction
     * @param type $id
     * @return type
     * throw NoResultException
     */
    public function getUserMessage($id) {
        $query = $this->getEntityManager()->createQuery("
            SELECT m
            FROM ObjectsInternJumpBundle:Message m
            JOIN m.company c
            JOIN m.user u
            WHERE m.id = :id
            ")->setParameter('id', $id);
        return $query->getSingleResult();
    }

    /**
     * this function return the new messages count for a user
     * @param integer $userId
     * @return integer the number of new messages
     */
    public function getUserNewMessagesCount($userId) {
        $query = $this->getEntityManager()->createQuery("
            SELECT COUNT(m)
            FROM ObjectsInternJumpBundle:Message m
            JOIN m.user u
            WHERE m.isRead = 0
            AND m.sentFromCompany = 1
            AND u.id = :userId
            ")->setParameter('userId', $userId);
        $result = $query->getResult();
        return $result[0][1];
    }

}
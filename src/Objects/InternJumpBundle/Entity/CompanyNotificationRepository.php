<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CompanyNotificationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CompanyNotificationRepository extends EntityRepository {

    /**
     * this function will get company notifications
     * @author Ahmed
     * @param int $companyId
     * @param int $page
     * @param int $itemsPerPage
     * @param string $type
     */
    public function getCompanyNotifications($companyId, $type = null, $page, $itemsPerPage) {
        if ($page < 1) {
            return array();
        }
        $page--;
        $query = "
                SELECT n
                FROM ObjectsInternJumpBundle:CompanyNotification n
                JOIN n.company c
                WHERE c.id = :id
                ";
        
        $parmeters = array();
        
        if($type){
            $query .= ' and n.type = :type';
            $parmeters ['type'] = $type;
        }
        
        $query .= ' order by n.createdAt desc';
        
        $parmeters ['id'] = $companyId;
        
        $query = $this->getEntityManager()->createQuery($query);
        $query->setParameters($parmeters);
        
        if ($itemsPerPage) {
            $query->setFirstResult($page * $itemsPerPage);
            $query->setMaxResults($itemsPerPage);
        }
        
        return $query->getResult();
    }

    /**
     * this function will count all company notifications 
     * @author Ahmed
     * @param int $companyId
     */
    public function countAllCompanyNotifications($companyId) {
        $query = "
                SELECT count(n.id) as notficiationsCount
                FROM ObjectsInternJumpBundle:CompanyNotification n
                JOIN n.company c
                WHERE c.id = :id and n.isNew = true
                ";
        $parmeters ['id'] = $companyId;
        $query = $this->getEntityManager()->createQuery($query);
        $query->setParameters($parmeters);
        $result = $query->getResult();
        if ($result) {
            return $result['0']['notficiationsCount'];
        } else {
            return $result;
        }
    }

    /**
     * this function will count company notifications by group by notification type
     * @author Ahmed
     * @param int $companyId
     */
    public function countCompanyNotifications($companyId) {
        $query = "
                SELECT count(n.id) as notficiationsCount,n.type
                FROM ObjectsInternJumpBundle:CompanyNotification n
                JOIN n.company c
                WHERE c.id = :id and n.isNew = true
                group by n.type
                ";
        $parmeters ['id'] = $companyId;
        $query = $this->getEntityManager()->createQuery($query);
        $query->setParameters($parmeters);
        return $query->getResult();
    }

}
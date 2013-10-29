<?php

namespace Objects\InternJumpBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of SkillRepository
 *
 */
class SkillRepository extends EntityRepository {

    /**
     * this function will increase the skill users count
     * main use in SkillController:signupCVSkillsAction
     * @author Mahmoud
     * @param integer $skillId
     */
    public function increaseSkillUsersCount($skillId) {
        $this->getEntityManager()
                ->createQuery("
                    UPDATE ObjectsInternJumpBundle:Skill s
                    SET s.usersCount = s.usersCount + 1
                    WHERE s.id = :skillId")->setParameter('skillId', $skillId)->execute();
    }

    /**
     * this function will search for skill by it is title
     * the main use in SkillController:getSkillsAction
     * @author Mahmoud
     * @param string $skillTitle
     * @param integer $maxResults
     * @return array of skills
     */
    public function getSkills($skillTitle, $maxResults) {
        $em = $this->getEntityManager();
        $query = $em->createQuery("
                SELECT s.title
                FROM ObjectsInternJumpBundle:Skill s
                WHERE s.title LIKE :title
                ORDER BY s.usersCount DESC
                ");
        $query->setParameters(array('title' => '%' . $skillTitle . '%'));
        $query->setMaxResults($maxResults);
        $result = $query->getResult();
        return $result;
    }

    /**
     * This query to List All Skills for certain Student
     * @author Ola
     * @param int $studentId
     * @return array of skills
     */
    public function getStudentAllSkills($studentId) {
        $em = $this->getEntityManager();
        $para = array();

        $query = $em->createQuery("
                SELECT s.id, s.title
                FROM ObjectsUserBundle:User u
                JOIN u.skills s
                WHERE u.id = :id
                ");
        $para['id'] = $studentId;
        $query->setParameters($para);

        $result = $query->getResult();

        return $result;
    }

}

?>

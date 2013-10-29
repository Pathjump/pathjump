<?php

namespace Objects\InternJumpBundle\Validator\Constrains;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GlobalUniqueValidator extends ConstraintValidator {

    private $entityManager;

    public function __construct($doctrine) {
        $this->entityManager = $doctrine->getEntityManager();
    }

    public function isValid($object, Constraint $constraint) {
        //initialize the login name to valid
        $valid = TRUE;
        //check if we have a login name
        if ($object->getLoginName()) {
            //check if we have a user with that login name
            $user = $this->entityManager->getRepository('ObjectsUserBundle:User')->findOneByLoginName($object->getLoginName());
            if ($user) {
                //check if the current validated object is different than the one we found
                if (get_class($user) != get_class($object)) {
                    $valid = FALSE;
                } else {
                    //check if the object we found is not the same as the one we validate
                    if ($user->getId() != $object->getId()) {
                        $valid = FALSE;
                    }
                }
            }
            //check if we have a company with that login name
            $company = $this->entityManager->getRepository('ObjectsInternJumpBundle:Company')->findOneByLoginName($object->getLoginName());
            if ($company) {
                //check if the current validated object is different than the one we found
                if (get_class($company) != get_class($object)) {
                    $valid = FALSE;
                } else {
                    //reset valid to true incase the user if set it to false
                    $valid = TRUE;
                    //check if the object we found is not the same as the one we validate
                    if ($company->getId() != $object->getId()) {
                        $valid = FALSE;
                    }
                }
            }
            //check if we have a manager with that login name
            $manager = $this->entityManager->getRepository('ObjectsInternJumpBundle:Manager')->findOneByLoginName($object->getLoginName());
            if ($manager) {
                //check if the current validated object is different than the one we found
                if (get_class($manager) != get_class($object)) {
                    $valid = FALSE;
                } else {
                    //reset valid to true incase the user if set it to false
                    $valid = TRUE;
                    //check if the object we found is not the same as the one we validate
                    if ($manager->getId() != $object->getId()) {
                        $valid = FALSE;
                    }
                }
            }
            //check if the login name is not valid
            if (!$valid) {
                $propertyPath = $this->context->getPropertyPath() . 'loginName';
                $this->context->setPropertyPath($propertyPath);
                $this->context->addViolation($constraint->message, array(), null);
            }
        }
        return $valid;
    }

}

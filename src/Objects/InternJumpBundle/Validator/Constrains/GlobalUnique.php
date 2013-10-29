<?php

namespace Objects\InternJumpBundle\Validator\Constrains;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class GlobalUnique extends Constraint {

    public $message = 'The login name is not unique';

    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy() {
        return 'objectsunique';
    }

}

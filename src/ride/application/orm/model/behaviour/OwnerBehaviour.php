<?php

namespace ride\application\orm\model\behaviour;

use \ride\library\orm\model\behaviour\OwnerBehaviour as LibOwnerBehaviour;
use \ride\library\orm\model\Model;

/**
 * Behaviour to keep the owner of a entry
 */
class OwnerBehaviour extends LibOwnerBehaviour {

    /**
     * Flag to see if the owner is a user object instead of a username
     * @var boolean
     */
    protected $useUser;

    /**
     * Constructs a new behaviour
     * @param boolean $useUser Set to true to use the User object instead of the
     * username
     */
    public function __construct($useUser = false) {
        $this->useUser = $useUser;
    }

    /**
     * Gets the owner for this behavious
     * @param \ride\library\orm\model\Model $model
     * @return string|\ride\library\security\model\User
     */
    protected function getOwner(Model $model) {
        if ($this->useUser) {
            return $model->getOrmManager()->getUser();
        }

        return $model->getOrmManager()->getUserName();
    }

}

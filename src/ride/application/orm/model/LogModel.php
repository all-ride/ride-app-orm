<?php

namespace ride\application\orm\model;

use ride\library\orm\model\LogModel as LibLogModel;

/**
 * Model for logging model actions
 */
class LogModel extends LibLogModel {

    /**
     * Gets the name of the current user
     * @return string|null
     */
    protected function getUser() {
        $dependencyInjector = $this->orm->getDependencyInjector();
        $securityManager = $dependencyInjector->get('ride\\library\\security\\SecurityManager');

        $user = $securityManager->getUser();
        if ($user) {
            return $user->getUserName();
        }

        return null;
    }

}
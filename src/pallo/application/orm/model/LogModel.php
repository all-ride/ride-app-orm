<?php

namespace pallo\application\orm\model;

use pallo\library\orm\model\LogModel as LibLogModel;

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
        $securityManager = $dependencyInjector->get('pallo\\library\\security\\SecurityManager');

        $user = $securityManager->getUser();
        if ($user) {
            return $user->getUserName();
        }

        return null;
    }

}
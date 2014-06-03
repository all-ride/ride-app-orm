<?php

namespace ride\application\orm\model;

use ride\library\orm\model\EntryLogModel as LibEntryLogModel;

/**
 * Model for logging model actions
 */
class EntryLogModel extends LibEntryLogModel {

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

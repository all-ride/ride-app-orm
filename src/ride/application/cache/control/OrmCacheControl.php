<?php

namespace ride\application\cache\control;

use ride\library\orm\OrmManager;

/**
 * Cache control implementation for the ORM
 */
class OrmCacheControl extends AbstractCacheControl {

    /**
     * Name of this cache control
     * @var string
     */
    const NAME = 'orm';

    /**
     * Instance of the ORM manager
     * @var \ride\library\orm\OrmManager
     */
    private $orm;

    /**
     * Constructs a new ORM cache control
     * @param \ride\library\orm\OrmManager $orm
     * @return null
     */
    public function __construct(OrmManager $orm) {
        $this->orm = $orm;
    }

    /**
     * Gets whether this cache is enabled
     * @return boolean
     */
    public function isEnabled() {
        $modelCache = $this->orm->getModelCache();
        $queryCache = $this->orm->getQueryCache();
        $resultCache = $this->orm->getResultCache();

        return $modelCache || $queryCache || $resultCache;
    }

    /**
	 * Clears this cache
	 * @return null
     */
    public function clear() {
        $this->orm->clearCache();
    }

}

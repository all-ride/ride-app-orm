<?php

namespace pallo\application\cache\control;

use pallo\application\cache\control\AbstractCacheControl;

use pallo\library\orm\OrmManager;

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
     * @var pallo\library\orm\OrmManager
     */
    private $orm;

    /**
     * Constructs a new ORM cache control
     * @param pallo\library\orm\OrmManager $orm
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
        $modelCache = $orm->getModelCache();
        $queryCache = $orm->getQueryCache();
        $resultCache = $orm->getResultCache();

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
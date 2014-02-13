<?php

namespace pallo\application\orm;

use pallo\library\database\DatabaseManager;
use pallo\library\dependency\DependencyInjector;
use pallo\library\log\Log;
use pallo\library\orm\loader\ModelLoader;
use pallo\library\orm\model\data\format\DataFormatter;
use pallo\library\orm\OrmManager as LibOrmManager;
use pallo\library\reflection\ReflectionHelper;
use pallo\library\validation\factory\ValidationFactory;

/**
 * Pallo integration for the ORM manager
 */
class OrmManager extends LibOrmManager {

    /**
     * Instance of Zibo
     * @var pallo\library\dependency\DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Array with the available locales, the locale code as key
     * @var array
     */
    protected $locales;

    /**
     * Constructs a new ORM manager
     * @param pallo\library\reflection\ReflectionHelper $reflectionHelper
     * @param pallo\library\database\DatabaseManager $databaseManager
     * @param pallo\library\orm\loader\ModelLoader $modelLoader
     * @param pallo\library\dependency\DependencyInjector $dependencyInjector
     * @return null
     */
    public function __construct(ReflectionHelper $reflectionHelper, DatabaseManager $databaseManager, ModelLoader $modelLoader, ValidationFactory $validationFactory, DependencyInjector $dependencyInjector) {
        parent::__construct($reflectionHelper, $databaseManager, $modelLoader, $validationFactory);

        $this->dependencyInjector = $dependencyInjector;
        $this->locales = null;
    }

    /**
     * Gets the dependency injector
     * @return pallo\library\dependency\DependencyInjector
     */
    public function getDependencyInjector() {
        return $this->dependencyInjector;
    }

    /**
     * Sets the instance of the log
     * @param pallo\library\log\Log $log
     * @return null
     */
    public function setLog(Log $log) {
        $this->log = $log;
    }

    /**
     * Gets the instance of the log
     * @return pallo\library\log\Log|null
     */
    public function getLog() {
        return $this->log;
    }

    /**
     * Gets the instance of the data formatter
     * @return \pallo\library\orm\DataFormatter
     */
    public function getDataFormatter() {
        if (!$this->dataFormatter) {
            $modifiers = $this->dependencyInjector->getAll('pallo\\library\\orm\\model\\data\\format\\modifier\\DataFormatModifier');

            $this->dataFormatter = new DataFormatter($this->reflectionHelper, $modifiers);
        }

        return $this->dataFormatter;
    }

    /**
     * Gets an array with the available locales
     * @return array Array with the locale code as key
     */
    public function getLocales() {
        if ($this->locales !== null) {
            return $this->locales;
        }

        $i18n = $this->dependencyInjector->get('pallo\\library\\i18n\\I18n');

        $this->locales = $i18n->getLocaleCodeList();

        return $this->locales;
    }

    /**
     * Gets the current locale
     * @return string Code of the locale
     */
    public function getLocale() {
        $i18n = $this->dependencyInjector->get('pallo\\library\\i18n\\I18n');

        return $i18n->getLocale()->getCode();
    }

}
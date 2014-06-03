<?php

namespace ride\application\orm;

use ride\library\database\DatabaseManager;
use ride\library\dependency\DependencyInjector;
use ride\library\log\Log;
use ride\library\orm\loader\ModelLoader;
use ride\library\orm\model\data\format\DataFormatter;
use ride\library\orm\OrmManager as LibOrmManager;
use ride\library\reflection\ReflectionHelper;
use ride\library\validation\factory\ValidationFactory;

/**
 * Ride integration for the ORM manager
 */
class OrmManager extends LibOrmManager {

    /**
     * Instance of Zibo
     * @var \ride\library\dependency\DependencyInjector
     */
    protected $dependencyInjector;

    /**
     * Array with the available locales, the locale code as key
     * @var array
     */
    protected $locales;

    /**
     * Constructs a new ORM manager
     * @param \ride\library\database\DatabaseManager $databaseManager
     * @param \ride\library\orm\loader\ModelLoader $modelLoader
     * @param \ride\library\validation\factory\ValidationFactory $validationFactory
     * @param \ride\library\dependency\DependencyInjector $dependencyInjector
     * @param string $defaultNamespace
     * @return null
     */
    public function __construct(DatabaseManager $databaseManager, ModelLoader $modelLoader, ValidationFactory $validationFactory, DependencyInjector $dependencyInjector, $defaultNamespace) {
        parent::__construct($dependencyInjector->getReflectionHelper(), $databaseManager, $modelLoader, $validationFactory);

        $this->dependencyInjector = $dependencyInjector;
        $this->defaultNamespace = $defaultNamespace;
        $this->locales = null;
    }

    /**
     * Gets the dependency injector
     * @return \ride\library\dependency\DependencyInjector
     */
    public function getDependencyInjector() {
        return $this->dependencyInjector;
    }

    /**
     * Sets the instance of the log
     * @param \ride\library\log\Log $log
     * @return null
     */
    public function setLog(Log $log) {
        $this->log = $log;
    }

    /**
     * Gets the instance of the log
     * @return \ride\library\log\Log|null
     */
    public function getLog() {
        return $this->log;
    }

    /**
     * Gets the instance of the data formatter
     * @return \ride\library\orm\entry\format\EntryFormatter
     */
    public function getEntryFormatter() {
        if (!$this->entryFormatter) {
            $this->entryFormatter = $this->dependencyInjector->get('ride\\library\\orm\\entry\\format\\EntryFormatter');
        }

        return $this->entryFormatter;
    }

    /**
     * Gets an array with the available locales
     * @return array Array with the locale code as key
     */
    public function getLocales() {
        if ($this->locales !== null) {
            return $this->locales;
        }

        $i18n = $this->dependencyInjector->get('ride\\library\\i18n\\I18n');

        $this->locales = $i18n->getLocaleCodeList();

        return $this->locales;
    }

    /**
     * Gets the current locale
     * @return string Code of the locale
     */
    public function getLocale() {
        $i18n = $this->dependencyInjector->get('ride\\library\\i18n\\I18n');

        return $i18n->getLocale()->getCode();
    }

}

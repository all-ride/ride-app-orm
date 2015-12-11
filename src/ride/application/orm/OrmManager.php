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
     * Default locale
     * @var string
     */
    protected $locale;

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
        $this->locale = null;
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
     * @param string $locale Locale to get the relative locales prioritied
     * @return array Array with the locale code as key
     */
    public function getLocales($locale = null) {
        if ($this->locales === null) {
            $this->fetchLocales();
        }

        if ($locale === null) {
            $locale = $this->getLocale();
        }

        if (strpos($locale, '_')) {
            $language = substr($locale, 0, 2);
        } else {
            $language = $locale;
        }

        $preferred = array();
        $locales = array();

        foreach ($this->locales as $locale) {
            if (substr($locale, 0, 2) === $language) {
                $preferred[$locale] = $locale;
            } else {
                $locales[$locale] = $locale;
            }
        }

        return $preferred + array($language => $language) + $locales;
    }

    private function fetchLocales() {
        $i18n = $this->dependencyInjector->get('ride\\library\\i18n\\I18n');

        $this->locales = $i18n->getLocaleCodeList();
    }

    /**
     * Gets the current locale
     * @return string Code of the locale
     */
    public function getLocale() {
        if ($this->locale) {
            return $this->locale;
        }

        $i18n = $this->dependencyInjector->get('ride\\library\\i18n\\I18n');

        return $i18n->getLocale()->getCode();
    }

    /**
     * Sets the default locale for the ORM
     * @param string $locale Code of the locale
     * @return null
     */
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * Gets the current user
     * @return \ride\library\security\model\User|null
     */
    public function getUser() {
        $securityManager = $this->dependencyInjector->get('ride\\library\\security\\SecurityManager');

        return $securityManager->getUser();
    }

    /**
     * Gets the username of the current user
     * @return string|null
     */
    public function getUserName() {
        $user = $this->getUser();
        if ($user) {
            return $user->getUserName();
        }

        return null;
    }

}

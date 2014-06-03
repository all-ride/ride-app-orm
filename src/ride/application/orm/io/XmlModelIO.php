<?php

namespace ride\application\orm\io;

use ride\library\dependency\DependencyInjector;
use ride\library\orm\loader\io\AbstractXmlModelIO;
use ride\library\reflection\ReflectionHelper;
use ride\library\system\file\browser\FileBrowser;
use ride\library\system\file\File;

/**
 * Read and write model definitions from and to an xml structure in the Zibo
 * file system structure
 */
class XmlModelIO extends AbstractXmlModelIO {

    /**
     * Filename of the xml model definition in the Zibo file system structure
     * @var string
     */
    const FILE_MODELS = 'config/models.xml';

    /**
     * Instance of the file browser
     * @var ride\library\system\file\browser\FileBrowser
     */
    private $fileBrowser;

    /**
     * Instance of the dependency injector
     * @var ride\library\dependency\DependencyInjector
     */
    private $dependencyInjector;

    /**
     * Instance of the validation factory
     * @var ride\library\validation\factory\ValidationFactory
     */
    private $validationFactory;

    /**
     * Constructs a new model IO
     * @param ride\library\system\file\browser\FileBrowser $fileBrowser
     * @return null
     */
    public function __construct(ReflectionHelper $reflectionHelper, FileBrowser $fileBrowser, DependencyInjector $dependencyInjector, $defaultNamespace) {
        parent::__construct($reflectionHelper);

        $this->fileBrowser = $fileBrowser;
        $this->dependencyInjector = $dependencyInjector;
        $this->validationFactory = $dependencyInjector->get('ride\\library\\validation\\factory\\ValidationFactory');
        $this->defaultNamespace = $defaultNamespace;
    }

    /**
     * Read models from the data source
     * @return array Array with the name of the model as key and an instance of
     * Model as value
     * @see ride\library\orm\model\Model
     */
    public function readModels() {
        $models = array();

        $files = array_reverse($this->fileBrowser->getFiles(self::FILE_MODELS));
        foreach ($files as $file) {
            $models = $this->readModelsFromFile($file) + $models;
        }

        return $models;
    }

    /**
     * Gets the default entry class name for the provided model
     * @param string $modelName
     * @return string
     */
    protected function getEntryClassName($modelName) {
        return $this->defaultNamespace . '\\' . $modelName . 'Entry';
    }

    /**
     * Gets the default entry proxy class name for the provided model
     * @param string $modelName
     * @return string
     */
    protected function getProxyClassName($modelName) {
        return $this->defaultNamespace . '\\proxy\\' . $modelName . 'EntryProxy';
    }

    /**
     * Creates an instance of a validator
     * @param string $name Name of the validator
     * @param array $options Options for the validator
     * @return ride\library\validation\validator\Validator
     */
    protected function createValidator($name, $options) {
        return $this->validationFactory->createValidator($name, $options);
    }

    /**
     * Write the models to the data source
     * @param array $models Array with the name of the model as key and an
     * instance of Model as value
     * @return null
     * @see ride\library\orm\model\Model
     */
    public function writeModels(array $models) {

    }

}

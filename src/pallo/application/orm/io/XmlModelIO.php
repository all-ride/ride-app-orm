<?php

namespace pallo\application\orm\io;

use pallo\library\dependency\DependencyInjector;
use pallo\library\orm\loader\io\AbstractXmlModelIO;
use pallo\library\reflection\ReflectionHelper;
use pallo\library\system\file\browser\FileBrowser;
use pallo\library\system\file\File;

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
     * @var pallo\library\system\file\browser\FileBrowser
     */
    private $fileBrowser;

    /**
     * Instance of the dependency injector
     * @var pallo\library\dependency\DependencyInjector
     */
    private $dependencyInjector;

    /**
     * Instance of the validation factory
     * @var pallo\library\validation\factory\ValidationFactory
     */
    private $validationFactory;

    /**
     * Constructs a new model IO
     * @param pallo\library\system\file\browser\FileBrowser $fileBrowser
     * @return null
     */
    public function __construct(ReflectionHelper $reflectionHelper, FileBrowser $fileBrowser, DependencyInjector $dependencyInjector) {
        parent::__construct($reflectionHelper);

        $this->fileBrowser = $fileBrowser;
        $this->dependencyInjector = $dependencyInjector;
        $this->validationFactory = $dependencyInjector->get('pallo\\library\\validation\\factory\\ValidationFactory');
    }

    /**
     * Read models from the data source
     * @return array Array with the name of the model as key and an instance of
     * Model as value
     * @see pallo\library\orm\model\Model
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
     * Creates an instance of a validator
     * @param string $name Name of the validator
     * @param array $options Options for the validator
     * @return pallo\library\validation\validator\Validator
     */
    protected function createValidator($name, $options) {
        return $this->validationFactory->createValidator($name, $options);
    }

    /**
     * Write the models to the data source
     * @param array $models Array with the name of the model as key and an
     * instance of Model as value
     * @return null
     * @see pallo\library\orm\model\Model
     */
    public function writeModels(array $models) {

    }

}
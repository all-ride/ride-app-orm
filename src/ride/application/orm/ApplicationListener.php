<?php

namespace ride\application\orm;

use ride\library\event\Event;
use ride\library\orm\entry\EntryProxy;
use ride\library\orm\meta\ModelMeta;
use ride\library\orm\model\GenericModel;
use ride\library\reflection\Boolean;
use ride\library\reflection\ReflectionHelper;
use ride\library\system\file\browser\FileBrowser;

use \Exception;

/**
 * Application listener for the ORM
 */
class ApplicationListener {

    /**
     * Model option for the files delete flag
     * @var string
     */
    const OPTION_FILES_DELETE = 'files.delete';

    protected ReflectionHelper $reflectionHelper;

    protected FileBrowser $fileBrowser;

    protected array $eventNames;

    protected array $values;

    /**
     * Constructs a new ORM application listener
     * @param \ride\library\reflection\ReflectionHelper $reflectionHelper
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser
     * @return null
     */
    public function __construct(ReflectionHelper $reflectionHelper, FileBrowser $fileBrowser) {
        $this->reflectionHelper = $reflectionHelper;
        $this->fileBrowser = $fileBrowser;

        $this->eventNames = array(
            GenericModel::EVENT_UPDATE_PRE,
            GenericModel::EVENT_UPDATE_POST,
            GenericModel::EVENT_DELETE_POST,
        );
        $this->values = array();
    }

    /**
     * Deletes all files in the entry log for models with the files.delete
     * option enabled
     * @param \ride\library\orm\OrmManager $orm
     * @param boolean $isDryRun
     * @return array Array with the path of the file as key and a boolean value
     * to determine whether the file is deleted
     */
    public function deleteOldFiles(OrmManager $orm, $isDryRun = false) {
        $fileValues = array();
        $fileProperties = array();

        $logChangeModel = $orm->getEntryLogChangeModel();
        $models = $orm->getModels();

        // retrieve all history values
        foreach ($models as $modelName => $model) {
            $meta = $model->getMeta();

            $isLogged = $meta->getOption('behaviour.log') ? true : false;
            $fileProperties[$modelName] = $this->isFileDeletionEnabled($meta, $this->getFileProperties($meta));

            if (!$isLogged || !$fileProperties[$modelName]) {
                $fileProperties[$modelName] = null;

                continue;
            }

            $page = 1;
            $limit = 1000;

            do {
                $query = $logChangeModel->createQuery();
                $query->addJoin('INNER', 'EntryLog', 'el', '{entryLog} = {el.id}');
                $query->addCondition('{el.model} = %1% AND {fieldName} IN %2%', $model->getName(), array_keys($fileProperties[$modelName]));
                $query->setLimit($limit, ($page - 1) * $limit);

                $logChanges = $query->query();
                foreach ($logChanges as $logChange) {
                    $oldValue = $logChange->getOldValue();
                    $newValue = $logChange->getNewValue();

                    if ($oldValue) {
                        $fileValues[$oldValue] = $oldValue;
                    }
                    if ($newValue) {
                        $fileValues[$newValue] = $newValue;
                    }
                }

                $page++;
            } while ($logChanges);
        }

        // remove current values
        foreach ($models as $modelName => $model) {
            if (!$fileProperties[$modelName]) {
                continue;
            }

            $reflectionHelper = $model->getReflectionHelper();
            $page = 1;
            $limit = 1000;

            do {
                $query = $model->createQuery(null);
                $query->setFetchUnlocalized(true);
                $query->setLimit($limit, ($page - 1) * $limit);

                $entries = $query->query();
                foreach ($entries as $entry) {
                    foreach ($fileProperties[$modelName] as $filePropertyName => $fileProperty) {
                        $currentValue = $reflectionHelper->getProperty($entry, $filePropertyName);
                        if (!$currentValue || !isset($fileValues[$currentValue])) {
                            continue;
                        }

                        unset($fileValues[$currentValue]);
                    }
                }

                $page++;
            } while ($entries);
        }

        // delete all remaining values
        foreach ($fileValues as $fileValue => $file) {
            $file = $this->fileBrowser->getFile($fileValue);
            if ($file) {
                $status = false;

                if (!$isDryRun) {
                    try {
                        $file->delete();
                        $status = true;
                    } catch (Exception $exception) {

                    }
                }

                $fileValues[$fileValue] = $status;
            } else {
                unset($fileValues[$fileValue]);
            }
        }

        return $fileValues;
    }

    /**
     * Deletes the obsolete files references from ORM entries
     * @param \ride\library\event\Event $event Post delete event
     * @return null
     */
    public function handleObsoleteFiles(Event $event) {
        if (!in_array($event->getName(), $this->eventNames)) {
            return;
        }

        $model = $event->getArgument('model');
        $meta = $model->getMeta();
        $fileProperties = $this->isFileDeletionEnabled($meta, $this->getFileProperties($meta));

        if (!$fileProperties) {
            return;
        }

        $entry = $event->getArgument('entry');

        // act on the incoming event
        switch ($event->getName()) {
            case GenericModel::EVENT_UPDATE_PRE:
                $this->rememberFiles($model->getName(), $entry, $fileProperties);

                break;
            case GenericModel::EVENT_UPDATE_POST:
                $this->deleteChangedFiles($model->getName(), $entry, $fileProperties);

                break;
            case GenericModel::EVENT_DELETE_POST:
                $this->deleteFiles($entry, $fileProperties);

                break;
            default:
                return;
        }
    }

    /**
     * Keeps the file values of the provided entry for the post update event
     * @param string $modelName
     * @param mixed $entry
     * @param array $fileProperties
     * @return null
     */
    private function rememberFiles($modelName, $entry, array $fileProperties) {
        if (!$entry instanceof EntryProxy) {
            return;
        }

        // @todo get old value instead of current value
        $properties = array();
        foreach ($fileProperties as $filePropertyName => $fileProperty) {
            if (!$entry->isValueLoaded($filePropertyName)) {
                continue;
            }

            $properties[$filePropertyName] = $entry->getLoadedValues($filePropertyName);
        }

        $this->values[$modelName][$entry->getId()] = $properties;
    }

    /**
     * Deletes the remembered file values of the pre update event
     * @param string $modelName
     * @param mixed $entry
     * @param array $fileProperties
     * @return null
     */
    private function deleteChangedFiles($modelName, $entry, array $fileProperties) {
        foreach ($fileProperties as $filePropertyName => $fileProperty) {
            $currentValue = $this->reflectionHelper->getProperty($entry, $filePropertyName);
            if (isset($this->values[$modelName][$entry->getId()][$filePropertyName])) {
                $rememberedValue = $this->values[$modelName][$entry->getId()][$filePropertyName];
            } else {
                $rememberedValue = null;
            }

            if (!$rememberedValue || $rememberedValue === $currentValue) {
                continue;
            }

            $file = $this->fileBrowser->getFile($rememberedValue);
            if ($file) {
                $file->delete();
            }
        }

        if (isset($this->values[$modelName][$entry->getId()])) {
            unset($this->values[$modelName][$entry->getId()]);
        }
    }

    /**
     * Deletes the file values of a deleted entry
     * @param mixed $entry
     * @param array $fileProperties
     * @return null
     */
    private function deleteFiles($entry, array $fileProperties) {
        foreach ($fileProperties as $filePropertyName => $fileProperty) {
            $file = $this->reflectionHelper->getProperty($entry, $filePropertyName);
            if (!$file) {
                continue;
            }

            $file = $this->fileBrowser->getFile($file);
            if ($file) {
                $file->delete();
            }
        }
    }

    /**
     * Checks whether files should be physically removed from the file system
     * @param \ride\library\orm\meta\ModelMeta $meta
     * @param array $fileProperties Array with the name of the property as key
     * and an instance of ModelField as value
     * @return array|boolean Provided $fileProperties containing only the
     * properties for which file deletion is enabled
     */
    private function isFileDeletionEnabled(ModelMeta $meta, array $fileProperties) {
        if (!$fileProperties) {
            // no file properties, file deletion is disabled
            return false;
        }

        // determine default value
        $default = $meta->getOption('behaviour.log') ? false : true;
        $default = $this->checkFileDeletionOption($meta->getOption(self::OPTION_FILES_DELETE), $default);

        // check individual properties and remove property from list if file
        // deletetion is disabled for the property
        foreach ($fileProperties as $filePropertyName => $fileProperty) {
            $option = $fileProperty->getOption(self::OPTION_FILES_DELETE);
            if (!$this->checkFileDeletionOption($option, $default)) {
                unset($fileProperties[$filePropertyName]);
            }
        }

        // return properties with file deletion enabled
        return $fileProperties;
    }

    /**
     * Checks the file deletion option
     * @param mixed $option Value of a model or field option
     * @return boolean True if file deletion is enabled, false otherwise
     */
    private function checkFileDeletionOption($option, $default = true) {
        if ($option === null) {
            return $default;
        }

        return Boolean::getBoolean($option);
    }

    /**
     * Gets all the file properties of a model
     * @param \ride\library\orm\model\meta\ModelMeta $meta
     * @return array Array with the name of the property as key and an instance
     * of ModelField as value
     */
    private function getFileProperties(ModelMeta $meta) {
        $fileProperties = array();

        $properties = $meta->getProperties();
        foreach ($properties as $propertyName => $property) {
            $type = $property->getType();
            if ($type == 'file' || $type == 'image') {
                $fileProperties[$propertyName] = $property;
            }
        }

        if ($meta->getName() == 'Asset') {
            $fileProperties['value'] = $properties['value'];
        }

        return $fileProperties;
    }

}

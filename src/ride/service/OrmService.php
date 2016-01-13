<?php

namespace ride\service;

use ride\library\orm\definition\field\ModelField;
use ride\library\orm\definition\field\PropertyField;
use ride\library\orm\model\Model;
use ride\library\orm\OrmManager;
use ride\library\i18n\translator\Translator;

/**
 * Service to help with ORM handling
 */
class OrmService {

    /**
     * Instance of the ORM manager
     * @var \ride\library\orm\OrmManager
     */
    protected $orm;

    /**
     * Constructs a new ORM service
     * @param \ride\library\orm\OrmManager $ormManager
     * @return null
     */
    public function __construct(OrmManager $ormManager) {
        $this->orm = $ormManager;
    }

    /**
     * Gets a model from the ORM
     * @param string $modelName Name of the model
     * @return \ride\library\orm\model\Model
     */
    public function getModel($modelName) {
        return $this->orm->getModel($modelName);
    }

    /**
     * Gets the options for the provided field
     * @param \ride\library\orm\model\Model $model
     * @param \ride\library\orm\definition\field\ModelField $field
     * @param \ride\library\i18n\translator\Translator $translator
     * @param mixed $entry
     * @return array
     */
    public function getFieldInputOptions(Model $model, ModelField $field, Translator $translator, $entry = null) {
        if (!$field instanceof PropertyField) {
            $relationModel = $model->getRelationModel($field->getName());

            $condition = $field->getOption('scaffold.form.condition');
            if ($condition) {
                $query = $relationModel->createQuery($translator->getLocale());
                $query->setFetchUnlocalized(true);

                if (!$entry) {
                    $entry = $relationModel->createEntry();
                }

                $variables = array();

                $reflectionHelper = $this->model->getReflectionHelper();
                $meta = $this->model->getMeta();
                $properties = $meta->getProperties();
                $belongsTo = $meta->getBelongsTo();

                foreach ($properties as $name => $propertyField) {
                    $variables[$name] = $reflectionHelper->getProperty($entry, $name);
                }

                foreach ($belongsTo as $name => $belongsToField) {
                    $value = $reflectionHelper->getProperty($entry, $name);
                    if (!$value) {
                        continue;
                    }

                    if (is_object($value)) {
                        $variables[$name] = $value->getId();
                    } else {
                        $variables[$name] = $value;
                    }
                }

                $query->addConditionWithVariables($condition, $variables);

                $relationMeta = $relationModel->getMeta();
                $orderField = $relationMeta->getOption('order.field');
                if ($orderField) {
                    $query->addOrderBy('{' . $orderField . '} ' . $relationMeta->getOption('order.direction', 'ASC'));
                }

                $inputOptions = $relationModel->getOptionsFromEntries($query->query());
            } else {
                $inputOptions = $relationModel->getEntryList($translator->getLocale(), true);
            }
        } else {
            $optionMethod = $field->getOption('scaffold.form.options.method');
            if ($optionMethod) {
                $inputOptions = $this->model->$optionMethod($translator);
            } else {
                $inputOptions = $field->getOption('scaffold.form.options.json');
                if ($inputOptions) {
                    $inputOptions = json_decode($selectOptions, true);
                    foreach ($inputOptions as $index => $value) {
                        $inputOptions[$index] = $translator->translate($value);
                    }
                } else {
                    $inputOptions = array();
                }
            }
        }

        return $inputOptions;
    }

}

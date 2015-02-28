The ORM library takes care of the mapping between your database tables and you PHP objects.

## Model Definition

The model definition contains all the meta data about your model.
It's used by the ORM to implement the mapping between your database and your objects.

### Fields

Each model consists out of at least one field.

#### Property Fields

Property fields are fields which have a scalar or primitive type.
This means, the type of the field can directly mapped to a variable in your code.

The following property field types are available:

* string
* text
* boolean
* integer
* float
* date
* datetime
* email
* website
* password
* wysiwyg
* file
* image
* binary

#### Relation Fields

Relation fields have no primitive type.
They are represented by another model.
There are different types of relations.

##### Belongs To

A _belongsTo_ relation type is the easiest relation.

A simple example would be a comment for a blog post.
The comment model would have a belongsTo field with the blog model as related model.

For this relation, no extra tables are needed.
The field _blog_  is added to the comment table.
It contains the id of the blog item.

##### Has One

A _hasOne_ relation type is simular to the _belongsTo_ relation with the exception of how it's handled on database level.
For a hasOne relation type, a link table is needed since no field for the relation is stored in the table of the linking model.

##### Has Many

The _hasMany_ relation is in most cases the other side of a _belongsTo_ relation.

In the blog-comment example, a blog post has many comments.
Therefor a hasMany field with the comment model as related model could be added to the blog model.
That way, you can query for a blog post with all it's comments.

##### Has Many To Many

The other case for a _hasMany_ relation is when you have a relation of multiple records.

A example for this could be when you start to tag your blog posts.
A blog post could have multiple tags and a tag can be used for multiple blog posts.

You would have a tag model with a _hasMany_ field with the blog model as related model.
The blog model on his side, would have a _hasMany_ field with the tag model as related model.

#### Validators

Validators are the definitions for the actual [validators of the validation library](/manual/page/Library/Validation#validators).

You can add these validators to any field in order to validate on your model level.

### Behaviours

You can add extra behaviour to your models through the [ride\library\orm\model\behaviour\Behaviour](/api/class/ride/library/orm/model/behaviour/Behaviour) interface.
Check the implementations of the library for an example on how to create your own.

To hook your behaviour in your model, check the following example:

    namespace vendor\app\model;

    use ride\library\orm\model\behaviour\DatedBehaviour;
    use ride\library\orm\model\GenericModel;

    class RegistrationModel extends GenericModel {

        protected function initialize() {
            $this->addBehaviour(new DatedBehaviour());
        }

    }

#### Dated Behaviour

The [ride\library\orm\model\behaviour\DatedBehaviour](/api/class/ride/library/orm/model/behaviour/DatedBehaviour) implementation will maintain the _dateAdded_ and _dateModified_ fields of your data with the time of the insert or update action.

To use this behaviour, you have to:

* define a _dateAdded_ and/or _dateModified_ property field with the _date_ or _datetime_ type in your model
* add the behaviour in your _initialize_ method of your model, see the [example](#behaviours)

#### Log Behaviour

The [ride\library\orm\model\behaviour\LogBehaviour](/api/class/ride/library/orm/model/behaviour/LogBehaviour) implementation will keep a log of all insert, update and delete actions.

You can query the history and obtain data in a certain version or from a certain date through the _ModelLog_ model.
Check the [ride\library\orm\model\LogModel](/api/class/ride/library/orm/model/LogModel) API for the available methods.

This behaviour is automatically enabled if your model is set to be logged.

#### Slug Behaviour

The [ride\library\orm\model\behaviour\FieldSlugBehaviour](/api/class/ride/library/orm/model/behaviour/FieldSlugBehaviour) and the [ride\library\orm\model\behaviour\MethodSlugBehaviour](/api/class/ride/library/orm/model/behaviour/MethodSlugBehaviour) implementation will maintain a _slug_ property field based on some fields or a method of your data container.

Slugs are human friendly ids for your data and are mostly used to generate user friendly URLs.

To use this behaviour, you have to:

* define a _slug_ property field with the _string_ type in your model
* add the behaviour in your _initialize_ method of your model, see the [example](#behaviours)

#### Unique Behaviour

The [ride\library\orm\model\behaviour\UniqueBehaviour](/api/class/ride/library/orm/model/behaviour/UniqueBehaviour) implementation will force a unique value for a field in your model.

To use this behaviour, you have to add the behaviour in your _initialize_ method of your model:

    namespace vendor\app\model;

    use ride\library\orm\model\behaviour\DatedBehaviour;
    use ride\library\orm\model\GenericModel;

    class RegistrationModel extends GenericModel {

        protected function initialize() {
            $behaviour = new UniqueBehaviour('email', array('type'));
            $behaviour->setValidationError('error.validation.registration.email.unique');

            $this->addBehaviour($behaviour);
        }

    }

In this example, the email addresses of registrations have to be unique for each type.
When the email address already exists for the type of the data, a [ride\library\validation\exception\ValidationException](/api/class/ride/library/validation/exception/ValidationException) is thrown with the _error.validation.registration.email.unique_ validation error for the _email_ field.

#### Version Behaviour

The [ride\library\orm\model\behaviour\VersionBehaviour](/api/class/ride/library/orm/model/behaviour/VersionBehaviour) implementation will increase the version of your data on every save action.

To use this behaviour, you have to:

* define a _version_ property field with the _integer_ type in your model
* add the behaviour in your _initialize_ method of your model, see the [example](#behaviours)

### Data Formats

Data formats provide a way to generalize your model data.

The predefined and common used data formats are:

* __title__
A title or name for your data
* __teaser__
A teaser for your data
* __image__
Path to the image of your data
* __date__
A date for your data

To format your data, check this sample:

    $orm = $ride->getDependency('ride\\library\\orm\\OrmManager');

    $model = $orm->getModel('Blog');
    $post = $model->getById(3);
    $format = $model->getMeta()->getDataFormat('title');

    $dataFormatter = $orm->getDataFormatter();
    $title = $dataFormatter->formatData($post, $format);

Although data formats are optional, it's recommended you define at least a _title_ format.

### models.xml

Model definitions can be saved to file in the configuration directory of a module or of your application.
The file name is _models.xml_ and as the extensions shows, it's in XML format.

#### Example

Example of a definition through models.xml for a blog:

    <?xml version="1.0" encoding="UTF-8"?>
    <models>
        <model name="Blog">
            <field name="title" type="string">
                <validation name="required" />
            </field>
            <field name="teaser" type="wysiwyg">
                <validation name="required" />
            </field>
            <field name="post" type="wysiwyg" />
            <field name="image" type="image" />
            <field name="author" type="string" />
            <field name="datePublished" type="datetime" />
            <field name="dateAdded" type="datetime" />
            <field name="comments" relation="hasMany" model="BlogComment" />

            <format name="title">{title}</format>
            <format name="teaser">{teaser}</format>
            <format name="image">{image}</format>
            <format name="date">{datePublished}</format>

            <option name="manager" value="1" />
        </model>

        <model name="BlogComment">
            <field name="post" relation="belongsTo" model="Blog">
                <validation name="required" />
            </field>
            <field name="comment" type="text">
                <validation name="required" />
            </field>
            <field name="author" type="string" />
            <field name="dateAdded" type="datetime" />

            <format name="title">{comment.title} - {author}</format>
            <format name="teaser">{comment|truncate}</format>
            <format name="date">{dateAdded}</format>
        </model>
    </models>

#### Reference

The following is a reference for _models.xml_ and the available elements.

##### models

* __attributes__: -
* __value__: -
* __parent__: _none_ (root of your file)
* __children__: model

##### model

* __attributes__:
    * __name__: Name of the model (required)
    * __modelClass__: Full class name for your model, defaults to [ride\library\orm\model\GenericModel](/api/class/ride/library/orm/model/GenericModel)
    * __dataClass__: Full class name for your data, defaults to [ride\library\orm\model\data\Data](/api/class/ride/library/orm/model/data/Data)
* __value__: -
* __parent__: models
* __children__: field, index, format, option

##### field

* __attributes__:
    * __name__: Name of the model (required)
    * __type__: Type of the field. (required for a non relation property)
    * __default__: Default value for the field. (only for scalar properties)
    * __label__: Translation key for the name of this field, defaults to null
    * __localized__: Boolean to state if this field is localized, defaults to false
    * __model__: Model name of the relation. (required for a relation property)
    * __relation__: Type of the relation. (required for a relation property)
    * __linkModel__: Model name for the link model, defaults to a generated value of the 2 linked model names
    * __foreignKey__: Name for the foreign key in the related model, set this attribute when the related model has multiple relations back to this model.
    * __dependant__: Boolean to state if the related data should be deleted when this record gets deleted, defaults to false
    * __relationOrder__: Order expression for the relation. (only for hasMany relations)
* __value__: -
* __parent__: model
* __children__: validation, option

##### validation

* __attributes__:
    * __name__: The name of the validator (required)
* __value__: -
* __parent__: field
* __children__: parameter

##### parameter

* __attributes__:
    * __name__: The name of the parameter (required)
    * __value__: The value of the parameter (required)
* __value__: -
* __parent__: validation
* __children__: _none_

##### index

* __attributes__:
    * __name__: The name of the index (required)
* __value__: -
* __parent__: model
* __children__: indexField

##### indexField

* __attributes__:
    * __name__: The name of the indexed field (required)
* __value__: -
* __parent__: index
* __children__: _none_

##### format

* __attributes__:
    * __name__: The name of the format (required)
* __value__: The format
* __parent__: model
* __children__: _none_

##### option

* __attributes__:
    * __name__: The name of the option (required)
    * __value__: The value of the option (required)
* __value__: -
* __parent__: model, field
* __children__: _none_

## Models

Models contain the logic of your data model.
A generic ORM model contains methods which will allow you to create a CRUD.

### Add A Record

    $model = $orm->getModel('BlogPost');

    $post = $model->createEntry();
    $post->title = 'My First Post';
    $post->teaser = '<p>Welcome to the first post of my blog.</p>';
    $post->author = 'John';

    $model->save($post);

    // $post->id will contain the id of the new post

### Update A Record

To update a record, you simply change it's values and save it back to the model.

    $model = $orm->getModel('BlogPost');

    $post = $model->getById(5);
    $post->teaser = '<p>Welcome to the first post of my great blog.</p>';

    $model->save($post);

Since I'm only updating one field, I could have done:

    $model = $orm->getModel('BlogPost');

    $post = $model->getById(5);
    $post->teaser = '<p>Welcome to the first post of my great blog.</p>';

    $model->save($post, 'teaser');

And again, since it's only one field, I could have done it without fetching the post first:

    $model = $orm->getModel('BlogPost');

    $teaser = '<p>Welcome to the first post of my great blog.</p>';

    $model->save($teaser, 'teaser', 5);

### Delete A Record

Deleting a record from the model is as easy as invoking the _delete_ method:

    $model = $orm->getModel('BlogPost');

    $post = $model->getById(5);

    $model->delete($post);

Or without fetching the post first:

    $model = $orm->getModel('BlogPost');

    $model->delete(5);

You can delete multiple posts at once:

    $model = $orm->getModel('BlogPost');

    $model->delete(array(5, 7, 9));

### Query A Model

You first create a query:

    $orm = $ride->getDependency('ride\\library\\orm\\OrmManager');

    // query the Registration model
    $query = $orm->createQuery('Registration');

    // query the Registration model and fetch 2 levels of relation properties
    $query = $orm->createQuery('Registration', 2);

You might need to select specific fields:

    $query->setFields('{id}, {firstName}, {lastName}');
    $query->addField('{email}');

Or add some conditions:

    $query-addCondition('{id} = %1%', 1);
    $query-addCondition('{firstName} LIKE %1%', '%o%');
    $query-addCondition('{firstName} LIKE %1% OR {firstName} LIKE %2%', '%o%', 'J%');
    $query-addConditionWithVariables('{firstName} = %name%', array('name' => 'John'));

Before you finally get your result:

    // get all rows in a array
    $result = $query->query();

    // get all rows in a array and index on the firstName field
    $result = $query->query('firstName');

    // get the first row
    $data = $query->queryFirst();

    // count the result
    $numRows  = $query->count();

Yii2 Serialize Attribute Behavior
===================================

This Yii2 ActiveRecord behavior allows you to store serialized values in attributes.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mkiselev/yii2-serialize-attribute-behavior "*"
```

or add

```
"mkiselev/yii2-serialize-attribute-behavior": "*"
```

to the require section of your `composer.json` file.

Usage
-----

Behavior will add property to your Active Record class with name "$attribute . $unserializedAttributeSuffix".
 
You can get deserialized property value like ```$model->propertyArray```.

You can set deserialized property value like ```$model->propertyArray = []```.

* #####Basic
If you want only to serialize and unserialize attribute use config like this:
```php
public function behaviors()
{
    return [
        ...
        [
            'class' => SerializeAttributeBehavior::className(),
            'attribute' => 'data',
            'unserializedAttributeSuffix' => 'Array',
        ],
        ...
    ];
}
```

* #####Advanced
If you need more flexible logic, you may configure behavior like this:
```php
public function behaviors()
{
    return [
        ...
        [
            'class' => SerializeAttributeBehavior::className(),
            'attribute' => 'data',
            'unserializedAttributeSuffix' => 'Model',
            // MyModel must extend mkiselev\serialized\Model
            'setAttributesToModel' => MyModel::className(),
            'setAttributesToModelSafeOnly' => true,
            // serializerClass must implements mkiselev\serialized\interfaces\SerializerInterface
            'serializerClass' => MySerializer::className(),
        ],
        ...
    ];
}
```
In this example ```MyModel``` must contain attributes, rules, and more.

This was made for support ```ActiveField``` for deserialized attributes.

For example:
```php
<?= $form->field($model, 'activeRecordModelAttribute')->textInput(); ?>
<?= $form->field($model->dataModel, 'MyModelAttribute')->textInput(); ?>
```


Use cases
-----

* This behavior was develop primarily for use with json PostgreSQL/MariaDB columns

* Also you may use it for store any serialized data as text (thank you captain obvious :smile:)

-----
p.s. Sorry for my English :us:
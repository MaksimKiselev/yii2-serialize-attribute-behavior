<?php

namespace mkiselev\serialized\behaviors;

use mkiselev\serialized\Model;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use yii\validators\Validator;

/**
 * Class SerializeAttributeBehavior
 * @package mkiselev\serialized\behaviors
 * @autor MKiselev@github.com
 */
class SerializeAttributeBehavior extends Behavior
{
    /**
     * @var string attribute name
     */
    public $attribute;

    /**
     * @var string suffix for getter and setter of unserialized attribute
     */
    public $unserializedAttributeSuffix = 'Unserialized';

    /**
     * @var null|string if class name was set unserialized attribute value setter will be set data to model
     * by use yii\base\Model::setAttributes() method.
     * For support this feature class must be child of mkiselev\serialized\Model
     */
    public $setAttributesToModel = null;

    /**
     * @var bool set attributes to model safe only @see yii\base\Model::setAttributes()
     */
    public $setAttributesToModelSafeOnly = true;

    /**
     * @var string class to serialize/unserialize attribute,
     * must implements mkiselev\serialized\interfaces\SerializerInterface
     */
    public $serializerClass = 'mkiselev\serialized\serializers\JsonSerializer';

    /**
     * @var string unserialized attribute name
     */
    protected $_unserializedAttributeName;

    /**
     * @var mixed|Model unserialized attribute value
     */
    protected $_unserializedAttributeValue;

    /**
     * @var string getter name of unserialized attribute
     */
    protected $_unserializedAttributeGetter;

    /**
     * @var string setter name of unserialized attribute
     */
    protected $_unserializedAttributeSetter;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $serializerInterfaceName = 'mkiselev\serialized\interfaces\SerializerInterface';
        if (!is_subclass_of($this->serializerClass, $serializerInterfaceName)) {
            throw new InvalidConfigException('serializerClass must implement ' . $serializerInterfaceName);
        }

        if ($this->setAttributesToModel) {
            $baseModelName = 'mkiselev\serialized\Model';
            if (!is_subclass_of($this->setAttributesToModel, $baseModelName)) {
                throw new InvalidConfigException('setAttributesToModel must extend ' . $baseModelName);
            }
        }

        if (empty($this->unserializedAttributeSuffix) || !is_string($this->unserializedAttributeSuffix)) {
            throw new InvalidConfigException('unserializedAttributeSuffix cannot be empty and must be string');
        }

        $this->_unserializedAttributeName = $this->attribute . $this->unserializedAttributeSuffix;
        $this->_unserializedAttributeGetter = 'get' . ucfirst($this->attribute) . $this->unserializedAttributeSuffix;
        $this->_unserializedAttributeSetter = 'set' . ucfirst($this->attribute) . $this->unserializedAttributeSuffix;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        /** @var BaseActiveRecord $owner */
        if ($this->setAttributesToModel) {
            $owner->validators[] = Validator::createValidator('safe', $owner, $this->_unserializedAttributeName);
            $this->_unserializedAttributeValue = new $this->setAttributesToModel();
            $this->_unserializedAttributeValue->setFormName($owner->formName() . "[{$this->_unserializedAttributeName}]");
        }
        parent::attach($owner);
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'serializeAttribute',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'serializeAttribute',

            BaseActiveRecord::EVENT_AFTER_FIND => 'unserializeAttribute',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'unserializeAttribute',
            BaseActiveRecord::EVENT_AFTER_UPDATE => 'unserializeAttribute',
            BaseActiveRecord::EVENT_AFTER_REFRESH => 'unserializeAttribute',
        ];
    }

    /**
     * Serialize attribute value by unserializer
     */
    public function serializeAttribute()
    {
        $this->owner->{$this->attribute} = call_user_func(
            [$this->serializerClass, 'serialize'],
            $this->owner->{$this->_unserializedAttributeGetter}()
        );
    }

    /**
     * Unserialize attribute value by unserializer
     */
    public function unserializeAttribute()
    {
        $this->owner->{$this->_unserializedAttributeSetter}(call_user_func(
            [$this->serializerClass, 'unserialize'],
            $this->owner->{$this->attribute}
        ));
    }

    /**
     * Setter for unserialized attribute value
     * @param $value
     */
    protected function setAttributeValueInternal($value)
    {
        if ($this->setAttributesToModel) {
            $this->_unserializedAttributeValue->setAttributes($value, $this->setAttributesToModelSafeOnly);
        } else {
            $this->_unserializedAttributeValue = $value;
        }
    }

    /**
     * Getter for unserialized attribute value
     * @return mixed|Model
     */
    protected function getAttributeValueInternal()
    {
        return $this->_unserializedAttributeValue;
    }

    /**
     * @inheritdoc
     */
    public function hasMethod($name)
    {
        if (in_array($name, [$this->_unserializedAttributeSetter, $this->_unserializedAttributeGetter])) {
            return true;
        } else {
            return parent::hasMethod($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if ($name === $this->_unserializedAttributeName) {
            return true;
        } else {
            return parent::canSetProperty($name, $checkVars = true);
        }
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if ($name === $this->_unserializedAttributeName) {
            return true;
        } else {
            return parent::canSetProperty($name, $checkVars = true);
        }
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $params)
    {
        if ($name === $this->_unserializedAttributeGetter) {
            return $this->getAttributeValueInternal();
        } elseif ($name === $this->_unserializedAttributeSetter) {
            $this->setAttributeValueInternal($params[0]);
        } else {
            return parent::__call($name, $params);
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if ($name === $this->_unserializedAttributeName) {
            return $this->getAttributeValueInternal();
        } else {
            return parent::__get($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if ($name === $this->_unserializedAttributeName) {
            $this->setAttributeValueInternal($value);
        } else {
            return parent::__set($name, $value);
        }
    }

}

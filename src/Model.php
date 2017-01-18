<?php
namespace mkiselev\serialized;

class Model extends \yii\base\Model {
    private $_formName;

    /**
     * @return mixed
     */
    public function formName()
    {
        if($this->_formName){
            return $this->_formName;
        }

        parent::formName();
    }

    /**
     * @param $value
     */
    public function setFormName($value)
    {
        $this->_formName = $value;
    }
}
<?php

namespace mkiselev\serialized\serializers;

use mkiselev\serialized\interfaces\SerializerInterface;

class PhpSerializer implements SerializerInterface {

    /**
     * @inheritdoc
     */
    public static function serialize($data)
    {
        return serialize($data);
    }

    /**
     * @inheritdoc
     */
    public static function unserialize($data)
    {
        return unserialize($data);
    }
}
<?php

namespace mkiselev\serialized\serializers;

use mkiselev\serialized\interfaces\SerializerInterface;

class JsonSerializer implements SerializerInterface {

    /**
     * @inheritdoc
     */
    public static function serialize($data)
    {
        return json_encode($data);
    }

    /**
     * @inheritdoc
     */
    public static function unserialize($data)
    {
        return json_decode($data, true);
    }
}
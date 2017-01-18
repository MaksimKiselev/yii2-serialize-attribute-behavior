<?php

namespace mkiselev\serialized\interfaces;

interface SerializerInterface {
    /**
     * @param mixed $data to serialize
     * @return string serialized attribute
     */
    public static function serialize($data);

    /**
     * @param string $data to unserialize
     * @return mixed unserialized attribute value
     */
    public static function unserialize($data);
}
<?php

namespace Solution10\Data;

/**
 * Class ReflectionExtract
 *
 * Extracts values from a given model either via Reflection or by using
 * the getter methods on the object.
 *
 * @package     Solution10\Data
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait ReflectionExtract
{
    use StringConverter;

    /**
     * Extracts properties from a given object. Will use getters for those
     * properties if available, otherwise will use reflection.
     *
     * @param   object  $object         Model to extract from
     * @param   array   $properties     Properties / methods to extract
     * @return  array
     */
    public function extractWithReflection($object, array $properties)
    {
        $data = [];

        $ref = new \ReflectionClass($object);
        foreach ($properties as $prop) {
            $methodName = $this->snakeToCamel($prop, 'get');
            if (method_exists($object, $methodName)) {
                $data[$prop] = $object->$methodName();
            } elseif (property_exists($object, $prop)) {
                $property = $ref->getProperty($prop);
                $property->setAccessible(true);
                $data[$prop] = $property->getValue($object);
            }
        }

        return $data;
    }
}

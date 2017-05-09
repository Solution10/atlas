<?php

namespace Solution10\Data;

use Solution10\Data\Util\Str;

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
            $methodName = Str::snakeToCamel($prop, 'get');
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

    /**
     * Extracts all the protected properties from an object via Reflection.
     * You probably don't want to use this for real. It'll use the getter for
     * the property if it's available, otherwise, it'll use the property directly.
     *
     * @param   object  $object
     * @return  array
     */
    public function extractAllWithReflection($object)
    {
        $data = [];
        $ref = new \ReflectionClass($object);
        $properties = $ref->getProperties(\ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            $methodName = Str::snakeToCamel($property->getName(), 'get');
            if (method_exists($object, $methodName)) {
                $data[$property->getName()] = $object->$methodName();
            } else {
                $property->setAccessible(true);
                $data[$property->getName()] = $property->getValue($object);
            }
        }
        return $data;
    }
}

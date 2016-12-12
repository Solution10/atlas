<?php

namespace Solution10\Data;

trait ReflectionPopulate
{
    /**
     * @param   object  $object
     * @param   array   $data
     * @return  object
     */
    public function populateWithReflection($object, array $data)
    {
        $ref = new \ReflectionClass($object);
        foreach ($data as $k => $v) {
            $setterName = 'set'.str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $k)));
            if (method_exists($object, $setterName)) {
                $object->$setterName($v);
            } elseif (property_exists($object, $k)) {
                $prop = $ref->getProperty($k);
                $prop->setAccessible(true);
                $prop->setValue($object, $v);
            }
        }
        return $object;
    }
}

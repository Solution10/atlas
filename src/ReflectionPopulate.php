<?php

namespace Solution10\Atlas;

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
            if (property_exists($object, $k)) {
                $prop = $ref->getProperty($k);
                $prop->setAccessible(true);
                $prop->setValue($object, $v);
            }
        }
        return $object;
    }
}

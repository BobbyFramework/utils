<?php
namespace BobbyFramework\Utils\Traits;
/**
 * Class ToArray
 * @package BobbyFramework\Utils\Traits
 */
trait ToArray
{
    /**
     * @return array
     */
    public function toArray() {
        $array                = [];
        $reflection           = new \ReflectionClass(get_called_class());
        $propertiesReflection = $reflection->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($propertiesReflection as $property) {
            if ($property instanceof \ReflectionProperty) {
                $propertyName = $property->getName();
            }
            else {
                $propertyName = $property;
            }

            // NULL value
            if ($this->{$propertyName} === null) {
                $array[$propertyName] = null;
            }

            $getter  = 'get'.ucfirst($propertyName);
            if(method_exists($this,$getter)){
                $array[$propertyName] = $this->$getter();
            }
            
        }
        return $array;
    }
}

<?php
namespace BobbyFramework\Utils\Traits;
/**
 * Class UtilsClass
 * @package BobbyFramework\Utils\Traits
 */
trait UtilsClass
{
    /**
     * @param mixed
     */
    public function getClassNameUsingNamespace() {
      return array_pop(array_slice(explode('\\', get_called_class()), -1));
    }
}

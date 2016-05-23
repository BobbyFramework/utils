<?php
namespace BobbyFramework\Utils\Traits;

/**
 * Class Hydrator
 * @package BobbyFramework\Utils\Traits
 */
trait Hydrator
{
    /**
     * @param $data
     */
    public function hydrate(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (is_callable([$this, $method])) {
                $this->$method($value);
            }
        }
    }
}
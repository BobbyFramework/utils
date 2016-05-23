<?php
namespace BobbyFramework\Utils\Traits;
/**
 * Class Uid
 * @package BobbyFramework\Utils\Traits
 */
trait Uid
{
    /**
     * @return string
     */
    public function getUniqueId()
    {
        return md5(uniqid(time()));
    }
}
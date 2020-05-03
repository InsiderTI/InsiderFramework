<?php

namespace Apps\Start\Models;

/**
 * Example model
 *
 * @author Marcello Costa
 * @package Apps\Start\Models\Example
 *
 */
class ExampleModel extends \Modules\InsiderFramework\Core\Model
{
    public function getUsers()
    {
        $query = "SELECT * from users ";

        $result = $this->select($query);
        
        return $result;
    }
}

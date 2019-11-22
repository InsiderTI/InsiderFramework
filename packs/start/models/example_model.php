<?php
// Namespace relativo ao pack do controller
namespace Models\start;

class Example_Model extends \KeyClass\Model{
    /**
     *   @author Marcello Costa
     *
     *   Exemplo de model
     *
     *   @param  string  $name    Nome que será buscado
     *
     *   @return string Nome retornado pela busca
    */
    function QueryTest($name=null) {
        if ($name !== null) {
            $query="SELECT * from users WHERE username = :username";
            $bindarray = array(
                'username' => $name
            );

            $dummydata=$this->select($query, $bindarray, true);
        }
        else {
            $query="SELECT * from users";
            $dummydata=$this->select($query, null, true);
        }

        return $dummydata;
    }
}
?>

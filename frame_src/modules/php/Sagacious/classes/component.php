<?php
/**
  Arquivo Sagacious\SgsComponent
*/

// Namespace do Sagacious
namespace Sagacious;

/**
  Classe que define o que é um objeto (componentes em nível de view)
  e quais as principais funções que todos eles possuem

  @author Marcello Costa

  @package Sagacious\SgsComponent
*/
class SgsComponent{
    /**
        Função que fecha o código do componente (inserindo tag de fechamento no HTML)
     
        @author Marcello Costa

        @package Sagacious\SgsComponent
     
        @return  string  Retorna a tag de fechamento da região
    */
    public function close() : string {
        return $this->tagclose;
    }

    /**
        Retorna o código do componente em forma de string
     
        @author Marcello Costa

        @package Sagacious\SgsComponent
     
        @return  mixed  Retorna o que contiver no código do componente
    */
    public function returnCode() {
        return $this->code;
    }

    /**
        Exibição direta do código retornado pelo elemento
     
        @author Marcello Costa

        @package Sagacious\SgsComponent
     
        @return  void  Without return
    */
    public function echoCode() : void {
        // Se o código do componente não pode ser exibido
        if (!is_string($this->code) && !is_null($this->code) && !is_numeric($this->code)) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
            \KeyClass\Error::i10nErrorRegister('An component has a code that can not be displayed by the echo: %'.json_encode($backtrace).'%', 'pack/sys', LINGUAS, 'LOG');
        }
        echo $this->code;
    }
}

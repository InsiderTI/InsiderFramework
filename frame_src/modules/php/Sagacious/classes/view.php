<?php
/**
  Arquivo Sagacious\SgsView
*/

// Namespace do Sagacious
namespace Sagacious;

/**
  Classe responsável pelo objeto SgsView.

  @author Marcello Costa

  @package Sagacious\SgsView
*/
class SgsView{
    /** @var string Nome da view */
    private $viewFilename;
    
    /** @var string Pack do objeto SgsView */
    private $pack;

    /**
        Função para recuperar o nome do arquivo da view
     
        @author Marcello Costa
     
        @package Sagacious\SgsView
     
        @return  string  Nome do arquivo
    */
    public function getViewFilename() : string {
        return $this->viewFilename;
    }

    /**
        Função para setar o nome do arquivo da view
     
        @author Marcello Costa
     
        @package Sagacious\SgsView
     
        @param  string  $viewFilename    Nome do arquivo
        @param  string  $pack            Nome do arquivo
     
        @return void Without return
    */
    public function setViewFilename(string $viewFilename, string $pack=null) : void {
        $pattern="/"."((?P<pack>.*)::)?(?P<viewPath>.*)"."/";

        // Se não foi encontrada uma tag literal
        preg_match_all($pattern, $viewFilename, $viewFilenameMatches, PREG_SET_ORDER);

        if (is_array($viewFilenameMatches) && count($viewFilenameMatches) > 0) {
            $viewData=$viewFilenameMatches[0];
            $viewFilename=$viewData['viewPath'];

            // Se foi especificado pack via viewFilename
            if (trim($viewData['pack']) !== "") {
                $pack=$viewData['pack'];
            }
        }

        // Se também não foi especificado o pack via parâmetros da função
        if ($pack == null) {
            \KeyClass\Error::i10nErrorRegister('Unable to identify the origin of request to the view %'.$SgsView->getViewFilename().'%', 'pack/sys');
        }

        $this->setPack($pack);

        $this->viewFilename = "packs".DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR.$viewFilename;
    }

    /**
        Recupera o pack do objeto
     
        @author Marcello Costa
     
        @package Sagacious\SgsView
     
        @return  string Nome do pack
    */
    public function getPack() : string {
        return $this->pack;
    }

    /**
        Seta o pack do objeto
     
        @author Marcello Costa
     
        @package Sagacious\SgsView
     
        @param  string  $pack    Nome do pack
     
        @return void Without return
    */
    public function setPack($pack) : void {
        $this->pack = $pack;
    }
}

<?php
/**
  Arquivo Sagacious\SgsComponentsBag
*/

// Namespace do Sagacious
namespace Sagacious;

/**
  Classe que define um objeto ComponentsBag

  @author Marcello Costa

  @package Sagacious\SgsComponentsBag
 */
class SgsComponentsBag{
    /** @var array Array de componentes */
    protected $componentsBag=[];

    /**is
        Função que seta um estado num componente cadastrado
     
        @author Marcello Costa
      
        @package Sagacious\SgsComponentsBag
     
        @param  string  $id          ID do componente
        @param  string  $statename   Nome do estado a ser setado
        @param  array  $stateprops   Array contendo as propriedades estado a ser setado
     
        @return  bool  retorno da operação
    */
    public function setComponentState(string $id, string $statename, array $stateprops) : bool {
        // Se o componente existir
        if (isset($this->componentsBag[$id])) {
            $this->componentsBag[$id]['states'][$statename]=$stateprops;
            return true;
        }

        // Se o componente não existir
        else {
            \KeyClass\Error::i10nErrorRegister("The %".$id."% component was not initialized", 'pack/sys');
        }
    }

    /**
        Função que modifica o defaultstate de um componente
     
        @author Marcello Costa
      
        @package Sagacious\SgsComponentsBag
     
        @param  string  $id            ID do componente
        @param  string  $defaultstate  Nome/Número do defaultstate do componente
     
        @return void Without return
    */
    public function setDefaultState(string $id, string $defaultstate) : void {
        // Se o componente existir
        if (isset($this->componentsBag[$id])) {
            // Se o estado existir
            if (isset($this->componentsBag[$id]['states'][$defaultstate])) {
                $this->componentsBag[$id]['defaultstate']=$defaultstate;
            }
            // Se o estado não existir
            else {
                \KeyClass\Error::i10nErrorRegister("Status %".$defaultstate."% of component %".$id."% does not exist", 'insiderFrameworkSystem');
            }
        }

        // Se o componente não existir
        else {
            \KeyClass\Error::i10nErrorRegister("The %".$id."% component was not initialized", 'pack/sys');
        }
    }

    /**
        Função que instancia um componente da view (previamente declarado na mesma)
     
        @author Marcello Costa
      
        @package Sagacious\SgsComponentsBag
     
        @param  string $id        ID do componente na view
        @param  string $pack      Nome do pack do componente
        @param  string $statenow  Estado que o componente assumirá quando for renderizado
     
        @return void Without return
    */
    public function initializeComponent(string $id, string $pack, string $statenow=null) : void {
        // ID dos componentes sempre deverá existir, pois é através da ID na view,
        // que o código é inserido. Nem que seja um componente de estado NULL.

        // Verificando se o componente existe no diretório
        $searchComponent=\KeyClass\Registry::getComponentRegistryData($id, null, $pack);

        // Se encontrou
        if ($searchComponent !== false) {
            // Criando o componente no componentsBag
            // Se um defaultstate foi definido
            if ($statenow !== null) {
                $component=array(
                        "states" => $searchComponent['states'],
                        "defaultstate" => $searchComponent['defaultstate']
                );

                // Adicionando componente à bag
                $this->componentsBag[$id]=$component;

                // Modificando estado do componente
                $this->setDefaultState($id, $statenow);
            }

            // Se um defaultstate não foi definido
            else {
                $component=array(
                        "states" => $searchComponent['states'],
                        "defaultstate" => $searchComponent['defaultstate']
                );

                // Adicionando componente à bag
                $this->componentsBag[$id]=$component;
            }
        }

        // Se não encontrou
        else {
            \KeyClass\Error::i10nErrorRegister("Component ID %".$id."% not found in the pack record!", 'pack/sys');
        }
    }

    /**
        Função que retorna todos as propriedades de todos os componentes da
        componentsBag do controller
     
        @author Marcello Costa
      
        @package Sagacious\SgsComponentsBag
     
        @return  array  Propriedades dos componentes
    */
    public function getAllComponents() : array {
        return $this->componentsBag;
    }

    /**
        Modifica o estado de um componente parcialmente ou completamente
     
        @author Marcello Costa
      
        @package Sagacious\SgsComponentsBag
     
        @param  string                          $id              ID do componente a ser modificado
        @param  string                          $state           Estado a ser modificado
        @param  array                           $props           Array com valores a serem modificados
        @param  \KeyClass\Controller            $controllerObj   Objeto controller que terá seu objeto de
                                                                 view modificado
     
        @return  bool  Resultado da modificação
    */
    public function changeComponentProps(string $id, string $state, array $props, \KeyClass\Controller &$controllerObj) : bool {
        // Pack do componente
        $pack=$controllerObj->pack;

        // Inicializando componente
        $controllerObj->componentsBag->initializeComponent($id, $controllerObj->pack);

        // Modificando o estado padrão
        $controllerObj->componentsBag->setDefaultState($id, $state);

        // Capturando estado do componente no registro da view
        $componentRecovered=\KeyClass\Registry::getComponentViewData($id, $pack, $state);

        // Modificando as propriedades na variável
        $componentRecovered['state']['props']=array_merge($componentRecovered['state']['props'],$props);

        // Modificando as propriedades na componentsBag
        return $controllerObj->componentsBag->setComponentState($id, $state, $componentRecovered['state']);
    }

    /**
        Instancia um componente para uma variável com base nos componentes que foram
        previamente declarados
     
        @author Marcello Costa
      
        @package Sagacious\SgsComponentsBag
     
        @param  string  $pack    Pack onde o componente está declarado
        @param  string  $id      ID do componente
        @param  int     $state   Index do estado do componente a ser instanciado
        @param  array   $params  Array de parâmetros para inicializar o componente.
                                 Se nulo, pega os atributos pré-definidos no JSON
     
        @return  Object  Componente instanciado
    */
    public function getRealComponent(string $pack, string $id, int $state=null, array $params=[]) {
        // Recuperando os dados da classe do componente
        $componentReq=\KeyClass\Registry::getComponentRegistryData($id, NULL, $pack);

        // Classe/componente não encontrada !
        if ($componentReq === false) {
            \KeyClass\Error::i10nErrorRegister('Class/component %'.$id.'% not found', 'pack/sys');
        }

        // Se não foi passado nenhum estado, então é o default
        if ($state === NULL) {
            $state=$componentReq['defaultstate'];
        }

        // Se o array de parâmetros não for vazio
        if (count($params) !== 0) {
            // Convertendo o array em string
            $params=serialize($params);
        }
        // Se não existirem parâmetros passados para a função,
        // usa os do JSON
        else {
            $params=serialize($componentReq['states'][$state]['props']);
        }

        // Buscando informações da classe no registro do framework
        $componentReqregframe=\KeyClass\Registry::getComponentRegistryData(NULL, $componentReq['states'][$state]['class'], $pack);

        // Requerendo o arquivo do componente
        // $componentReq['states'][$state]['class']
        \KeyClass\FileTree::requireOnceFile(INSTALL_DIR.DIRECTORY_SEPARATOR.$componentReqregframe['directory'].DIRECTORY_SEPARATOR.ucfirst($componentReq['states'][$state]['class']).'.php');

        // Instanciando o componente
        $componentClass="\\Sagacious\\SgsComponent\\".$componentReq['states'][$state]['class'];
        $component = new $componentClass($params);

        // Retorna o componente instanciado
        return $component;
    }
}

<?php

/**
  Arquivo Sagacious\SgsTemplate
 */
// Namespace do Sagacious

namespace Sagacious;

// Requerendo Code
require_once("frame_src" . DIRECTORY_SEPARATOR . "keyclasses" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "code.php");

/**
  Classe sagacious de renderização de templates

  @author Marcello Costa

  @package Sagacious\SgsTemplate
 */
class SgsTemplate {

    /** @var string Pack do objeto SgsTemplate */
    protected $pack = "";

    /** @var string Caminho do arquivo de template */
    protected $templateFilename = "";

    /** @var object Objeto SgsView */
    protected $SgsView = "";

    /**
      Função que devolve o nome do pack

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @return  string  Nome do pack
     */
    public function getPack() : string {
        return $this->pack;
    }

    /**
      Função que devolve o nome do arquivo do template

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @return  string  Nome do arquivo do template
     */
    public function getTemplateFilename() : string {
        return $this->templateFilename;
    }

    /**
      Função que devolve o objeto de views do template

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @return  \Sagacious\SgsView  Objeto de views do template
     */
    public function getSgsView() : \Sagacious\SgsView {
        return $this->SgsView;
    }

    /**
      Função que converte arquivo SGV em PHP

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  \Sagacious\SgsView  $SgsView    View a ser renderizada

      @return  array  Código PHP
     */
    public function convertSGV2PHP($SgsView) : array {
        $this->SgsView = $SgsView;

        /////////// CONVERTENDO O CÓDIGO SGV EM PHP /////////////////////////
        // Quero encontrar uma view, do início ao fim
        // e depois, pegar esse código e enviar para
        // uma função que me retorne o código php da mesma.
        // Enquanto houverem views dentro do código,
        // chamo a mesma função novamente.
        // Mapeando os componentes da componentsBag e colocando em um array
        // Para cada componente encontrado, é criado um componente real em php
        // que instancie a classe do componente "fake" requisitado.
        // No início do código php de renderização, será colocada
        // a $componentsBag, então o arquivo de cache terá as declaraçãoes e
        // IDs dos componentes, mas não seu estado (este será recuperado
        // diretamente do controller, na requisição).
        $viewconverted = $this->convertSGV2PHPAux($this->SgsView);

        // Invertendo a ordem de detecção das views e templates pois
        // esta ordem importa para outras funções que irão utilizar
        // utilizar estas informações
        if (count($viewconverted['templatesPath']) > 1) {
            $viewconverted['templatesPath'] = array_reverse($viewconverted['templatesPath']);
        }
        if (count($viewconverted['viewsPath']) > 1) {
            $viewconverted['viewsPath'] = array_reverse($viewconverted['viewsPath']);
        }

        // Armazenando o código em um array
        $renderCode = $viewconverted['renderCode'];

        // Componentes encontrados
        $componentsViewConverted = $viewconverted['components'];

        // Criando um nome baseado na $direct_render para que este seja
        // utilizado dentro do código abaixo. Isto evita que variáveis e funções
        // sejam criadas com o mesmo nome e gerem problemas na renderização
        $vname = md5($this->getPack() . $this->SgsView->getViewFileName());

        // Para cada componente encontrado, montar um array contendo as propriedades
        // que serão utilizadas no arquivo em cache
        // Serializando componentes detectados para serem enviados para o arquivo em cache
        if (isset($componentsViewConverted) && $componentsViewConverted !== null) {
            // Definido o nome do pack
            $pack = $SgsView->getPack();

            $componentsViewConverted = addslashes(serialize($componentsViewConverted));

            // Código em array que declara cada componente da view de acordo com a situação
            $declarationComponent = "<?php";
            // $declarationComponent.=" // DECLARATION OF COMPONENTS";
            $declarationComponent .= " {";
            // $declarationComponent.="     // Decodificando e montando variável que contém os componentes detectados na view";
            $declarationComponent .= "     \$componentsViewConverted" . $vname . "=unserialize(stripslashes('" . $componentsViewConverted . "'));";
            $declarationComponent .= " ";
            $declarationComponent .= " ";
            // $declarationComponent.="     // Array de componente nos seus estados iniciais/definidos";
            $declarationComponent .= "     \$componentsDefined" . $vname . "=array();";
            $declarationComponent .= " ";
            // $declarationComponent.="     // Array de configurações de componente";
            $declarationComponent .= "     global \$kernelspace;";
            $declarationComponent .= " ";
            // $declarationComponent.="     // Variável que guarda as informações da componentsBag enviada pelo controller";
            $declarationComponent .= "     \$componentsBag=\$kernelspace->getVariable('componentsBag', 'insiderFrameworkSystem');";
            //$declarationComponent.=" ";
            // $declarationComponent.="     // Para cada componente que foi encontrado no código da view,";
            // $declarationComponent.="     // recuperar os estados do mesmo de acordo com componentsBag ou arquivo JSON";
            $declarationComponent .= "     foreach (\$componentsViewConverted" . $vname . " as \$key => \$id) {";
            // $declarationComponent.="       // Se o componente foi redefinido na componentsBag";
            $declarationComponent .= "        if (isset(\$componentsBag[\$id])) {";
            // $declarationComponent.="          // Armazenando o estado padrão do componente";
            $declarationComponent .= "           \$defaultState=\$componentsBag[\$id]['states'][\$componentsBag[\$id]['defaultstate']];";
            // $declarationComponent.=" ";
            // $declarationComponent.="          // Pegando os dados da classe default do componente";
            $declarationComponent .= "           \$stateclassdjson=\\KeyClass\Registry::getComponentRegistryData(null, \$defaultState['class'], '" . $pack . "');";
            // $declarationComponent.=" ";
            // $declarationComponent.="          // Colocando o estado default do componente no array";
            $declarationComponent .= "           \$componentsDefined" . $vname . "[]=array(";
            $declarationComponent .= "               'id' => \$id,";
            $declarationComponent .= "               'state' => \$defaultState,";
            $declarationComponent .= "               'directoryClass' => \$stateclassdjson['directory']";
            $declarationComponent .= "           );";
            $declarationComponent .= "        }";
            // $declarationComponent.=" ";
            // $declarationComponent.="       // Se o componente não foi definido na componentsBag";
            $declarationComponent .= "        else {";
            // $declarationComponent.="           // Recuperando dados do arquivo JSON";
            $declarationComponent .= "            \$componentRecovered=\\KeyClass\Registry::getComponentViewData(\$id,'" . $pack . "');";
            // $declarationComponent.=" ";
            // $declarationComponent.="           // Se a busca retornou um resultado";
            $declarationComponent .= "            if (\$componentRecovered !== false) {";
            // $declarationComponent.="               // Adiciona o componente ao array";
            $declarationComponent .= "                \$componentsDefined" . $vname . "[]=\$componentRecovered;";
            $declarationComponent .= "            }";
            // $declarationComponent.=" ";
            $declarationComponent .= "            else {";
            $declarationComponent .= "                throw new \Exception('Error retrieving component data '.\$id);";
            $declarationComponent .= "            }";
            $declarationComponent .= "         }";
            $declarationComponent .= "     }";
            $declarationComponent .= " ";
            // $declarationComponent.="    // Variável global viewBag";
            $declarationComponent .= "     \$viewBag = \$kernelspace->getVariable('viewBag', 'insiderFrameworkSystem');";
            // $declarationComponent.=" ";
            // $declarationComponent.="     // Com os dados de inicialização do componente prontos, é hora de criar o componente.";
            // $declarationComponent.="     // Criando o componente no código";
            $declarationComponent .= "     foreach(\$componentsDefined" . $vname . " as \$component) {";
            // $declarationComponent.="         // Serializando os dados a serem enviados para o componente";
            $declarationComponent .= "         \$props=serialize(\$component['state']['props']);";
            // $declarationComponent.=" ";
            // $declarationComponent.="         // Requerendo o arquivo do componente";
            $declarationComponent .= "         \\KeyClass\FileTree::requireOnceFile(INSTALL_DIR.DIRECTORY_SEPARATOR.\$component['directoryClass'].DIRECTORY_SEPARATOR.\$component['state']['class'].'.php');";
            // $declarationComponent.=" ";
            $declarationComponent .= "         \$n=\$component['state']['class'];";
            $declarationComponent .= "         \$n='\Sagacious\SgsComponent\\\'.\$n;";
            $declarationComponent .= "         \${\$component['id']}=new \$n(\$props);";
            $declarationComponent .= "     }";
            // $declarationComponent.=" ";
            // $declarationComponent.="    // Funções de views e templates";
            $declarationComponent .= "     if (!function_exists('getValueFromViewBag')) {";
            $declarationComponent .= "        \\KeyClass\FileTree::requireOnceFile(INSTALL_DIR.DIRECTORY_SEPARATOR.'frame_src'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'Sagacious'.DIRECTORY_SEPARATOR.'functions.php');";
            $declarationComponent .= "     }";
            $declarationComponent .= " }";
            // $declarationComponent.="/* Se a barra de debug está ativa, termina o 'contador' */";
            $declarationComponent .= " if (DEBUG_BAR == true) {";
            $declarationComponent .= "    \$timer = \$kernelspace->getVariable('timer', 'insiderFrameworkSystem');";
            // $declarationComponent.="    /* Terminamos o 'contador' e exibimos */";
            $declarationComponent .= "    \$timer->debugBar('render');";
            $declarationComponent .= " }";
            $declarationComponent .= "?>";

            // Colocando este código no início conteúdo do arquivo
            $renderCode = $declarationComponent . "\n" . $renderCode;
        }

        $viewconverted['renderCode'] = $renderCode;

        return $viewconverted;
    }

    /**
      Função que remove os comentários da view

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  string  $noCommentsTemplateCode    Código que terá os
      comentários removidos

      @return void  O código modificado diretamente na variável de origem
                    é alterado diretamente via ponteiro
     */
    public function RemoveComments(string &$noCommentsTemplateCode = null) : void {
        if ($noCommentsTemplateCode !== null) {
            // Removendo comentários
            $pattern = '/{\*.*?\*}/si';
            $replacement = '';
            $noCommentsTemplateCode = preg_replace($pattern, $replacement, $noCommentsTemplateCode);
        }
    }

    /**
      Função que converte o código de uma view em php

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  \Sagacious\SgsView  $SgsView    View a ser convertida em php

      @return  array  Retorna um array de string contendo código html e
      os componentes encontrados
     */
    public function convertSGV2PHPAux($SgsView) : array {
        // Variável que irá guardar os componentes encontrados no código
        $componentsFound = array();

        // Lendo conteúdo da view
        $codeView = \KeyClass\FileTree::fileReadContent(INSTALL_DIR . DIRECTORY_SEPARATOR . $SgsView->getViewFilename(), true);
        if ($codeView === false) {
            \KeyClass\Error::i10nErrorRegister('Could not find a view %' . $SgsView->getViewFilename() . '%', 'pack/sys');
        }

        // Contador de declarações de template dentro da view
        $countTemplates = 0;

        // Código de um próvavel template dentro da view
        $templateCode = "";

        // Paths dos templates nas views (se existirem)
        $templatesPath = [];

        // Path da view que está sendo processada
        $viewsPath = array(
            0 => $SgsView->getViewFilename()
        );

        // Blocos detectados na view
        $blocks = [];
        $endblocks = 0;

        // Contadores de blocos de javascripts e css
        $startjavascripts = 0;
        $endjavascripts = 0;
        $startcss = 0;
        $endcss = 0;

        // Regex geral para componentes, views, templates, etc
        $dataGroup = ".*?";
        $generalPattern = "/" . "(?P<allMatch>\{(?P<declaration>[^\s]+)(?<data>" . $dataGroup . ")[ ]*\})" . "/i";

        // Pattern para src
        $srcPattern = "/" . "(.*)src( *)?=( *)?['\"](?P<src>.*)['\"]" . "/i";

        /*
         * Esta função é divida em duas partes. A primeira parte busca declarações de componentes
         * e trata as mesmas (se possível) ou contabiliza-as. Na segunda parte, as declarações
         * que não foram tratadas pela primeira parte são processadas.
         */

        ///////////////////////// PARTE-1 /////////////////////////
        // Buscando por views, templates e blocos dentro do código da view
        $codeView = preg_replace_callback($generalPattern, function($gM) use ($srcPattern, &$viewsPath, &$templatesPath, $codeView, &$templateCode, &$blocks, &$endblocks, &$views, &$componentsFound, $countTemplates, $SgsView, &$startjavascripts, &$endjavascripts, &$startcss, &$endcss) {
            // Verificando declarações literais
            $literal = strpos($gM['allMatch'], ' literal ');
            if ($literal == false) {
                $literal = strpos($gM['allMatch'], ' literal}');
            }

            // Se NÃO é uma declaração literal
            if ($literal === false) {
                // Contabiliza e converte (em alguns casos) as declarações encontradas pela regex
                return $this->processDeclaration($gM, $SgsView, $codeView, $componentsFound, $blocks, $endblocks, $startcss, $endcss, $startjavascripts, $endjavascripts, $viewsPath, $templatesPath, $countTemplates, $templateCode, $srcPattern);
            } else {
                return $gM['allMatch'];
            }
        }, $codeView);
        ///////////////////////// FIM-PARTE-1 /////////////////////////
        ////////////////////////////////////////////////////////////////////////
        // Até aqui foi processado o código da view sem levar em consideração o template e views
        // dentro de views. Daqui para baixo o algoritmo processa o código restante.
        ///////////////////////// PARTE-2 /////////////////////////
        // Se foi encontrado um template na view
        if ($templateCode !== "") {
            // Removendo temporariamente os blocos da view e declaração de template
            $tmpView = $codeView;
            $tmpTemplate = $templateCode;
            $regexTemplate = "/" . "\{( *)?template(.*?)\}" . "/i";
            $tmpView = preg_replace($regexTemplate, "", $tmpView, 1);

            // Para cada bloco encontrado anteriormente
            foreach ($blocks as $blockId => $data) {
                // Removendo a declaração
                $regexBlock = "/" . "{block id( {0,})?=( {0,})?['\"]" . $blockId . "['\"]([^}]+)?}(?P<blockContent>.*){\/block}" . "/Uis";
                $tmpView = preg_replace($regexBlock, "", $tmpView, 1);
                $tmpTemplate = preg_replace($regexBlock, "", $tmpTemplate, 1);

                // Captura o conteúdo correspondente
                preg_match_all($regexBlock, $codeView, $blockMatches, PREG_SET_ORDER);
                if (is_array($blockMatches) && count($blockMatches) > 0) {
                    // Conteúdo do bloco na view
                    $contentBlock = $blockMatches[0]['blockContent'];

                    // Regex de blocos dentro do template
                    $regexBlockTemplate = "/" . "{block id=['\"]( {0,})?" . $blockId . "['\"][}](?P<blockContent>.*){\/block}" . "/Uis";
                    // Se é para preservar o conteúdo do bloco
                    if ($data['settings']['keepContent'] === true) {
                        // Recuperando provável conteúdo do bloco
                        preg_match_all($regexBlockTemplate, $templateCode, $blockMatches, PREG_SET_ORDER);
                        if (isset($blockMatches[0]['blockContent'])) {
                            $contentBlock = $blockMatches[0]['blockContent'] . $contentBlock;
                        }
                    }

                    // Substituindo os códigos de view no template
                    $templateCode = preg_replace_callback($regexBlockTemplate, function($bVM) use ($contentBlock) {
                        return $contentBlock;
                    }, $templateCode);
                }
                // Se não encontrou o bloco completo da view
                else {
                    \KeyClass\Error::i10nErrorRegister('The declaration of block %' . $blockId . '% appears to be incomplete at %' . $SgsView->getViewFilename() . '%', 'pack/sys');
                }
            }

            // Código fora de blocos na view
            if (trim($tmpView) !== "") {
                \KeyClass\Error::i10nErrorRegister('A code has been found outside of declaration of blocks at view %' . $SgsView->getViewFilename() . '%. Views with a declaration of template must be keep our codes inside blocks, otherwise them will not be included on response', 'pack/sys');
            }
            unset($tmpView);

            $templateCode = preg_replace_callback($generalPattern, function($gMT) use ($srcPattern, &$templatesPath, &$viewsPath, &$componentsFound) {
                // Verificando declarações literais
                $literal = strpos($gMT['allMatch'], ' literal ');
                if ($literal == false) {
                    $literal = strpos($gMT['allMatch'], ' literal}');
                }

                // Se NÃO é uma declaração literal
                if ($literal === false) {
                    switch ($gMT['declaration']) {
                        case 'template':
                            \KeyClass\Error::i10nErrorRegister('Declarations of templates inside template files are not accepted', 'pack/sys');
                        break;
                    
                        case 'view':
                            preg_match_all($srcPattern, strtolower($gMT['allMatch']), $vWSRCMatches, PREG_SET_ORDER);
                            return $this->convertViewPathToCode($vWSRCMatches, $componentsFound, $viewsPath, $templatesPath);
                        break;
                    
                        case "javascript_file":
                        case "css_file":
                            return $gMT['allMatch'];
                        break;
                    
                        case "component":
                            return $this->processComponentCode($gMT, $componentsFound);
                        break;

                        case 'injected_html':
                            return '<?php 
                            global $kernelspace;
                            $injectedHtml = $kernelspace->getVariable("injectedHtml", "insiderFrameworkSystem");
                            echo $injectedHtml; ?>';
                        break;
                    
                        case 'injected_js':
                            return '<?php 
                            global $kernelspace;
                            $injectedScripts = $kernelspace->getVariable("injectedScripts", "insiderFrameworkSystem");
                            echo $injectedScripts; ?>';
                        break;
                    
                        case 'injected_css':
                            return '<?php 
                            global $kernelspace;
                            $injectedCss = $kernelspace->getVariable("injectedCss", "insiderFrameworkSystem");
                            echo $injectedCss; ?>';
                        break;

                        case "l10n":
                            return $this->processI10nCode($gMT);
                        break;

                        // Comentários
                        case "*":
                        break;

                        default:
                            return $gMT['allMatch'];
                        break;
                    }
                } else {
                    return $gMT['allMatch'];
                }
            }, $templateCode);
        }
        // Se não foi encontrado um template
        else {
            // Se não foi encontrado um template na view mas a mesma faz referência a blocos
            if (count($blocks) > 0 || $endblocks > 0) {
                \KeyClass\Error::i10nErrorRegister('The view %' . $SgsView->getViewFilename() . '% declares blocks inside its content, however no template was specified', 'pack/sys');
            }

            // Se não existe código do template, então ele é o código da view
            $templateCode = $codeView;
        }

        // Processa o código de blocos CSS e JS
        $this->processJsCode($gMT, $templateCode);
        $this->processCssCOde($gMT, $templateCode);

        // Removendo a palavra literal (uma única vez) de todas os matches
        $templateCode = preg_replace_callback($generalPattern, function($gMT) use ($srcPattern, &$templatesPath, &$viewsPath, &$componentsFound) {
            switch ($gMT['declaration']) {
                case 'injected_html':
                case 'injected_js':
                case 'javascript_file':
                case 'javascript':
                case '/javascript':
                case 'template':
                case 'view':
                case 'block':
                case '/block':
                case 'component':
                case 'injected_css':
                case 'css_file':
                case 'css':
                case '/css':
                case "l10n":
                    $regexLiteral = "/" . "(?P<part1>\{.*) (?P<literal>literal)(?P<part2>.*\})" . "/m";

                    $gMT['allMatch'] = preg_replace_callback($regexLiteral, function($gMT) use ($srcPattern, &$templatesPath, &$viewsPath, &$componentsFound) {
                        return $gMT['part1'] . $gMT['part2'];
                    }, $gMT['allMatch']);

                    return $gMT['allMatch'];
                break;
            
                default:
                    return $gMT['allMatch'];
                break;
            }
        }, $templateCode);

        // Verificando se o número de tags dos elementos está certo
        $this->checkOpenCloseTags($blocks, $endblocks, $startcss, $endcss, $startjavascripts, $endjavascripts, $countTemplates, $SgsView);

        // Removendo comentários
        $this->RemoveComments($templateCode);
        ///////////////////////// FIM-PARTE-2 /////////////////////////

        return array(
            'renderCode' => $templateCode,
            'components' => $componentsFound,
            'templatesPath' => $templatesPath,
            'viewsPath' => $viewsPath
        );
    }

    /**
      Converte uma declaração encontrada pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $gMT               Array de matches de declarações
      @param  string $templateCode      Código do template+views processados

      @return void Without return
     */
    private function processCssCode(array &$gMT = null, string $templateCode = null) : void {
        if ($gMT !== null && $templateCode !== null ) {
            // CSS
            $cssPattern = "/" . "(?P<allMatch>{css}(?P<cssContent>.*)?{\/css})" . "/Uis";
            $templateCode = preg_replace_callback($cssPattern, function($gMT) use ($templateCode) {
                // Verificando declarações literais
                $literal = strpos($gMT['allMatch'], ' literal ');
                if ($literal == false) {
                    $literal = strpos($gMT['allMatch'], ' literal}');
                }

                // Se NÃO é uma declaração literal
                if ($literal === false) {
                    $sgsPage = new \Sagacious\SgsPage();
                    return $sgsPage->cssMinify($gMT['cssContent']);
                }

                return $gMT['allMatch'];
            }, $templateCode);
        }
    }

    /**
      Converte uma declaração encontrada pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $gMT               Array de matches de declarações
      @param  string $templateCode      Código do template+views processados

      @return void Without return
     */
    private function processJsCode(array &$gMT = null, string $templateCode = null) : void {
        if ($gMT !== null && $templateCode !== null ) {
            // Tratando declarações javascript e css
            // JS
            $javascriptPattern = "/" . "(?P<allMatch>{javascript}(?P<jsContent>.*)?{\/javascript})" . "/Uis";
            $templateCode = preg_replace_callback($javascriptPattern, function($gMT) use ($templateCode) {
                // Verificando declarações literais
                $literal = strpos($gMT['allMatch'], ' literal ');
                if ($literal == false) {
                    $literal = strpos($gMT['allMatch'], ' literal}');
                }

                // Se NÃO é uma declaração literal
                if ($literal === false) {
                    $sgsPage = new \Sagacious\SgsPage();
                    return $sgsPage->jsMinify($gMT['jsContent']);
                }

                return $gMT['allMatch'];
            }, $templateCode);
        }
    }

    /**
      Verifica o número de declarações abertas/fechadas

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $blocks           Array de blocos encontrados
      @param  int    $endblocks        Quantidade de blocos fechados
      @param  int    $startcss         Quantidade de declarações css iniciadas
      @param  int    $endcss           Quantidade de declarações css encerradas
      @param  int    $startjavascripts Quantidade de declarações js iniciadas
      @param  int    $endjavascripts   Quantidade de declarações js encerradas
      @param  int    $countTemplates   Quantidade de decalrações de template
      @param  object $SgsView          Objeto da view

      @return void Without return
     */
    private function checkOpenCloseTags(array &$blocks, int &$endblocks, int &$startcss, int &$endcss, int &$startjavascripts, int &$endjavascripts, int &$countTemplates, &$SgsView) : void {
        // Se foram declarados templates
        if ($countTemplates > 0) {
            // Se não foram encontrados blocos na view
            if (count($blocks) === 0) {
                \KeyClass\Error::i10nErrorRegister('A template has been defined inside view %' . $SgsView->getViewFilename() . '% but no declarations of blocks has been found at template', 'pack/sys');
            }

            // Se existe divergência entre o número de blocos declarados e o número de fechamentos de blocos
            if (count($blocks) !== $endblocks) {
                \KeyClass\Error::i10nErrorRegister('Error in declaration of blocks at view %' . $SgsView->getViewFilename() . '%', 'pack/sys');
            }
        }

        // Se existe divergência entre o número de blocos js declarados e o número de fechamentos de blocos
        if ($startjavascripts !== $endjavascripts) {
            \KeyClass\Error::i10nErrorRegister('Error in declaration of javascripts at view %' . $SgsView->getViewFilename() . '%', 'pack/sys');
        }

        // Se existe divergência entre o número de blocos css declarados e o número de fechamentos de blocos
        if ($startcss !== $endcss) {
            \KeyClass\Error::i10nErrorRegister('Error in declaration of css at view %' . $SgsView->getViewFilename() . '%', 'pack/sys');
        }
    }

    /**
      Contabiliza e converte (em alguns casos) as declarações encontradas pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $gM               Array de matches de declarações
      @param  object $SgsView          Objeto da view
      @param  string $codeView         Código da view sendo processada
      @param  array  $componentsFound  Array de componentes encontrados
      @param  array  $blocks           Array de blocos encontrados
      @param  int    $endblocks        Quantidade de blocos fechados
      @param  int    $startcss         Quantidade de declarações css iniciadas
      @param  int    $endcss           Quantidade de declarações css encerradas
      @param  int    $startjavascripts Quantidade de declarações js iniciadas
      @param  int    $endjavascripts   Quantidade de declarações js encerradas
      @param  array  $viewsPath        Path das views
      @param  array  $templatesPath    Path dos templates
      @param  int    $countTemplates   Contador de declaração de templates
      @param  string $templateCode     Código do template+views processados
      @param  string $srcPattern       Regex de elementos com SRC

      @return  string  Código da declaração convertida
     */
    private function processDeclaration(array $gM, &$SgsView, string &$codeView, array &$componentsFound, array &$blocks, int &$endblocks, int &$startcss, int &$endcss, int &$startjavascripts, int &$endjavascripts, array &$viewsPath, array &$templatesPath, int &$countTemplates, string &$templateCode, string &$srcPattern) : string {
        switch ($gM['declaration']) {
            case 'template':
                return $this->processTemplateCode($SgsView, $countTemplates, $srcPattern, $gM, $codeView, $templateCode, $templatesPath);
            break;

            case 'view':
                return $this->processViewCode($srcPattern, $gM, $componentsFound, $viewsPath, $templatesPath);
            break;

            // Identificando os blocos
            case 'block':
                return $this->processBlockCode($gM, $blocks);
            break;
        
            case '/block':
                // Incrementando contador de blocos
                $endblocks++;

                // Mantendo a declaração no código da view para tratamento posterior
                return $gM['allMatch'];
            break;

            case "component":
                return $this->processComponentCode($gM, $componentsFound);
            break;

            case "css":
                // Incrementando contador de css
                $startcss++;

                // Mantendo a declaração no código da view para tratamento posterior
                return $gM['allMatch'];
            break;
        
            case "/css":
                // Incrementando contador de css
                $endcss++;

                // Mantendo a declaração no código da view para tratamento posterior
                return $gM['allMatch'];
            break;

            case "javascript":
                // Incrementando contador de javascripts
                $startjavascripts++;

                // Mantendo a declaração no código da view para tratamento posterior
                return $gM['allMatch'];
            break;
        
            case '/javascript':
                // Incrementando contador de javascripts
                $endjavascripts++;

                // Mantendo a declaração no código da view para tratamento posterior
                return $gM['allMatch'];
            break;

            case "javascript_file":
                // Regex para javascript_file
                return $this->processJavaScriptFileCode($gM);
            break;
        
            case "css_file":
                return $this->processCssFileCode($gM);
            break;
        
            case "l10n":
                return $this->processI10nCode($gM, $SgsView);
            break;
        
            default:
                return $gM['allMatch'];
            break;
        }
    }

    /**
      Converte uma declaração encontrada pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $gM               Array de matches de declarações
      @param  object $SgsView          Objeto da view

      @return  string  Código da declaração convertida
     */
    private function processI10nCode(array &$gM, &$SgsView) : string {
        // Pattern para id
        $i10nPattern = "/" . "\{(.*)id( {0,})?=( {0,})?['|\"](?P<id>[^'|\"]*)['|\"](( {1,})? ((lang)?( {0,})?=( {0,})?(['|\"](?P<lang>[^'|\"]*))))?( settings( {0,})?=( {0,})?['|\"](?P<settings>[^'|\"]*))?" . "/";
        preg_match_all($i10nPattern, strtolower($gM['allMatch']), $i10nIDMatches, PREG_SET_ORDER);

        // Se o ID foi encontrado no bloco
        if (is_array($i10nIDMatches) && count($i10nIDMatches) > 0) {
            if (trim($i10nIDMatches[0]['id']) !== "") {
                $id = $i10nIDMatches[0]['id'];

                // ID não encontrado
                if ($id === "") {
                    \KeyClass\Error::i10nErrorRegister('The declaration of translation %' . $gM['allMatch'] . '% don\'t have a valid declaration of ID (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
                }

                // Pegando a linguagem de tradução
                $lang = LINGUAS;
                if (isset($i10nIDMatches[0]['lang'])) {
                    $lang = $i10nIDMatches[0]['lang'];
                }
                $lang = strtolower($lang);

                // Variável que define se a string será exibida com echo ou retornada
                $returncode = false;

                // Variável que informa que não devem ser printadas tags php
                $nophptags = false;

                // Se existem propriedades
                if (isset($i10nIDMatches[0]['settings'])) {
                    $settings = explode(';', $i10nIDMatches[0]['settings']);

                    // Para cada setting
                    foreach ($settings as $setting) {
                        if (trim($setting) !== "") {
                            switch (strtolower($setting)) {
                                case 'return-code':
                                    $returncode = true;
                                break;
                            
                                case 'no-php-tags':
                                    $nophptags = true;
                                break;
                            }
                        }
                    }
                }

                // Definido o comando que irá chamar o método de tradução
                $cmd = "echo";
                if ($returncode) {
                    $cmd = "return";
                }

                // Pack da view
                $pack = $SgsView->getPack();

                // Se encontrar um parâmetro para não imprimir as tags php
                if ($nophptags !== false) {
                    // Substituindo o que encontrou
                    $gM['allMatch'] = preg_replace("{" . $gM['allMatch'] . "}", $cmd . " \\Sagacious\\SgsPage::translateString('$pack', '$id', '$lang')", $gM['allMatch']);
                } else {
                    // Substituindo o que encontrou
                    $gM['allMatch'] = preg_replace("{" . $gM['allMatch'] . "}", "<?php " . $cmd . " \\Sagacious\\SgsPage::translateString('$pack', '$id', '$lang'); ?>", $gM['allMatch']);
                }
            }
        }

        return $gM['allMatch'];
    }

    /**
      Converte uma declaração encontrada pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $gM   Array de matches de declarações

      @return  string  Código da declaração convertida
     */
    private function processCssFileCode(array $gM) : string {
        // Regex para css_file
        $regex = "/" . "{( {0,})?css_file src=['\"](?P<src>(.*))['\"]( {0,})?}" . "/";

        // Verificando se a declaração é válida
        preg_match_all($regex, $gM[0], $matchescssfiles, PREG_SET_ORDER);
        if (count($matchescssfiles) === 0 || !isset($matchescssfiles[0]['src'])) {
            \KeyClass\Error::i10nErrorRegister('A declaration of css_file has been found but it\'s not valid at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
        } else {
            // Definido o valor em uma variável para
            // ser utilizada em alguns casos
            $csspath = $matchescssfiles[0]['src'];

            // KeyClass Code
            $sgsPage = new \Sagacious\SgsPage();

            // Removendo barra no início do nome do arquivo (caso exista)
            if ($csspath[0] == "/") {
                $csspath = \KeyClass\Code::extractString($csspath, 1, strlen($csspath));
            }

            // Se o arquivo especificado não for CSS, não é compatível
            $ext = pathinfo($csspath, PATHINFO_EXTENSION);
            if (strtolower($ext) !== "css") {
                \KeyClass\Error::i10nErrorRegister('The file specified at %' . $csspath . '% it\'s not compatible with the minification of css', 'pack/sys');
            }

            // Minificando o arquivo em tempo de execução (se não existir ou for diferente)
            // Arquivo não existe
            if (!(file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $csspath))) {
                \KeyClass\Error::i10nErrorRegister('CSS file not found: %' . $csspath . '%', 'pack/sys');
            }

            // Se o arquivo minificado não existir
            $minnamecssfile = str_replace(".css", ".min.css", $csspath);
            if (!(file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamecssfile))) {
                // Cria o arquivo minificado
                $minifiedcsscontent = $sgsPage->cssMinify(\KeyClass\FileTree::fileReadContent(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $csspath));
                \KeyClass\FileTree::fileWriteContent(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamecssfile, $minifiedcsscontent);
            }
            // Se o arquivo minificado existir
            else {
                // Recuperando o hash de ambos os arquivos
                $hashfile = md5_file(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $csspath);
                $hashfilemin = md5_file(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamecssfile);

                // Se o hash for diferente
                if ($hashfile !== $hashfilemin) {
                    // Reconstrói o arquivo
                    // Objeto KC_Ftree utilizado na função
                    \KeyClass\FileTree::deleteFile(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamecssfile);
                    $minifiedcsscontent = $sgsPage->cssMinify(\KeyClass\FileTree::fileReadContent(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $csspath));
                    \KeyClass\FileTree::fileWriteContent(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamecssfile, $minifiedcsscontent);
                }
            }

            // Coloca a linha html que chama o css
            return "<link href='" . REQUESTED_URL . "/" . $minnamecssfile . "' rel='stylesheet' type='text/css'>";
        }
    }

    /**
      Converte uma declaração encontrada pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $gM               Array de matches de declarações

      @return  string  Código da declaração convertida
     */
    private function processJavaScriptFileCode(array $gM) : string {
        // Pattern para javascript_file
        $regex = "/" . "{( {0,})?javascript_file src=['\"](?P<src>(.*))['\"]( {0,})?}" . "/";

        // Verificando se a declaração é válida
        preg_match_all($regex, $gM[0], $matchesjsfiles, PREG_SET_ORDER);
        if (count($matchesjsfiles) === 0 || !isset($matchesjsfiles[0]['src'])) {
            \KeyClass\Error::i10nErrorRegister('A declaration of js_file has been found but it\'s invalid at ' . $gM['allMatch'] . ' (' . $SgsView->getViewFilename() . ')', 'pack/sys');
        } else {
            // Definido o valor em uma variável para
            // ser utilizada em alguns casos
            $jspath = $matchesjsfiles[0]['src'];

            $sgsPage = new \Sagacious\SgsPage();

            // Removendo barra no início do nome do arquivo (caso exista)
            if ($jspath[0] == "/") {
                $jspath = \KeyClass\Code::extractString($jspath, 1, strlen($jspath));
            }

            // Se o arquivo especificado não for JS, não é compatível
            $ext = pathinfo($jspath, PATHINFO_EXTENSION);
            if (strtolower($ext) !== "js") {
                \KeyClass\Error::i10nErrorRegister('The file specified %' . $jspath . '% it\'s not compatible with the minification of javascripts', 'pack/sys');
            }

            // Minificando o arquivo em tempo de execução (se não existir ou for diferente)
            // Arquivo não existe
            if (!(file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $jspath))) {
                \KeyClass\Error::i10nErrorRegister('JS File not found: %' . $jspath . '%', 'pack/sys');
            }

            // Se o arquivo minificado não existir
            $minnamejsfile = str_replace(".js", ".min.js", $jspath);
            if (!(file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamejsfile))) {
                // Cria o arquivo minificado
                $minifiedjscontent = $sgsPage->jsMinify(\KeyClass\FileTree::fileReadContent(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $jspath));
                \KeyClass\FileTree::fileWriteContent(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamejsfile, $minifiedjscontent);
            }

            // Se o arquivo minificado existir
            else {
                // Recuperando o hash de ambos os arquivos
                $hashfile = md5_file(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $jspath);
                $hashfilemin = md5_file(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamejsfile);

                // Se o hash for diferente
                if ($hashfile !== $hashfilemin) {
                    // Reconstrói o arquivo
                    \KeyClass\FileTree::deleteFile(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamejsfile);
                    $minifiedjscontent = $sgsPage->jsMinify(\KeyClass\FileTree::fileReadContent(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $jspath));
                    \KeyClass\FileTree::fileWriteContent(INSTALL_DIR . DIRECTORY_SEPARATOR . "web" . DIRECTORY_SEPARATOR . $minnamejsfile, $minifiedjscontent);
                }
            }

            // Coloca a linha html que chama o javascript
            return "<script type='text/javascript' src='" . REQUESTED_URL . "/" . $minnamejsfile . "'></script>";
        }
    }

    /**
      Converte uma declaração encontrada pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $gM                 Array de matches de declarações
      @param  array  $componentsFound    Array de componentes encontrados

      @return  string  Código do componente convertido
     */
    private function processComponentCode(array &$gM, array &$componentsFound) : string {
        // Pattern para components
        $componentPattern = "/" . "\{(.*)id( {0,})?=( {0,})?['|\"](?P<id>[^'|\"]*)['|\"](( {1,})? ((settings)?( {0,})?=( {0,})?(['|\"](?P<settings>.+?(?=['|\"]))))['|\"]( {0,})?\})?" . "/";

        preg_match_all($componentPattern, strtolower($gM['allMatch']), $blIDMatches, PREG_SET_ORDER);

        // Se o ID foi encontrado no bloco
        if (is_array($blIDMatches) && count($blIDMatches) > 0) {
            if (trim($blIDMatches[0]['id']) !== "") {
                // Recuperando o nome do componente
                $id = $blIDMatches[0]['id'];

                // ID não encontrado
                if ($id === "") {
                    \KeyClass\Error::i10nErrorRegister('The component %' . $gM['allMatch'] . '% don\'t have a valid declaration of ID (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
                }

                // Guardando o componente encontrado no array
                $componentsFound[] = $id;

                // Variável que define se o código será exibido ou retornado
                $returncode = false;

                // Variável que informa que não devem ser printadas tags php
                $nophptags = false;

                if (isset($blIDMatches[0]['settings'])) {
                    $settings = explode(';', $blIDMatches[0]['settings']);

                    // Para cada setting
                    foreach ($settings as $setting) {
                        if (trim($setting) !== "") {
                            switch (strtolower($setting)) {
                                case 'return-code':
                                    $returncode = true;
                                break;
                            
                                case 'no-php-tags':
                                    $nophptags = true;
                                break;
                            }
                        }
                    }
                }

                // Deve ser retornado o código ao invés de exibido
                if ($returncode) {
                    $typeFunction = "ReturnCode()";
                } else {
                    $typeFunction = "EchoCode()";
                }

                // Se encontrar um parâmetro para não imprimir as tags php
                if ($nophptags !== false) {
                    // Substituindo o que encontrou
                    $gM['allMatch'] = preg_replace("{" . $gM['allMatch'] . "}", "$" . $id . "->" . $typeFunction, $gM['allMatch']);
                } else {
                    // Substituindo o que encontrou
                    $gM['allMatch'] = preg_replace("{" . $gM['allMatch'] . "}", "<?php $" . $id . "->" . $typeFunction . "; ?>", $gM['allMatch']);
                }
                return $gM['allMatch'];
            }
        }
        // Se não foi encontrado um ID na declaração
        else {
            \KeyClass\Error::i10nErrorRegister('A declaration of component has been found but there is not a ID specified at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
        }
    }

    /**
      Converte uma declaração encontrada pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $gM               Array de matches de declarações
      @param  array  $blocks           Array de blocos encontrados

      @return  string  Código do bloco convertido
     */
    private function processBlockCode(array &$gM, array &$blocks) : string {
        $viewFileName = "Unknow view";
        if ($this->SgsView !== null){
            $viewFileName = $this->SgsView->getViewFilename();
        }
        
        // Pattern para id
        $id_pattern = "/" . "\{(.*)id( {0,})?=( {0,})?['|\"](?P<id>[^'|\"]*)" . "/i";

        preg_match_all($id_pattern, strtolower($gM['allMatch']), $blIDMatches, PREG_SET_ORDER);

        // Pattern para um bloco inteiro com settings
        $blockWithSettingsPattern = "/" . "\{(.*)id( {0,})?=( {0,})?['|\"](?P<id>.*)['|\"]( {0,})((settings)?( {0,})?=( {0,})?(['|\"](?P<settings>.+?(?=['|\"]))))['|\"]( {0,})?\}" . "/i";

        // Se o ID foi encontrado no bloco
        if (is_array($blIDMatches) && count($blIDMatches) > 0) {
            if (trim($blIDMatches[0]['id']) !== "") {
                if (!isset($blocks[$blIDMatches[0]['id']])) {
                    // Verificando se o bloco tem settings
                    preg_match_all($blockWithSettingsPattern, strtolower($gM['allMatch']), $blSettingsMatches, PREG_SET_ORDER);

                    // Variáveis possíveis para um bloco
                    $keepContent = false;

                    // Se é um bloco que contém settings
                    if (is_array($blSettingsMatches) && count($blSettingsMatches) > 0) {
                        $settings = explode(';', $blSettingsMatches[0]['settings']);

                        // Para cada setting
                        foreach ($settings as $setting) {
                            if (trim($setting) !== "") {
                                switch (strtolower($setting)) {
                                    case 'keep-content':
                                        $keepContent = true;
                                    break;
                                }
                            }
                        }
                    }

                    // Gravando o ID do bloco e as propriedades
                    $blocks[$blIDMatches[0]['id']]['settings']['keepContent'] = $keepContent;

                    // Mantendo a declaração no código da view para tratamento posterior
                    return $gM['allMatch'];
                } else {
                    \KeyClass\Error::i10nErrorRegister('A declaration of block has been found but the specified ID has been already declared before at %' . $gM['allMatch'] . '% (%' . $viewFileName . '%)', 'pack/sys');
                }
            }

            // ID vazio
            else {
                \KeyClass\Error::i10nErrorRegister('A declaration of block has been found but the specified ID is invalid at ' . $gM['allMatch'] . ' (%' . $viewFileName . '%)', 'pack/sys');
            }
        }
        // Se não foi encontrado um ID no bloco
        else {
            \KeyClass\Error::i10nErrorRegister('A declaration of block has been found but there is not an ID specified at %' . $gM['allMatch'] . '% (%' . $viewFileName . '%)', 'pack/sys');
        }
    }

    /**
      Converte uma declaração encontrada pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  string $srcPattern         Pattern regex
      @param  array  $gM                 Array de matches de declarações
      @param  array  $componentsFound    Array de componentes encontrados
      @param  array  $viewsPath          Array de views encontradas
      @param  array  $templatesPath      Paths onde ficam localizados os templates

      @return  string  Código da view convertido
     */
    private function processViewCode(string &$srcPattern, array &$gM, array &$componentsFound, array &$viewsPath, array &$templatesPath) : string {
        //preg_match_all($srcPattern, strtolower($gM['allMatch']), $vWSRCMatches, PREG_SET_ORDER);
        preg_match_all($srcPattern, $gM['allMatch'], $vWSRCMatches, PREG_SET_ORDER);

        // Se o SRC foi encontrado na view
        if (is_array($vWSRCMatches) && count($vWSRCMatches) > 0) {
            if (trim($vWSRCMatches[0]['src']) !== "") {
                return $this->convertViewPathToCode($vWSRCMatches, $componentsFound, $viewsPath, $templatesPath);
            }

            // SRC vazio
            else {
                \KeyClass\Error::i10nErrorRegister('A declaration of view has been found but the specified SRC is invalid at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
            }
        } else {
            \KeyClass\Error::i10nErrorRegister('A declaration of view has been found but there is not an SRC specified at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
        }
    }

    /**
      Converte uma declaração encontrada pela regex

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  object $SgsView          Objeto da view
      @param  int    $countTemplates   Contador de declaração de templates
      @param  string $srcPattern       Pattern regex
      @param  array  $gM               Array de matches de declarações
      @param  string $codeView         Código da view sendo processada
      @param  string $templateCode     Código do template pós processamento
      @param  array  $templatesPath    Templates que foram detectados

      @return  string  Código do template convertido
     */
    private function processTemplateCode($SgsView, int &$countTemplates, string &$srcPattern, array &$gM, string &$codeView, string &$templateCode, array &$templatesPath) : string {
        if ($countTemplates == 0) {
            $countTemplates++;

            // Verificando se o template é a primeira declaração da view
            preg_match_all($srcPattern, strtolower($gM['allMatch']), $tpSRCMatches, PREG_SET_ORDER);
            list($before) = str_split(preg_replace('/\s+/', '', $codeView), 9);

            if ($before !== '{template') {
                \KeyClass\Error::i10nErrorRegister('The declaration of template must be written on the first line of the file at view (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
            }

            // Se o SRC foi encontrado no template
            if (is_array($tpSRCMatches) && count($tpSRCMatches) > 0) {
                if (trim($tpSRCMatches[0]['src']) !== "") {
                    // Se não tem a declaração do pack
                    if (strpos($tpSRCMatches[0]['src'], "::") === false) {
                        // Tentando pegar o pack via explode
                        $dataSRC = explode(DIRECTORY_SEPARATOR, $tpSRCMatches[0]['src']);
                        if (count($dataSRC) < 3) {
                            \KeyClass\Error::i10nErrorRegister('The declaration of template appears to be incomplete at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
                        }

                        // 0 = pack
                        // 1 = diretório templates
                        // 2 >= restante do path
                        $file = "packs" . DIRECTORY_SEPARATOR . $dataSRC[0] . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $dataSRC[2] . ".sgv";
                    }
                    // Se tem a declaração do pack
                    else {
                        $dataSRC = explode('::', $tpSRCMatches[0]['src']);

                        // 0 = pack
                        // 1 >= restante do path (sem templates)
                        $file = "packs" . DIRECTORY_SEPARATOR . $dataSRC[0] . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $dataSRC[1] . ".sgv";
                    }
                    
                    // Inserindo template detectado
                    $templatesPath[]=$file;

                    // Lendo conteúdo do template
                    $templatesPath[] = INSTALL_DIR . DIRECTORY_SEPARATOR . $file;

                    // Objeto KC_Ftree utilizado na função
                    $templateCode = \KeyClass\FileTree::fileReadContent(INSTALL_DIR . DIRECTORY_SEPARATOR . $file, true);

                    // Mantendo a declaração no código da view para tratamento posterior
                    return $gM['allMatch'];
                }

                // SRC vazio
                else {
                    \KeyClass\Error::i10nErrorRegister('A declaration of template has been found but the specified SRC is invalid at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
                }
            }
            // Se não foi encontrado um SRC no template
            else {
                \KeyClass\Error::i10nErrorRegister('A declaration of template has been found but there is not a SRC specified at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
            }
        } else {
            \KeyClass\Error::i10nErrorRegister('More than one template has been declared at view %' . $SgsView->getViewFilename() . '%', 'pack/sys');
        }
    }

    /**
      Converte uma declaração encontrada pela regex em código HTML

      @author Marcello Costa

      @package Sagacious\SgsTemplate

      @param  array  $matches            Matches de acordo com as funções da classe
      @param  array  $componentsFound    Array de componentes encontrados
      @param  array  $viewsPath          Array de views encontradas
      @param  array  $templatesPath      Array de templates encontradas

      @return  string  Código da view convertido em HTML
     */
    private function convertViewPathToCode(array $matches, array &$componentsFound, array &$viewsPath, array &$templatesPath) : string {
        // Se o SRC foi encontrado na view
        if (is_array($matches) && count($matches) > 0) {
            if (trim($matches[0]['src']) !== "") {
                // Criando novo objeto SgsView para ser lido
                $insideSgsView = new \Sagacious\SgsView();

                // Se não tem a declaração do pack
                if (strpos($matches[0]['src'], "::") === false) {
                    // Tentando pegar o pack via explode
                    $dataSRC = explode(DIRECTORY_SEPARATOR, $matches[0]['src']);
                    if (count($dataSRC) < 3) {
                        \KeyClass\Error::i10nErrorRegister('The declaration of view appears to be incomplete at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
                    }

                    // 0 = pack
                    // 1 = diretório views
                    // 2 >= restante do path
                    $file = join(DIRECTORY_SEPARATOR, array_slice($dataSRC, 2, count($dataSRC) - 2)) . ".sgv";
                    $insideSgsView->setViewFilename($file, $dataSRC[0]);
                }
                // Se tem a declaração do pack
                else {
                    $insideSgsView->setViewFilename($matches[0]['src'] . ".sgv");
                }

                // Recuperando código convertido da view
                // e inserindo no lugar da declaração
                $viewconverted = $this->convertSGV2PHPAux($insideSgsView);

                $componentsFound = array_merge($componentsFound, $viewconverted['components']);

                // Inserindo templates e views encontradas no processamento para serem retornados
                $templatesPath = array_merge($viewconverted['templatesPath'], $templatesPath);
                $viewsPath = array_merge($viewconverted['viewsPath'], $viewsPath);

                return $viewconverted['renderCode'];
            }

            // SRC vazio
            else {
                \KeyClass\Error::i10nErrorRegister('A declaration of view as been found but the SRC specified is invalid at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
            }
        } else {
            \KeyClass\Error::i10nErrorRegister('A declaration of view as been found but there is not a SRC specified at %' . $gM['allMatch'] . '% (%' . $SgsView->getViewFilename() . '%)', 'pack/sys');
        }
    }

}

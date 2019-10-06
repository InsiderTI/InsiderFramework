<?php
/**
  Arquivo Sagacious\SgsPage
*/

// Namespace do Sagacious
namespace Sagacious;

/**
   Classe de tratamento de elementos na página

   @author Marcello Costa

   @package Sagacious\SgsPage
 */
class SgsPage{
    /**
        Atualiza dinamicamente o CSS da página quando,
        por exemplo, um novo componente é criado e este deve
        modificar o css da página
     
        @author Marcello Costa

        @package Sagacious\SgsPage
    
        @param  string  $css    Código CSS (com tag style) a ser colocado na página
     
        @return bool Retorno da operação
    */
    public function updateCSSOfPage(string $css) : bool {
        global $kernelspace;
        $cssExploded=explode("\n", $css);

        // Novo css em uma única linha
        $newcss="";

        // Contando número de linhas
        $countCss=count($cssExploded);

        // Para cada linha
        foreach ($cssExploded as $i => $cssline) {
            // Se ainda não chegou no final
            if ($i < $countCss-1) {
                // Adiciona uma quebra de linha (para o javascript
                // funcionar corretamente)
                $newcss.=$cssline."\n";
            }

            // Se chegou ao final
            else {
                $newcss.=$cssline;
            }
        }

        // Inserindo css na variável global
        $injectedCss = $kernelspace->getVariable('injectedCss', 'insiderFrameworkSystem');
        $injectedCss = $injectedCss . $newcss;
        $kernelspace->setVariable(array('injectedCss' => $injectedCss), 'insiderFrameworkSystem');

        return true;
    }

    /**
        Atualiza dinamicamente o JS da página quando,
        por exemplo, um novo componente é criado e este deve
        modificar o js da página
     
        @author Marcello Costa

        @package Sagacious\SgsPage
     
        @param  string  $js    Código JS a ser adicionado (com tag <script>);
     
        @return Void Without return
    */
    public function updateJSOfPage(string $js) : void {
        global $kernelspace;
        $injectedScripts = $kernelspace->getVariable('injectedScripts', 'insiderFrameworkSystem');
        $injectedScripts = $injectedScripts . $js;
        $kernelspace->setVariable(array('injectedScripts' => $injectedScripts), 'insiderFrameworkSystem');
    }

    /**
        Atualiza dinamicamente o HTML da página quando,
        por exemplo, um novo componente é criado e este deve
        modificar o html da página
     
        @author Marcello Costa

        @package Sagacious\SgsPage
     
        @param  string  $html    Código HTML a ser adicionado;
     
        @return Void Without return
    */
    public function updateHTMLOfpage(string $html) : void {
        global $kernelspace;
        $kernelspace->setVariable(array(
           'injectedHtml' => $kernelspace->getVariable('injectedHtml', 'insiderFrameworkSystem').$html
        ), 'insiderFrameworkSystem');
    }

    /**
        Função que minifica um código JS
     
        @author Marcello Costa

        @package Sagacious\SgsPage
     
        @param  string  $js    Código JS a ser minificado
     
        @return  string  JS minificado
    */
    public static function jsMinify(string $js) : string {
        // Remove comentários
        $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
        $js = preg_replace($pattern, '', $js);

        // Colocando tudo em uma linha
        $js = str_replace(array("\n","\r"),'',$js);

        // Remove múltiplos espaços por apenas um
        $js = preg_replace('!\s+!',' ',$js);

        return $js;
    }

    /**
        Função que minifica um código CSS
     
        @author Marcello Costa

        @package Sagacious\SgsPage
     
        @param  string  $css    Código CSS a ser minificado
     
        @return  string  CSS minificado
    */
    public static function cssMinify(string $css) : string {
        // Remove comentários
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

        // Remove espaços após dois pontos
        $css = str_replace(': ', ':', $css);

        // Remove espaços em branco
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);

        // Retorna o CSS minificado
        return $css;
    }

    /**
        Função que traduz uma string baseado no ID
     
        @author Marcello Costa

        @package Sagacious\SgsPage
     
        @param  string  $pack    Nome do pack onde está localizada a tradução
        @param  string  $id      Id da string a ser traduzida
        @param  string  $lang    Linguagem para qual será traduzida a string
     
        @return  string  CSS minificado
    */
    public static function translateString(string $pack, string $id, string $lang=LINGUAS) : string {
      // Se não existe o diretório de tradução
      $pathLang = INSTALL_DIR.DIRECTORY_SEPARATOR."packs".DIRECTORY_SEPARATOR.$pack.DIRECTORY_SEPARATOR."i10n".DIRECTORY_SEPARATOR.$lang;
      if (!is_dir($pathLang)) {
        \KeyClass\Error::i10nErrorRegister("Error trying to translate string %".$id."% - Directory not found: %".$pathLang."%", 'pack/sys');
      }

      // Para cada arquivo json encontrado no diretório, busca o ID
      // Arquivo de conteúdo
      $content = [];

      // Para cada arquivo
      foreach (\KeyClass\FileTree::dirTree($pathLang) as $file) {
        $contentFile = \KeyClass\JSON::getJSONDataFile($file);
        if ($contentFile !== false && $contentFile !== null) {
          $content = array_merge($contentFile, $content);
        }
      }

      if (count($content) > 0) {
        $content = array_change_key_case($content, CASE_LOWER);
      }

      // Se não encontrar a tradução da string
      if (!isset($content[$id])) {
        \KeyClass\Error::i10nErrorRegister("Error trying to translate string %".$id."% - String translation ID not found", 'pack/sys');
      }

      return $content[$id];
    }
}

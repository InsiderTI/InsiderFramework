<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

/**
 * Classe de tratamento de elementos na página
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
 */
class SgsPage
{
  /**
   * Atualiza dinamicamente o CSS da página quando,
   * por exemplo, um novo componente é criado e este deve
   * modificar o css da página
   *
   * @author Marcello Costa
   *
   * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
   *
   * @param string $css Código CSS (com tag style) a ser colocado na página
   *
   * @return void
   */
    public static function updateCssOfPage(string $css): void
    {
        $cssExploded = explode("\n", $css);

        // Novo css em uma única linha
        $newcss = "";

        // Contando número de linhas
        $countCss = count($cssExploded);

        // Para cada linha
        foreach ($cssExploded as $i => $cssline) {
            // Se ainda não chegou no final
            if ($i < $countCss - 1) {
                // Adiciona uma quebra de linha (para o javascript
                // funcionar corretamente)
                $newcss .= $cssline . "\n";
            } else {
                $newcss .= $cssline;
            }
        }

        // Inserindo css na variável global
        $injectedCss = \Modules\InsiderFramework\Core\KernelSpace::getVariable('injectedCss', 'sagacious');
        $injectedCss = $injectedCss . $newcss;
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'injectedCss' => $injectedCss
            ),
            'sagacious'
        );
    }

    /**
     * Atualiza dinamicamente o JS da página quando,
     * por exemplo, um novo componente é criado e este deve
     * modificar o js da página
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
     *
     * @param string $js Código JS a ser adicionado (com tag <script>);
     *
     * @return void
     */
    public static function updateJsOfPage(string $js): void
    {
        $injectedScripts = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'injectedScripts',
            'sagacious'
        );
        $injectedScripts = $injectedScripts . $js;
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
              'injectedScripts' => $injectedScripts
            ),
            'sagacious'
        );
    }

    /**
     * Atualiza dinamicamente o HTML da página quando,
     * por exemplo, um novo componente é criado e este deve
     * modificar o html da página
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
     *
     * @param string $html Código HTML a ser adicionado;
     *
     * @return void
     */
    public static function updateHtmlOfPage(string $html): void
    {
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array(
          'injectedHtml' => \Modules\InsiderFramework\Core\KernelSpace::getVariable(
              'injectedHtml',
              'sagacious'
          ) . $html
        ), 'sagacious');
    }

    /**
      * Função que minifica um código JS
      *
      * @author Marcello Costa
      *
      * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
      *
      * @param string $js Código JS a ser minificado
      *
      * @return string JS minificado
    */
    public static function jsMinify(string $js): string
    {
        // Remove comentários
        $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
        $js = preg_replace($pattern, '', $js);

        // Colocando tudo em uma linha
        $js = str_replace(array("\n", "\r"), '', $js);

        // Remove múltiplos espaços por apenas um
        $js = preg_replace('!\s+!', ' ', $js);

        return $js;
    }

    /**
     * Função que minifica um código CSS
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
     *
     * @param string $css Código CSS a ser minificado
     *
     * @return string CSS minificado
    */
    public static function cssMinify(string $css): string
    {
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
     * Função que traduz uma string baseado no ID
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsPage
     *
     * @param string $app  Nome do app onde está localizada a tradução
     * @param string $id   Id da string a ser traduzida
     * @param string $lang Linguagem para qual será traduzida a string
     *
     * @return string CSS minificado
   */
    public static function translateString(string $app, string $id, string $lang = LINGUAS): string
    {
        // Se não existe o diretório de tradução
        $pathLang = INSTALL_DIR . DIRECTORY_SEPARATOR . "apps" . DIRECTORY_SEPARATOR .
        $app . DIRECTORY_SEPARATOR . "i10n" . DIRECTORY_SEPARATOR . $lang;

        if (!is_dir($pathLang)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Error trying to translate string %" . $id . "%" .
                " - Directory not found: %" . $pathLang . "%",
                "app/sys"
            );
        }

        // Para cada arquivo json encontrado no diretório, busca o ID
        // Arquivo de conteúdo
        $content = [];

        // Para cada arquivo
        foreach (\Modules\InsiderFramework\Core\FileTree::dirTree($pathLang) as $file) {
            $contentFile = \Modules\InsiderFramework\Core\Json::getJSONDataFile($file);
            if ($contentFile !== false && $contentFile !== null) {
                $content = array_merge($contentFile, $content);
            }
        }

        if (count($content) > 0) {
            $content = array_change_key_case($content, CASE_LOWER);
        }

      // Se não encontrar a tradução da string
        if (!isset($content[$id])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Error trying to translate string %" . $id . "%" .
                " - String translation ID not found",
                "app/sys"
            );
        }

        return $content[$id];
    }
}

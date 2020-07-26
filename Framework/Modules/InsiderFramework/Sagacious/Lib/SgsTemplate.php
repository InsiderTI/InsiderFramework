<?php

namespace Modules\InsiderFramework\Sagacious\Lib;

use Modules\InsiderFramework\Sagacious\Lib\SgsView;
use Modules\InsiderFramework\Sagacious\Lib\SgsPage;
use Modules\InsiderFramework\Core\KernelSpace;
use Modules\InsiderFramework\Sagacious\Lib\SgsBags\SgsViewsBag;

/**
 * Sagacious template rendering class
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
 */
class SgsTemplate
{
    /** @var string SgsTemplate object app */
    protected $app = "";

    /** @var string Template file path */
    protected $templateFilename = "";

    /** @var object SgsView Object */
    protected $SgsView = "";

    /**
     * Construct function of class
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @return void
    */
    public function __construct()
    {
        $this->SgsView = new SgsView();
    }

    /**
     * Function that returns the name of the app
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @return string App name
     */
    public function getApp(): string
    {
        return $this->app;
    }

    /**
     * Function that returns the template file name
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @return string Template file name
     */
    public function getTemplateFilename(): string
    {
        return $this->templateFilename;
    }

    /**
     * Function that returns the template views object
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @return \Modules\InsiderFramework\Sagacious\Lib\SgsView Template views object
     */
    public function getSgsView(): SgsView
    {
        return $this->SgsView;
    }

    /**
     * Function that converts SGV file to PHP
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param \Modules\InsiderFramework\Sagacious\Lib\SgsView $SgsView View to be rendered
     *
     * @return array Código PHP
     */
    public function convertSGV2PHP(SgsView $SgsView): array
    {
        $this->SgsView = $SgsView;

        $viewconverted = $this->convertSGV2PHPAux($this->SgsView);
        
        // Inverting the order of detection of views and templates because
        // this order matters for other functions that will use
        // use this information
        if (count($viewconverted['templatesPath']) > 1) {
            $viewconverted['templatesPath'] = array_reverse($viewconverted['templatesPath']);
        }
        if (count($viewconverted['viewsPath']) > 1) {
            $viewconverted['viewsPath'] = array_reverse($viewconverted['viewsPath']);
        }

        $renderCode = $viewconverted['renderCode'];
        $viewComponents = $viewconverted['components'];

        $viewName = $this->getApp() . $this->SgsView->getViewFileName();
        $viewEncryptedName = md5($viewName);

        // For each component found, assemble an array containing the properties
        // that will be used in the cached file
        // Serializing detected components to be sent to the cached file
        if ($viewComponents !== null && !empty($viewComponents)) {
            $componentsIds = [];

            $viewComponentsInKernelSpace = \Modules\InsiderFramework\Core\Manipulation\KernelSpace::getVariable(
                'viewComponents',
                'sagacious'
            );
            if ($viewComponentsInKernelSpace === null) {
                $viewComponentsInKernelSpace = [];
            }

            foreach ($viewComponents as $component) {
                $app = $SgsView->getApp();

                $componentId = uniqid();
                $componentsIds[] = $componentId;

                $viewComponentsArray[$componentId] = array(
                    'id' => $component,
                    'app' => $app,
                    'viewName' => $viewName,
                    'viewEncryptedName' => $viewEncryptedName
                );
            }

            if (!empty($componentsIds)) {
                $componentsData = json_encode($viewComponentsArray);
                $declarationComponent =
                    "<?php
                        \\Modules\\InsiderFramework\\Sagacious\\Lib\\SgsView::InitializeViewCode('$componentsData');
                    ?>";

                // Placing this code at the beginning of the file
                $renderCode = $declarationComponent . "\n" . $renderCode;
            }
        }

        $viewconverted['renderCode'] = $renderCode;

        return $viewconverted;
    }

    /**
     * Function that removes comments from the view
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param string $noCommentsTemplateCode Code without comments
     *
     * @return void
     */
    public function removeComments(string &$noCommentsTemplateCode = null): void
    {
        if ($noCommentsTemplateCode !== null) {
            $pattern = '/{\*.*?\*}/si';
            $replacement = '';
            $noCommentsTemplateCode = preg_replace($pattern, $replacement, $noCommentsTemplateCode);
        }
    }

    /**
     * Function that converts a view's code to php
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param \Modules\InsiderFramework\Sagacious\Lib\SgsView $SgsView View to be converted to php
     *
     * @return array Returns an array of string containing html code and
     *               the components found
     */
    public function convertSGV2PHPAux(SgsView $SgsView): array
    {
        $componentsFound = array();
        $viewPath = INSTALL_DIR . DIRECTORY_SEPARATOR . $SgsView->getViewFilename();

        if (!file_exists($viewPath)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'Could not find a view %' . $SgsView->getViewFilename() . '%',
                "app/sys"
            );
        }
        $codeView = \Modules\InsiderFramework\Core\FileTree::fileReadContent(
            INSTALL_DIR . DIRECTORY_SEPARATOR . $SgsView->getViewFilename(),
            true
        );

        $countTemplates = 0;
        $templateCode = "";
        $templatesPath = [];

        $viewsPath = array(
            0 => $SgsView->getViewFilename()
        );

        $blocks = [];
        $endblocks = 0;

        $startjavascripts = 0;
        $endjavascripts = 0;
        $startcss = 0;
        $endcss = 0;

        // General regex for components, views, templates, etc.
        $dataGroup = ".*?";
        $generalPattern = "/" . "(?P<allMatch>\{(?P<declaration>[^\s]+)(?<data>" .
                        $dataGroup . ")[ ]*\})" . "/i";

        // Pattern for src
        $srcPattern = "/" . "(.*)src( *)?=( *)?['\"](?P<src>.*)['\"]" . "/i";

        /*
         * This function is splitted into two parts. The first part seeks component declarations
         * and treats them (if possible) or accounts for them. In the second part, the statements
         * that were not dealt with by the first party are processed.
        */

        ///////////////////////// PART-1 //////////////////////////////////////////////////////
        // Searching for views, models and blocks within the view code
        $codeView = preg_replace_callback(
            $generalPattern,
            function ($gM) use (
                $srcPattern,
                &$viewsPath,
                &$templatesPath,
                $codeView,
                &$templateCode,
                &$blocks,
                &$endblocks,
                &$views,
                &$componentsFound,
                $countTemplates,
                $SgsView,
                &$startjavascripts,
                &$endjavascripts,
                &$startcss,
                &$endcss
            ) {
                // Checking literal statements
                $literal = strpos($gM['allMatch'], ' literal ');
                if ($literal == false) {
                    $literal = strpos($gM['allMatch'], ' literal}');
                }

                if ($literal === false) {
                    // Account and convert (in some cases) the statements found by the regex
                    return $this->processDeclaration(
                        $gM,
                        $SgsView,
                        $codeView,
                        $componentsFound,
                        $blocks,
                        $endblocks,
                        $startcss,
                        $endcss,
                        $startjavascripts,
                        $endjavascripts,
                        $viewsPath,
                        $templatesPath,
                        $countTemplates,
                        $templateCode,
                        $srcPattern
                    );
                } else {
                    return $gM['allMatch'];
                }
            },
            $codeView
        );
        ///////////////////////// END-OF-PART-1 /////////////////////////////////////////////////////

        ///////////////////////////////////////////////////////////////////////////////////////////
        // So far the view code has been processed without taking into account the template and views
        // within views. From here on down the algorithm processes the remaining code.
        ///////////////////////////////////////////////////////////////////////////////////////////

        ///////////////////////// PART-2 /////////////////////////////////////////////////////////
        // If a template was found in the view
        if ($templateCode !== "") {
            // Temporarily removing blocks from the view and template declaration
            $tmpView = $codeView;
            $tmpTemplate = $templateCode;
            $regexTemplate = "/" . "\{( *)?template(.*?)\}" . "/i";
            $tmpView = preg_replace($regexTemplate, "", $tmpView, 1);

            // For each block previously found
            foreach ($blocks as $blockId => $data) {
                // Removing the declaration
                $regexBlock = "/" .
                              "{block id( {0,})?=( {0,})?['\"]" .
                              $blockId .
                              "['\"]([^}]+)?}(?P<blockContent>.*){\/block}" .
                              "/Uis";

                $tmpView = preg_replace($regexBlock, "", $tmpView, 1);
                $tmpTemplate = preg_replace($regexBlock, "", $tmpTemplate, 1);

                // Capture the corresponding content
                preg_match_all($regexBlock, $codeView, $blockMatches, PREG_SET_ORDER);
                if (is_array($blockMatches) && count($blockMatches) > 0) {
                    // Block content in the view
                    $contentBlock = $blockMatches[0]['blockContent'];

                    // Regex of blocks within the template
                    $regexBlockTemplate = "/" .
                                          "{block id=['\"]( {0,})?" .
                                          $blockId .
                                          "['\"][}](?P<blockContent>.*){\/block}" .
                                          "/Uis";

                    // Whether to preserve the contents of the block
                    if ($data['settings']['keepContent'] === true) {
                        // Retrieving likely block content
                        preg_match_all($regexBlockTemplate, $templateCode, $blockMatches, PREG_SET_ORDER);
                        if (isset($blockMatches[0]['blockContent'])) {
                            $contentBlock = $blockMatches[0]['blockContent'] . $contentBlock;
                        }
                    }

                    // Replacing the view codes in the template
                    $templateCode = preg_replace_callback($regexBlockTemplate, function ($bVM) use ($contentBlock) {
                        return $contentBlock;
                    }, $templateCode);
                } else {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        'The declaration of block %' . $blockId .
                        '% appears to be incomplete at %' . $SgsView->getViewFilename() . '%',
                        "app/sys"
                    );
                }
            }

            // Code out of blocks in the view
            if (trim($tmpView) !== "") {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'A code has been found outside of declaration of blocks at view %' .
                    $SgsView->getViewFilename() .
                    '%. Views with a declaration of template must be keep our codes inside blocks, ' .
                    'otherwise them will not be included on response',
                    "app/sys"
                );
            }
            unset($tmpView);

            $templateCode = preg_replace_callback(
                $generalPattern,
                function ($gMT) use ($srcPattern, &$templatesPath, &$viewsPath, &$componentsFound) {
                    // Checking literal statements
                    $literal = strpos($gMT['allMatch'], ' literal ');
                    if ($literal == false) {
                        $literal = strpos($gMT['allMatch'], ' literal}');
                    }

                    // If it is NOT a literal statement
                    if ($literal === false) {
                        switch ($gMT['declaration']) {
                            case 'template':
                                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                    'Declarations of templates inside template files are not accepted',
                                    "app/sys"
                                );
                                break;

                            case 'view':
                                preg_match_all(
                                    $srcPattern,
                                    strtolower($gMT['allMatch']),
                                    $vWSRCMatches,
                                    PREG_SET_ORDER
                                );
                                return $this->convertViewPathToCode(
                                    $vWSRCMatches,
                                    $componentsFound,
                                    $viewsPath,
                                    $templatesPath
                                );
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
                                
                                $injectedHtml = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                                    "injectedHtml",
                                    "insiderFrameworkSystem"
                                );
                                echo $injectedHtml; ?>';
                                break;

                            case 'injected_js':
                                return '<?php 
                                
                                $injectedScripts = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                                    "injectedScripts",
                                    "insiderFrameworkSystem"
                                );
                                echo $injectedScripts; ?>';
                                break;

                            case 'injected_css':
                                return '<?php 
                                
                                $injectedCss = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                                    "injectedCss",
                                    "insiderFrameworkSystem"
                                );
                                echo $injectedCss; ?>';
                                break;

                            case "viewsbag":
                                return $this->processViewsBagCode($gMT);
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
                },
                $templateCode
            );
        } else {
            // If a template was not found in the view but it references blocks
            if (count($blocks) > 0 || $endblocks > 0) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'The view %' .
                    $SgsView->getViewFilename() .
                    '% declares blocks inside its content, however no template was specified',
                    "app/sys"
                );
            }

            // If there is no template code, then it is the view code
            $templateCode = $codeView;
        }

        // Process CSS and JS block code
        $this->processJsCode($gMT, $templateCode);
        $this->processCssCOde($gMT, $templateCode);

        // Removing the literal word (once) from all matches
        $templateCode = preg_replace_callback(
            $generalPattern,
            function ($gMT) use ($srcPattern, &$templatesPath, &$viewsPath, &$componentsFound) {
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
                    case "viewsbag":
                        $regexLiteral = "/" . "(?P<part1>\{.*) (?P<literal>literal)(?P<part2>.*\})" . "/m";

                        $gMT['allMatch'] = preg_replace_callback(
                            $regexLiteral,
                            function ($gMT) use ($srcPattern, &$templatesPath, &$viewsPath, &$componentsFound) {
                                return $gMT['part1'] . $gMT['part2'];
                            },
                            $gMT['allMatch']
                        );

                        return $gMT['allMatch'];
                        break;

                    default:
                        return $gMT['allMatch'];
                        break;
                }
            },
            $templateCode
        );

        // Checking if the number of tags of the elements is right
        $this->checkOpenCloseTags(
            $blocks,
            $endblocks,
            $startcss,
            $endcss,
            $startjavascripts,
            $endjavascripts,
            $countTemplates,
            $SgsView
        );

        // Removing comments
        $this->removeComments($templateCode);
        ///////////////////////// END-OF-PART-2 /////////////////////////

        return array(
            'renderCode' => $templateCode,
            'components' => $componentsFound,
            'templatesPath' => $templatesPath,
            'viewsPath' => $viewsPath
        );
    }

    /**
     * Converts a declaration found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array  $gMT          Declaration Match Array
     * @param string $templateCode Template code + processed views
     *
     * @return void
     */
    private function processCssCode(array &$gMT = null, string $templateCode = null): void
    {
        if ($gMT !== null && $templateCode !== null) {
            // CSS
            $cssPattern = "/" . "(?P<allMatch>{css}(?P<cssContent>.*)?{\/css})" . "/Uis";
            $templateCode = preg_replace_callback($cssPattern, function ($gMT) use ($templateCode) {
                // Checking literal statements
                $literal = strpos($gMT['allMatch'], ' literal ');
                if ($literal == false) {
                    $literal = strpos($gMT['allMatch'], ' literal}');
                }

                // If it is NOT a literal statement
                if ($literal === false) {
                    $sgsPage = new SgsPage();
                    return $sgsPage->cssMinify($gMT['cssContent']);
                }

                return $gMT['allMatch'];
            }, $templateCode);
        }
    }

    /**
     * Converts a declaration found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array  $gMT          Declaration Match Array
     * @param string $templateCode Template code + processed views
     *
     * @return void
     */
    private function processJsCode(array &$gMT = null, string $templateCode = null): void
    {
        if ($gMT !== null && $templateCode !== null) {
            // Handling javascript and css declarations
            // JS
            $javascriptPattern = "/" . "(?P<allMatch>{javascript}(?P<jsContent>.*)?{\/javascript})" . "/Uis";
            $templateCode = preg_replace_callback($javascriptPattern, function ($gMT) use ($templateCode) {
                // Checking literal statements
                $literal = strpos($gMT['allMatch'], ' literal ');
                if ($literal == false) {
                    $literal = strpos($gMT['allMatch'], ' literal}');
                }

                // If it is NOT a literal statement
                if ($literal === false) {
                    $sgsPage = new SgsPage();
                    return $sgsPage->jsMinify($gMT['jsContent']);
                }

                return $gMT['allMatch'];
            }, $templateCode);
        }
    }

    /**
     * Checks the number of open / closed statements
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array  $blocks           Array of blocks found
     * @param int    $endblocks        Number of closed blocks
     * @param int    $startcss         Number of css declarations started
     * @param int    $endcss           Number of css declarations closed
     * @param int    $startjavascripts Number of js declarations started
     * @param int    $endjavascripts   Number of js statements already closed
     * @param int    $countTemplates   Number of template decals
     * @param object $SgsView          View object
     *
     * @return void
     */
    private function checkOpenCloseTags(
        array &$blocks,
        int &$endblocks,
        int &$startcss,
        int &$endcss,
        int &$startjavascripts,
        int &$endjavascripts,
        int &$countTemplates,
        &$SgsView
    ): void {
        // Whether templates were declared
        if ($countTemplates > 0) {
            // If no blocks were found in the view
            if (count($blocks) === 0) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'A template has been defined inside view %' .
                    $SgsView->getViewFilename() .
                    '% but no declarations of blocks has been found at template',
                    "app/sys"
                );
            }

            // If there is a discrepancy between the number of blocks declared and the number of block closings
            if (count($blocks) !== $endblocks) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'Error in declaration of blocks at view %' . $SgsView->getViewFilename() . '%',
                    "app/sys"
                );
            }
        }

        // If there is a discrepancy between the number of declared js blocks and the number of block closings
        if ($startjavascripts !== $endjavascripts) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'Error in declaration of javascripts at view %' .
                $SgsView->getViewFilename() .
                '%',
                "app/sys"
            );
        }

        // If there is a discrepancy between the number of declared css blocks and the number of block closings
        if ($startcss !== $endcss) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'Error in declaration of css at view %' .
                $SgsView->getViewFilename() .
                '%',
                "app/sys"
            );
        }
    }

    /**
     * Account and convert (in some cases) the statements found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array  $gM               Declaration Match Array
     * @param object $SgsView          View object
     * @param string $codeView         View code being processed
     * @param array  $componentsFound  Found component array
     * @param array  $blocks           Array of blocks found
     * @param int    $endblocks        Number of closed blocks
     * @param int    $startcss         Number of css declarations started
     * @param int    $endcss           Number of css declarations closed
     * @param int    $startjavascripts Number of js declarations started
     * @param int    $endjavascripts   Number of statements already closed
     * @param array  $viewsPath        Path of views
     * @param array  $templatesPath    Path of templates
     * @param int    $countTemplates   Template declaration counter
     * @param string $templateCode     Template code + processed views
     * @param string $srcPattern       Regex of elements with SRC
     *
     * @return string Converted declaration code
     */
    private function processDeclaration(
        array $gM,
        &$SgsView,
        string &$codeView,
        array &$componentsFound,
        array &$blocks,
        int &$endblocks,
        int &$startcss,
        int &$endcss,
        int &$startjavascripts,
        int &$endjavascripts,
        array &$viewsPath,
        array &$templatesPath,
        int &$countTemplates,
        string &$templateCode,
        string &$srcPattern
    ): string {
        switch ($gM['declaration']) {
            case 'template':
                return $this->processTemplateCode(
                    $SgsView,
                    $countTemplates,
                    $srcPattern,
                    $gM,
                    $codeView,
                    $templateCode,
                    $templatesPath
                );
                break;

            case 'view':
                return $this->processViewCode(
                    $srcPattern,
                    $gM,
                    $componentsFound,
                    $viewsPath,
                    $templatesPath
                );
                break;

            case 'block':
                return $this->processBlockCode($gM, $blocks);
                break;

            case '/block':
                $endblocks++;

                return $gM['allMatch'];
                break;

            case "component":
                return $this->processComponentCode($gM, $componentsFound);
                break;

            case "css":
                $startcss++;

                return $gM['allMatch'];
                break;

            case "/css":
                $endcss++;

                return $gM['allMatch'];
                break;

            case "javascript":
                $startjavascripts++;

                return $gM['allMatch'];
                break;

            case '/javascript':
                $endjavascripts++;

                return $gM['allMatch'];
                break;

            case "javascript_file":
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
    * Converts a declaration found by the regex
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
    *
    * @param array $gM Declaration Match Array
    *
    * @return string Converted declaration code
    */
    private function processViewsBagCode(array &$gM): string
    {
        $viewsbagPattern = "/" . "{viewsbag key=['\\\"](?P<value>.*)['\\\"]( {0,})?}" . "/";

        preg_match_all($viewsbagPattern, strtolower($gM['allMatch']), $viewsBagMatches, PREG_SET_ORDER);

        if (!isset($viewsBagMatches[0]['value'])) {
            return $gM['allMatch'];
        }

        $value = SgsViewsBag::get($viewsBagMatches[0]['value']);

        return $value ? $value : "";
    }

    /**
     * Converts a declaration found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array   $gM      Declaration Match Array
     * @param SgsView $SgsView View object
     *
     * @return string Converted declaration code
     */
    private function processI10nCode(array &$gM, &$SgsView): string
    {
        // Pattern to id
        $i10nPattern = "/" . "\{(.*)id( {0,})?=( {0,})?['|\"]" .
                       "(?P<id>[^'|\"]*)['|\"](( {1,})? ((lang)?" .
                       "( {0,})?=( {0,})?(['|\"](?P<lang>[^'|\"]*))))?" .
                       "( settings( {0,})?=( {0,})?['|\"](?P<settings>[^'|\"]*))?" . "/";

        preg_match_all($i10nPattern, strtolower($gM['allMatch']), $i10nIDMatches, PREG_SET_ORDER);

        // If the ID was found in the block
        if (is_array($i10nIDMatches) && count($i10nIDMatches) > 0) {
            if (trim($i10nIDMatches[0]['id']) !== "") {
                $id = $i10nIDMatches[0]['id'];

                // ID not found
                if ($id === "") {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        'The declaration of translation %' . $gM['allMatch'] .
                        '% don\'t have a valid declaration of ID (%' .
                        $SgsView->getViewFilename() . '%)',
                        "app/sys"
                    );
                }

                // Taking the translation language
                $lang = LINGUAS;
                if (isset($i10nIDMatches[0]['lang'])) {
                    $lang = $i10nIDMatches[0]['lang'];
                }
                $lang = strtolower($lang);

                // Variable that defines whether the string will be echoed or returned
                $componentCode = false;

                // Variable that informs that php tags should not be printed
                $stripphptags = false;

                if (isset($i10nIDMatches[0]['settings'])) {
                    $settings = explode(';', $i10nIDMatches[0]['settings']);

                    foreach ($settings as $setting) {
                        if (trim($setting) !== "") {
                            switch (strtolower($setting)) {
                                case 'raw':
                                    $componentCode = true;
                                    break;

                                case 'strip-php-tags':
                                    $stripphptags = true;
                                    break;
                            }
                        }
                    }
                }

                $cmd = "echo";
                if ($componentCode) {
                    $cmd = "return";
                }

                $app = $SgsView->getApp();

                if ($stripphptags !== false) {
                    $gM['allMatch'] = preg_replace(
                        "{" . $gM['allMatch'] . "}",
                        $cmd . " \\Sagacious\\SgsPage::translateString('$app', '$id', '$lang')",
                        $gM['allMatch']
                    );
                } else {
                    $gM['allMatch'] = preg_replace(
                        "{" . $gM['allMatch'] . "}",
                        "<?php " . $cmd . " \\Sagacious\\SgsPage::translateString('$app', '$id', '$lang'); ?>",
                        $gM['allMatch']
                    );
                }
            }
        }

        return $gM['allMatch'];
    }

    /**
     * Converts a declaration found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array $gM Declaration Match Array
     *
     * @return string Converted declaration code
     */
    private function processCssFileCode(array $gM): string
    {
        // Regex for css_file
        $regex = "/" . "{( {0,})?css_file src=['\"](?P<src>(.*))['\"]( {0,})?}" . "/";

        preg_match_all($regex, $gM[0], $matchescssfiles, PREG_SET_ORDER);
        if (count($matchescssfiles) === 0 || !isset($matchescssfiles[0]['src'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'A declaration of css_file has been found but it\'s not valid at %' .
                $gM['allMatch'] .
                '% (%' . $SgsView->getViewFilename() . '%)',
                "app/sys"
            );
        } else {
            $csspath = $matchescssfiles[0]['src'];

            $sgsPage = new SgsPage();

            if ($csspath[0] == "/") {
                $csspath = \Modules\InsiderFramework\Core\Manipulation\Text::extractString(
                    $csspath,
                    1,
                    strlen($csspath)
                );
            }

            $ext = pathinfo($csspath, PATHINFO_EXTENSION);
            if (strtolower($ext) !== "css") {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'The file specified at %' . $csspath . '% it\'s not compatible with the minification of css',
                    "app/sys"
                );
            }

            if (!(file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $csspath))) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'CSS file not found: %' . $csspath . '%',
                    "app/sys"
                );
            }

            $minnamecssfile = str_replace(".css", ".min.css", $csspath);
            if (!(file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamecssfile))) {
                $minifiedcsscontent = $sgsPage->cssMinify(
                    \Modules\InsiderFramework\Core\FileTree::fileReadContent(
                        INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $csspath
                    )
                );

                \Modules\InsiderFramework\Core\FileTree::fileWriteContent(
                    INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamecssfile,
                    $minifiedcsscontent
                );
            } else {
                $hashfile = md5_file(
                    INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $csspath
                );
                $hashfilemin = md5_file(
                    INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamecssfile
                );

                if ($hashfile !== $hashfilemin) {
                    \Modules\InsiderFramework\Core\FileTree::deleteFile(
                        INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamecssfile
                    );

                    $minifiedcsscontent = $sgsPage->cssMinify(
                        \Modules\InsiderFramework\Core\FileTree::fileReadContent(
                            INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $csspath
                        )
                    );

                    \Modules\InsiderFramework\Core\FileTree::fileWriteContent(
                        INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamecssfile,
                        $minifiedcsscontent
                    );
                }
            }

            return "<link href='" . REQUESTED_URL . "/" . $minnamecssfile . "' rel='stylesheet' type='text/css'>";
        }
    }

    /**
     * Converts a declaration found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array $gM Declaration Match Array
     *
     * @return string Converted declaration code
     */
    private function processJavaScriptFileCode(array $gM): string
    {
        // Pattern for javascript_file
        $regex = "/" . "{( {0,})?javascript_file src=['\"](?P<src>(.*))['\"]( {0,})?}" . "/";

        preg_match_all($regex, $gM[0], $matchesjsfiles, PREG_SET_ORDER);
        if (count($matchesjsfiles) === 0 || !isset($matchesjsfiles[0]['src'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'A declaration of js_file has been found but it\'s invalid at ' . $gM['allMatch'] .
                ' (' . $SgsView->getViewFilename() . ')',
                "app/sys"
            );
        } else {
            $jspath = $matchesjsfiles[0]['src'];

            $sgsPage = new SgsPage();

            if ($jspath[0] == "/") {
                $jspath = \Modules\InsiderFramework\Core\Manipulation\Text::extractString($jspath, 1, strlen($jspath));
            }

            $ext = pathinfo($jspath, PATHINFO_EXTENSION);
            if (strtolower($ext) !== "js") {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'The file specified %' . $jspath . '% it\'s not compatible with the minification of javascripts',
                    "app/sys"
                );
            }

            if (!(file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $jspath))) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'JS File not found: %' . $jspath . '%',
                    "app/sys"
                );
            }

            $minnamejsfile = str_replace(".js", ".min.js", $jspath);
            if (!(file_exists(INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamejsfile))) {
                $minifiedjscontent = $sgsPage->jsMinify(
                    \Modules\InsiderFramework\Core\FileTree::fileReadContent(
                        INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $jspath
                    )
                );
                \Modules\InsiderFramework\Core\FileTree::fileWriteContent(
                    INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamejsfile,
                    $minifiedjscontent
                );
            } else {
                $hashfile = md5_file(INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $jspath);
                $hashfilemin = md5_file(
                    INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamejsfile
                );

                if ($hashfile !== $hashfilemin) {
                    \Modules\InsiderFramework\Core\FileTree::deleteFile(
                        INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamejsfile
                    );
                    $minifiedjscontent = $sgsPage->jsMinify(
                        \Modules\InsiderFramework\Core\FileTree::fileReadContent(
                            INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $jspath
                        )
                    );
                    \Modules\InsiderFramework\Core\FileTree::fileWriteContent(
                        INSTALL_DIR . DIRECTORY_SEPARATOR . "Web" . DIRECTORY_SEPARATOR . $minnamejsfile,
                        $minifiedjscontent
                    );
                }
            }

            return "<script type='text/javascript' src='" . REQUESTED_URL . "/" . $minnamejsfile . "'></script>";
        }
    }

    /**
     * Converts a declaration found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array $gM              Declaration Match Array
     * @param array $componentsFound Found component array
     *
     * @return string Converted component code
     */
    private function processComponentCode(array &$gM, array &$componentsFound): string
    {
        // Pattern for components
        $componentPattern = "/" . "\{(.*)id( {0,})?=( {0,})?['|\"]" .
        "(?P<id>[^'|\"]*)['|\"](( {1,})? ((settings)?( {0,})?=( {0,})?(['|\"]" .
        "(?P<settings>.+?(?=['|\"]))))['|\"]( {0,})?\})?" . "/";

        preg_match_all($componentPattern, strtolower($gM['allMatch']), $blIDMatches, PREG_SET_ORDER);

        // If the ID was found in the block
        if (is_array($blIDMatches) && count($blIDMatches) > 0) {
            if (trim($blIDMatches[0]['id']) !== "") {
                // Retrieving the component name
                $id = $blIDMatches[0]['id'];

                // ID not found
                if ($id === "") {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        'The component %' . $gM['allMatch'] .
                        '% don\'t have a valid declaration of ID (%' .
                        $SgsView->getViewFilename() .
                        '%)',
                        "app/sys"
                    );
                }

                $componentsFound[] = $id;
                $componentCode = false;
                $stripphptags = false;

                if (isset($blIDMatches[0]['settings'])) {
                    $settings = explode(';', $blIDMatches[0]['settings']);

                    foreach ($settings as $setting) {
                        if (trim($setting) !== "") {
                            switch (strtolower($setting)) {
                                case 'raw':
                                    $componentCode = true;
                                    break;

                                case 'strip-php-tags':
                                    $stripphptags = true;
                                    break;
                            }
                        }
                    }
                }

                if ($componentCode) {
                    $typeFunction = "rawComponent";
                } else {
                    $typeFunction = "renderComponent";
                }

                if ($stripphptags !== false) {
                    $gM['allMatch'] = preg_replace(
                        "{" . $gM['allMatch'] . "}",
                        "\\Modules\\InsiderFramework\\Sagacious\\Lib\\SgsView::executeComponentFunction('" .
                            $id . "', '" . $typeFunction . "'" .
                        ");",
                        $gM['allMatch']
                    );
                } else {
                    $gM['allMatch'] = preg_replace(
                        "{" . $gM['allMatch'] . "}",
                        "<?php " .
                            "\\Modules\\InsiderFramework\\Sagacious\\Lib\\SgsView::executeComponentFunction('" .
                                $id . "', '" . $typeFunction . "'" .
                            ");" .
                        " ?>",
                        $gM['allMatch']
                    );
                }

                return $gM['allMatch'];
            }
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'A declaration of component has been found but there is not a ID specified at %' .
                $gM['allMatch'] .
                '% (%' .
                $SgsView->getViewFilename() .
                '%)',
                "app/sys"
            );
        }
    }

    /**
     * Converts a declaration found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array $gM     Declaration Match Array
     * @param array $blocks Array of blocks found
     *
     * @return string Converted block code
     */
    private function processBlockCode(array &$gM, array &$blocks): string
    {
        $viewFileName = "Unknow view";
        if ($this->SgsView !== null) {
            $viewFileName = $this->SgsView->getViewFilename();
        }

        // Pattern for id
        $id_pattern = "/" . "\{(.*)id( {0,})?=( {0,})?['|\"](?P<id>[^'|\"]*)" . "/i";

        preg_match_all($id_pattern, strtolower($gM['allMatch']), $blIDMatches, PREG_SET_ORDER);

        // Pattern for an entire block with settings
        $blockWithSettingsPattern = "/" . "\{(.*)id( {0,})?=( {0,})?['|\"]" .
        "(?P<id>.*)['|\"]( {0,})" .
        "((settings)?( {0,})?=( {0,})?(['|\"]" .
        "(?P<settings>.+?(?=['|\"]))))['|\"]( {0,})?\}" . "/i";

        if (is_array($blIDMatches) && count($blIDMatches) > 0) {
            if (trim($blIDMatches[0]['id']) !== "") {
                if (!isset($blocks[$blIDMatches[0]['id']])) {
                    preg_match_all(
                        $blockWithSettingsPattern,
                        strtolower($gM['allMatch']),
                        $blSettingsMatches,
                        PREG_SET_ORDER
                    );

                    $keepContent = false;

                    if (is_array($blSettingsMatches) && count($blSettingsMatches) > 0) {
                        $settings = explode(';', $blSettingsMatches[0]['settings']);

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

                    $blocks[$blIDMatches[0]['id']]['settings']['keepContent'] = $keepContent;

                    return $gM['allMatch'];
                } else {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        'A declaration of block has been found but the " .
                        "specified ID has been already declared before at %' .
                        $gM['allMatch'] .
                        '% (%' .
                        $viewFileName .
                        '%)',
                        "app/sys"
                    );
                }
            } else {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'A declaration of block has been found but the specified ID is invalid at ' .
                    $gM['allMatch'] .
                    ' (%' . $viewFileName . '%)',
                    "app/sys"
                );
            }
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'A declaration of block has been found but there is not an ID specified at %' .
                $gM['allMatch'] .
                '% (%' . $viewFileName . '%)',
                "app/sys"
            );
        }
    }

    /**
     * Converts a declaration found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param string $srcPattern      Regex pattern
     * @param array  $gM              Declaration Match Array
     * @param array  $componentsFound Found component array
     * @param array  $viewsPath       Array of views found
     * @param array  $templatesPath   Paths where templates are located
     *
     * @return string Converted view ID
     */
    private function processViewCode(
        string &$srcPattern,
        array &$gM,
        array &$componentsFound,
        array &$viewsPath,
        array &$templatesPath
    ): string {
        //preg_match_all($srcPattern, strtolower($gM['allMatch']), $vWSRCMatches, PREG_SET_ORDER);
        preg_match_all($srcPattern, $gM['allMatch'], $vWSRCMatches, PREG_SET_ORDER);

        if (is_array($vWSRCMatches) && count($vWSRCMatches) > 0) {
            if (trim($vWSRCMatches[0]['src']) !== "") {
                return $this->convertViewPathToCode($vWSRCMatches, $componentsFound, $viewsPath, $templatesPath);
            } else {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'A declaration of view has been found but the specified SRC is invalid at %' .
                    $gM['allMatch'] .
                    '% (%' .
                    $SgsView->getViewFilename() .
                    '%)',
                    "app/sys"
                );
            }
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'A declaration of view has been found but there is not an SRC specified at %' .
                $gM['allMatch'] .
                '% (%' .
                $SgsView->getViewFilename() .
                '%)',
                "app/sys"
            );
        }
    }

    /**
     * Converts a declaration found by the regex
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param object $SgsView        View object
     * @param int    $countTemplates Template declaration counter
     * @param string $srcPattern     Regex pattern
     * @param array  $gM             Declaration Match Array
     * @param string $codeView       View code being processed
     * @param string $templateCode   Post processing template code
     * @param array  $templatesPath  Templates that were detected
     *
     * @return string Converted template code
     */
    private function processTemplateCode(
        $SgsView,
        int &$countTemplates,
        string &$srcPattern,
        array &$gM,
        string &$codeView,
        string &$templateCode,
        array &$templatesPath
    ): string {
        if ($countTemplates == 0) {
            $countTemplates++;

            preg_match_all($srcPattern, $gM['allMatch'], $tpSRCMatches, PREG_SET_ORDER);
            list($before) = str_split(preg_replace('/\s+/', '', $codeView), 9);

            if ($before !== '{template') {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'The declaration of template must be written on the first line of the file at view (%' .
                    $SgsView->getViewFilename() .
                    '%)',
                    "app/sys"
                );
            }

            if (is_array($tpSRCMatches) && count($tpSRCMatches) > 0) {
                if (trim($tpSRCMatches[0]['src']) !== "") {
                    if (strpos($tpSRCMatches[0]['src'], "::") === false) {
                        $dataSRC = explode(DIRECTORY_SEPARATOR, $tpSRCMatches[0]['src']);
                        if (count($dataSRC) < 3) {
                            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                                'The declaration of template appears to be incomplete at %' .
                                $gM['allMatch'] .
                                '% (%' .
                                $SgsView->getViewFilename() .
                                '%)',
                                "app/sys"
                            );
                        }

                        // 0 = app
                        // 1 = templates directory
                        // 2> = rest of the path
                        $file = "Apps" . DIRECTORY_SEPARATOR .
                                $dataSRC[0] . DIRECTORY_SEPARATOR .
                                "Templates" . DIRECTORY_SEPARATOR .
                                $dataSRC[2] . ".sgv";
                    } else {
                        $dataSRC = explode('::', $tpSRCMatches[0]['src']);

                        // 0 = app
                        // 1> = rest of the path (no templates)
                        $file = "Apps" . DIRECTORY_SEPARATOR .
                                $dataSRC[0] . DIRECTORY_SEPARATOR .
                                "Templates" . DIRECTORY_SEPARATOR .
                                $dataSRC[1] . ".sgv";
                    }

                    $templatesPath[] = $file;

                    $templatesPath[] = INSTALL_DIR . DIRECTORY_SEPARATOR . $file;

                    $templateCode = \Modules\InsiderFramework\Core\FileTree::fileReadContent(
                        INSTALL_DIR . DIRECTORY_SEPARATOR . $file,
                        true
                    );

                    return $gM['allMatch'];
                } else {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        'A declaration of template has been found but the specified SRC is invalid at %' .
                        $gM['allMatch'] .
                        '% (%' . $SgsView->getViewFilename() .
                        '%)',
                        "app/sys"
                    );
                }
            } else {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'A declaration of template has been found but there is not a SRC specified at %' .
                    $gM['allMatch'] .
                    '% (%' .
                    $SgsView->getViewFilename() .
                    '%)',
                    "app/sys"
                );
            }
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'More than one template has been declared at view %' .
                $SgsView->getViewFilename() .
                '%',
                "app/sys"
            );
        }
    }

    /**
     * Converts a declaration found by the regex into HTML code
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Sagacious\Lib\SgsTemplate
     *
     * @param array $matches         Matches according to class functions
     * @param array $componentsFound Found component array
     * @param array $viewsPath       Array of views found
     * @param array $templatesPath   Array de templates encontradas
     *
     * @return string View code converted to HTML
     */
    private function convertViewPathToCode(
        array $matches,
        array &$componentsFound,
        array &$viewsPath,
        array &$templatesPath
    ): string {
        if (is_array($matches) && count($matches) > 0) {
            if (trim($matches[0]['src']) !== "") {
                $insideSgsView = new SgsView();

                if (strpos($matches[0]['src'], "::") === false) {
                    $dataSRC = explode(DIRECTORY_SEPARATOR, $matches[0]['src']);
                    if (count($dataSRC) < 3) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                            'The declaration of view appears to be incomplete at %' .
                            $gM['allMatch'] .
                            '% (%' . $SgsView->getViewFilename() . '%)',
                            "app/sys"
                        );
                    }

                    // 0 = app
                    // 1 = views directory
                    // 2> = rest of the path
                    $file = join(DIRECTORY_SEPARATOR, array_slice($dataSRC, 2, count($dataSRC) - 2)) . ".sgv";
                    $insideSgsView->setViewFilename($file, $dataSRC[0]);
                } else {
                    $insideSgsView->setViewFilename($matches[0]['src'] . ".sgv");
                }

                $viewconverted = $this->convertSGV2PHPAux($insideSgsView);

                $componentsFound = array_merge($componentsFound, $viewconverted['components']);

                $templatesPath = array_merge($viewconverted['templatesPath'], $templatesPath);
                $viewsPath = array_merge($viewconverted['viewsPath'], $viewsPath);

                return $viewconverted['renderCode'];
            } else {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    'A declaration of view as been found but the SRC specified is invalid at %' .
                    $gM['allMatch'] .
                    '% (%' . $SgsView->getViewFilename() .
                    '%)',
                    "app/sys"
                );
            }
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'A declaration of view as been found but there is not a SRC specified at %' .
                $gM['allMatch'] .
                '% (%' .
                $SgsView->getViewFilename() .
                '%)',
                "app/sys"
            );
        }
    }
}

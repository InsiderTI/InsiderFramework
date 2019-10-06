<?php
/**
  Arquivo KeyClass\I10n
*/

// Namespace das KeyClass
namespace KeyClass;

/**
  KeyClass de tradução

  @package KeyClass\I10n
  
  @author Marcello Costa
*/
class I10n{
    /**
        Função que busca uma string para ser traduzida

        @author Marcello Costa

        @package KeyClass\I10n


        @param  string  $stringToTranslate  String a ser traduzida
        @param  string  $domain             Domínio ao qual a tradução pertence
        @param  string  $linguas            Idioma para o qual será traduzido

        @return string  String traduzida
    */
    public static function getTranslate($stringToTranslate, $domain, $linguas=LINGUAS) : string {
        global $kernelspace;
        $i10n = $kernelspace->getVariable('i10n', 'insiderFrameworkSystem');
        
        if ($i10n === null){
            primaryError('$10n variable not initialized');
        }

        // If not exists translation for the message
        if (!isset($i10n[$domain]) || !isset($i10n[$domain][$linguas])) {
            return str_replace("%", "", $stringToTranslate);
        }

        $regex = '/(?P<matches>(%.*?%))/';

        $tmpString = preg_replace_callback($regex, function ($gMT) use ($linguas, $domain, $i10n, $stringToTranslate) {
            return 'REPLACE_STRING';
        }, $stringToTranslate);

        $shortest = 99999;
        foreach ($i10n[$domain][$linguas] as $original => $translate) {
            $lev = levenshtein($tmpString, $original);
            
            if ($lev <= $shortest || $shortest < 0) {
                // Set the closest match, and shortest distance
                $closest  = $translate;
                $shortest = $lev;
            }
        }

        preg_replace_callback($regex, function ($gMT) use (&$closest, $regex, $linguas, $domain, $i10n, $stringToTranslate) {
            $matchesString = [];
            preg_match_all($regex, $stringToTranslate, $matchesString, PREG_SET_ORDER);

            $matchesClosest = [];
            preg_match_all($regex, $closest, $matchesClosest, PREG_SET_ORDER);
            
            // Para cada "match" (string a ser substituída)
            foreach ($matchesString as $mS){
                $newString = str_replace('%','',$mS['matches']);
                
                $closest = str_replace($matchesClosest[0]['matches'], $newString, $closest);                
            }
        }, $stringToTranslate);

        return $closest;
    }
    
    /**
        Função que carrega um arquivo de tradução

        @author Marcello Costa

        @package KeyClass\I10n


        @param  string  $domain    Domínio ao qual a tradução pertence
        @param  string  $filePath  Caminho do arquivo de tradução

        @return void  Without return
    */
    public static function loadi10nFile($domain, $filePath) : void {
        global $kernelspace;
        if ($kernelspace !== null){
            $i10n = $kernelspace->getVariable('i10n', 'insiderFrameworkSystem');
        }
        else{
            global $i10n;
            if ($i10n === null){
                $i10n=[];
            }
        }

        // If file not exists
        if (!file_exists($filePath)){
            \KeyClass\Error::errorRegister("Cannot load file %".$filePath."%", "LOG");
        }
        
        // Idioma da tradução
        $language=basename(dirname(strtolower($filePath)));

        // Lendo o arquivo de tradução
        $i10nData = \KeyClass\JSON::getJSONDataFile($filePath);
        
        if (!$i10nData) {
            \KeyClass\Error::errorRegister("Cannot load file contents %".$filePath."%", "LOG");
        }

        else{
            // Inserindo os dados no domínio de traduções
            if (!isset($i10n[$domain])){
                $i10n[$domain]=[];
            }
            if (!isset($i10n[$domain][$language])){
                $i10n[$domain][$language]=[];
            }

            $i10n[$domain][$language] = array_merge($i10n[$domain][$language], $i10nData);
        }
        
        if ($kernelspace !== null){
            $kernelspace->setVariable(array('i10n' => $i10n), 'insiderFrameworkSystem');
        }
    }
}

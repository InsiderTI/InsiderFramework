<?php

/**
    Este arquivo carrega as KeyClasses , interfaces e etc do ambiente
    dinamicamente
 
    @author Marcello Costa
    @package Core
*/

/**
    Inicializa as classes, interfaces e etc do ambiente
    
    @author Marcello Costa
    @package Core

    @param  string  $soughtitem   Nome do item requisitado
 
    @return void
 */
spl_autoload_register(function($soughtitem) : void {
    $namespaceFirstClass = explode("\\",$soughtitem);
    
    $firstClass="";
    if (is_array($namespaceFirstClass) && count($namespaceFirstClass) > 0){
        $firstClass=$namespaceFirstClass[0];
    }

    switch (strtolower($firstClass)){
        case "keyclass":
            // Trata o nome da classe para que seja encontrado o nome do arquivo
            $soughtclassAux=str_replace("KeyClass\\", "",$soughtitem);
            $soughtclassAux=str_replace("", "",$soughtclassAux);

            // Nome do arquivo php
            $basename = str_replace('\\', DIRECTORY_SEPARATOR, (str_replace("keyclass_","",strtolower($soughtclassAux))));

            // Montando o caminho de requisição da classe
            $file=strtolower('frame_src'.DIRECTORY_SEPARATOR.'keyclasses'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$basename.".php");

            // Requere o arquivo php da classe
            requireKeyFile($file, $soughtitem);
        break;
        case "modules":
            // Trata o nome da classe para que seja encontrado o nome do arquivo
            $soughtclassAux=str_replace("Modules\\", "",$soughtitem);
            $soughtclassAux=str_replace("", "",$soughtclassAux);
            $soughtclassAux=str_replace("\\\\", "\\",$soughtclassAux);
            $soughtclassAux = str_replace('\\', DIRECTORY_SEPARATOR, $soughtclassAux);

            // Montando o caminho de requisição da classe
            $file=strtolower('frame_src'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$soughtclassAux.".php");

            // Requere o arquivo php da classe
            requireKeyFile($file, $soughtitem);
        break;
        default:
        break;
    }
});

/**
    Executa o método require num arquivo php que contém o item requisitado
 
    @author Marcello Costa
    @package Core
 
    @param  string  $file          Arquivo php requisitado
    @param  string  $soughtitem    Item requisitado
 
    @return void
 */
function requireKeyFile(string $file, string $soughtitem) : void {
    // Se não foi especificado o nome do arquivo
    if ($file == "") {
        primaryError("The variable file was not specified for the item: ".$soughtitem." !");
    }

    // Classe de erro
    require_once('frame_src'.DIRECTORY_SEPARATOR.'keyclasses'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'error.php');

    // Se o arquivo existir
    $filepath = INSTALL_DIR.DIRECTORY_SEPARATOR.$file;
    if (file_exists($filepath) &&
        is_readable($filepath)) {
        // Requisita o arquivo
        require_once $filepath;

        // Se mesmo assim o item não existir, gera um erro
        if (!(class_exists($soughtitem))) {
            KeyClass\Error::classNotFound($soughtitem, $filepath);
        }
    }

    // Se o arquivo não existir, gera um erro
    else {
        if (!(isset($namespace))) {
            $namespace=null;
        }

        KeyClass\Error::classFileNotFound($filepath, $soughtitem, $namespace);
    }
}

<?php
    /**
      Arquivo que carrega as configurações do sagacious e suas classes
    */

    // Carregando configurações
    require_once 'config_sagacious.php';

    // Mapeando as classes
    $path='frame_src'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'Sagacious';
    $classes=\KeyClass\FileTree::dirTree($path.DIRECTORY_SEPARATOR.'classes');
    unset($path);

    // Iniciando as classes do Sagacious
    foreach ($classes as $class) {
        require_once $class;
    }
    unset($classes);
    if (isset($class)){
        unset($class);
    }
    
    // Variável global de componentes
    global $kernelspace;

    // Criando o componentsBag como um objeto
    $kernelspace->setVariable(array('componentsBag' => new \Sagacious\SgsComponentsBag()), 'insiderFrameworkSystem');
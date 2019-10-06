<?php
/**
  Este é um arquivo que contém as funções que podem ser chamadas diretamente
  por views e templates.
  
  @package Sagacious
  @author Marcello Costa
*/

    /**
        Recupera o valor de um item armazenado na viewbag

        @author Marcello Costa

        @package Sagacious

        @param  string  $key    Chave do que está sendo pesquisado

        @return  mixed  Valor recuperado
    */
    function getValueFromViewBag(string $key) {
        global $kernelspace;
        $viewBag = $kernelspace->getVariable('viewBag', 'insiderFrameworkSystem');
        if (is_array($viewBag) && isset($viewBag[$key])) {
            return $viewBag[$key];
        }
    }
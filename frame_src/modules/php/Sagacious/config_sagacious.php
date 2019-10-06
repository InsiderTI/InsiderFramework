<?php
/**
  Configuração do Sagacious
*/

// CACHE
/** @var bool  Ativar/desativar o cache do sagacious */
global $kernelspace;
$kernelspace->setVariable(array('SagaciousCacheStatus' => true), 'sagacious');

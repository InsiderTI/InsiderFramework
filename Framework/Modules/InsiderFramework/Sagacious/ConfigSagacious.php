<?php

/**
 *  Sagacious configuration loader
 */

$configSagacious = \Modules\InsiderFramework\Core\Loaders\ConfigLoader::getConfigFileData('sagacious');
if (count($configSagacious) === 0 || !isset($configSagacious['SagaciousCacheStatus'])) {
    \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
        "Unable to load Sagacious configuration file"
    );
}

\Modules\InsiderFramework\Core\KernelSpace::setVariable(
    array(
      // CACHE
      /** @var bool  Enable / disable Sagacious cache */
      'SagaciousCacheStatus' => (bool)$configSagacious['SagaciousCacheStatus']
    ),
    'sagacious'
);

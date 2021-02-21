<?php
namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;
class RepositoriesList {
  /**
    * Initialize the repositories data
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\RepositoriesList
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
  public static function load(array &$coreData): void {
        if (
            !isset($coreData['REPOSITORIES']) ||
            !is_array($coreData['REPOSITORIES']) ||
            empty($coreData['REPOSITORIES'])
        ) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'REPOSITORIES'"
            );
        }

        $localRepositories = [];
        $remoteRepositories = [];
        foreach ($coreData['REPOSITORIES'] as $currentRepository) {
            RepositoriesList::addLocalOrRemoteRepository($currentRepository, $localRepositories, $remoteRepositories);
        }

        $rK = array_keys($remoteRepositories);
        $lK = array_keys($localRepositories);

        $final = array_merge($rK, $lK);
        $finalUnique = array_unique($final);

        if (count($final) !== count($finalUnique)) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "Duplicated domains has been founded on configuration " .
                "REPOSITORIES and DOMAIN. Please, review the configuration files"
            );
        }
        unset($rK);
        unset($lK);
        unset($final);
        unset($finalUnique);

        /**
         * Local repositories
         *
         * @package Core
         */
        define('LOCAL_REPOSITORIES', $localRepositories);
        unset($localRepositories);

        /**
         * Remote repositories
         *
         * @package Core
         */
        define('REMOTE_REPOSITORIES', $remoteRepositories);
    }

   /**
    * Define data of array repositories (local repositories or remote repositories list)
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\RepositoriesList
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
    protected static function addLocalOrRemoteRepository(
      array $currentRepository,
      array &$localRepositories,
      array &$remoteRepositories
    ): void {
      if (!isset($currentRepository['DOMAIN'])) {
          \Modules\Insiderframework\Core\Error::primaryError(
              "The following information was not found in the repositories configuration: 'DOMAIN'"
          );
      }
      if (!isset($currentRepository['TYPE'])) {
          \Modules\Insiderframework\Core\Error::primaryError(
              "The following information was not found in the repositories configuration: 'TYPE'"
          );
      }
      switch (strtoupper(trim($currentRepository['TYPE']))) {
          case 'REMOTE':
              if (isset($remoteRepositories[$currentRepository['DOMAIN']])) {
                  \Modules\Insiderframework\Core\Error::primaryError(
                      "Duplicated entry for repository: " . $currentRepository['DOMAIN']
                  );
              }
              $remoteRepositories[$currentRepository['DOMAIN']] = $currentRepository;
              break;

          case 'LOCAL':
              if (isset($localRepositories[$currentRepository['DOMAIN']])) {
                  \Modules\Insiderframework\Core\Error::primaryError(
                      "Duplicated entry for repository: " . $currentRepository['DOMAIN']
                  );
              }
              $localRepositories[$currentRepository['DOMAIN']] = $currentRepository;
              break;
          default:
              \Modules\Insiderframework\Core\Error::primaryError(
                  "Unknown type for repository: " . $currentRepository['TYPE']
              );
              break;
      }
    }
}
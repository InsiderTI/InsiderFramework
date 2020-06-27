<?php

namespace Modules\InsiderFramework\PureSql;

/**
 * Trait that contains functions to manage the database
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\PureSql\Core
 */
trait Core
{
    /**
     * Creates filters to be used in the searches with PureSql
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\PureSql\Core
     *
     * @param array  $searchData Data to be searched
     * @param string $filter     String that will be the filter
     *
     * @return void
     */
    public function makeFiltersSelect(array &$searchData, string &$filter): void
    {
        if (is_array($searchData)) {
            $filter = implode(
                ' and ',
                array_map(
                    function ($item) {
                        return $item . " = :" . $item;
                    },
                    array_keys($searchData)
                )
            );

            $filter = " where " . $filter;
        }
    }

    /**
     * Searches one only result. If multiple rows was found, returns an empty array.
     * If not was found, returns an empty array.
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\PureSql\Core
     *
     * @param string $select     Select query (without where)
     * @param array  $searchData Data to be searched
     *
     * @return array Array with a single result
     */
    private function getOneOrNone(string $select, array $searchData): array
    {
        $filter = "";
        $this->makeFiltersSelect($searchData, $filter);

        $query = $select . " " . $filter;

        $resultquery = $this->select($query, $searchData, false);

        if (empty($resultquery)) {
            return [];
        }
        if (count($resultquery) > 1) {
            return [];
        }
        return $resultquery[0];
    }

    /**
     * Searches data by PK
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\PureSql\Core
     *
     * @param string $table Target table
     * @param int    $id    Id to be searched
     *
     * @return array Data result array
     */
    private function find(string $table, int $id): array
    {
        $pkQuery = $this->select("SHOW COLUMNS FROM " . $table, true);

        $pkColumn = null;
        foreach ($pkQuery as $column) {
            if ($column['Key'] === 'PRI') {
                $pkColumn = $column['Field'];
            }
        }
        if ($pkColumn === null) {
            return [];
        }

        $query = "select * from `" . $table . "` where " . $pkColumn . " = :id";
        $resultquery = $this->select($query, array('id' => $id), false);

        return $resultquery;
    }

    /**
     * Searches rows under some conditions
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\PureSql\Core
     *
     * @param string $table      Target table
     * @param array  $searchData Data to be searched
     *
     * @return array Data result array
     */
    private function findBy(string $table, array $searchData): array
    {
        $filter = "";
        $this->makeFiltersSelect($searchData, $filter);

        $query = "select * from " . $table . " " . $filter;

        $resultquery = $this->select($query, $searchData, false);

        return $resultquery;
    }

    /**
     * Updates one or more rows under some condition
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\PureSql\Core
     *
     * @param string $table      Target table
     * @param array  $searchData Data to be searched
     * @param array  $newData    New data array
     *
     * @return int Number of affected rows
     */
    private function updateBy(string $table, array $searchData, array $newData): int
    {
        if (trim($table) === "") {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'Table name cannot by empty on update',
                "app/sys"
            );
        }

        if (count($newData) === 0) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'New data not specified on updateBy with table %' . $table . '%',
                "app/sys"
            );
        }

        $updateColumns = implode(
            ', ',
            array_map(
                function ($item) {
                    return $item . " = :" . $item;
                },
                array_keys($newData)
            )
        );

        $filter = "";

        if (count($searchData) === 0) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'Search data not specified on updateBy with table %' . $table . '%',
                "app/sys"
            );
        }

        $this->makeFiltersSelect($searchData, $filter);

        $query = "UPDATE `" . $table . "` SET "
            . $updateColumns
            . $filter
            . ";";

        $bindArray = array_merge($searchData, $newData);

        $result = $this->execute($query, $bindArray);

        if (!is_numeric($result)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                'Error on execute update query %' . $query . '%: ' .
                '%' . \Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($result) . '%',
                __FILE__,
                __LINE__
            );
        }

        return $result;
    }

    /**
     * Inserts one or more rows in some table
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\PureSql\Core
     *
     * @param string $table   Target table
     * @param array  $newData New data array
     *
     * @return int Number of affected rows
     */
    private function insert(string $table, array $newData): int
    {
        if (trim($table) === "") {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'Table name cannot by empty on insert',
                "app/sys"
            );
        }

        if (count($newData) === 0) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                'New data not specified on insert with table %' . $table . '%',
                "app/sys"
            );
        }

        $insertColumns = implode(
            ', ',
            array_map(
                function ($item) {
                    return $item . " = :" . $item;
                },
                array_keys($newData)
            )
        );

        $query = "INSERT INTO `" . $table . "` SET "
            . $insertColumns
            . ";";

        $bindArray = $newData;

        $result = $this->execute($query, $bindArray);

        if (!is_numeric($result)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                'Error on execute insert query %' . $query . '%: ' .
                '%' . \Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($result) . '%',
                __FILE__,
                __LINE__
            );
        }

        return $result;
    }
}

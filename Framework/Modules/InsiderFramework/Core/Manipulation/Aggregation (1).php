<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for handle aggregations (arrays and objects)
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Aggregation
 */
trait Aggregation
{
    /**
    * Change all keys of array to lowercase
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Manipulation\Aggregation
    *
    * @param array $array Array to be modified
    *
    * @return void
    */
    public static function changeKeysToLowerCaseArray(array &$array): void
    {
        $map = array();

        foreach ($array as $key => $value) {
            $map[strtolower($key)] = $value;
        }

        $array = $map;
    }

    /**
    * Change all keys of array to uppercase
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Manipulation\Aggregation
    *
    * @param array $array Array to be modified
    *
    * @return void
    */
    public static function changeKeysToUpperCaseArray(array $array): void
    {
        $map = array();

        foreach ($array as $key => $value) {
            $map[strtoupper($key)] = $value;
        }

        $array = $map;
    }

    /**
     * Returns the first element of array
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Aggregation
     *
     * @param array $array Target array
     *
     * @return mixed|null First elemento of array or null
     */
    public static function firstArrayItem(array &$array)
    {
        // If array is empty
        if (count($array) === 0) {
            return null;
        }

        // Resetting the pointer of array
        reset($array);

        // Returning the first element of array
        return $array[key($array)];
    }

    /**
     * Returns the address (pointer) of last element of array
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Aggregation
     *
     * @param array $array Target array
     *
     * @return mixed Pointer to the last element of array
     */
    public static function lastArrayItem(array &$array)
    {
        // If array is empty
        if (count($array) === 0) {
            return null;
        }

        // Sending the pointer of array to the end
        end($array);

        // Returning the last element of array
        return $array[key($array)];
    }

    /**
     * Functions that makes a merge between array overwritting elements
     * with same key name
     *
     * Example:
     *  array_merge_recursive_distinct(
     *    array('key' => 'old value'),
     *    array('key' => 'new value')
     *  );
     *
     *  Result:
     *   array('key' => array('new value'));
     *
     *  The array are processed for the function as reference only
     *  for improve the performance. They are not changed by the function.
     *
     * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
     * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
     * @see    <http://php.net/manual/pt_BR/function.array-merge-recursive.php>
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Aggregation
     *
     * @param array $array1 Array 1
     * @param array $array2 Array 2 (this array will overwrite the
     *                      values of $array1)
     *
     * @return array Result of merge
     */
    public static function arrayMergeRecursiveDistinct(
        array &$array1,
        array &$array2
    ): array {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (
                is_array($value) &&
                isset($merged[$key]) &&
                is_array($merged[$key])
            ) {
                $merged[$key] = \Modules\InsiderFramework\Core\Manipulation::arrayMergeRecursiveDistinct(
                    $merged[$key],
                    $value
                );
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Converts an object to array
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Aggregation
     *
     * @param mixed $object Object/array to be converted
     *
     * @return array Object converted to array
     */
    public static function objectToArray($object): array
    {
        if (!is_object($object) && !is_array($object)) {
            return $object;
        } else {
            return array_map(array($this, 'objectToArray'), (array) $object);
        }
    }

    /**
    * Cycles through an array recursively by executing a function.
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Manipulation\Aggregation
    *
    * @param mixed    $value    Value to be processed
    * @param function $callback Callback function
    *
    * @return mixed
    */
    public static function arrayWalkRecursive($value, $callback, $runCallBackInArrays = false)
    {
        if (is_array($value)) {
            $newValues = [];
            foreach ($value as $vK => $vV) {
                if ($runCallBackInArrays === true && is_array($vV)) {
                    $callback($vK, $vV);
                } else {
                    $newValues[$vK] = \Modules\InsiderFramework\Core\Aggregation::arrayWalkRecursive($vV, $callback);
                }
            }
            return $newValues;
        } else {
            return $callback($value);
        }
    }
}

<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Methods for the image handling
 *
 * @package Modules\InsiderFramework\Core\Validation\Image
 *
 * @author Marcello Costa
 */
trait Image
{
    /**
     * Checks if a file it's an image
     *
     * @author 'Silver Moon'
     * @see http://www.binarytides.com/php-check-if-file-is-an-image/
     *
     * @package Modules\InsiderFramework\Core\Validation\Image
     *
     * @param string $path Path of the file
     *
     * @return bool Processing result
     */
    public static function isImage(string $path): bool
    {
        // Getting the size of the image
        $size = getimagesize($path);

        // Checking if the type of the image with the info inside the array
        $image_type = $size[2];

        // If the returning type is GIF, JPEG, PNG or BMP
        if (
            in_array(
                $image_type,
                array(
                    IMAGETYPE_GIF,
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_BMP
                )
            )
        ) {
            // It's an image
            return true;
        }

        // It's not an image
        return false;
    }
}

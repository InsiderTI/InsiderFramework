<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 * Methods responsible for handle images
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Manipulation\Image
 */
trait Image
{
    /**
     * Converts images GIF, PNG, BMP and JPG to JPG or PNG
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Image
     *
     * @param string $desttype     Output format type for image
     * @param string $origpath     Path of the original image
     * @param string $destpath     Path of the destination image
     * @param string $widthdest    Width for the destination image (optional):
     *                              The value of this variable must be in pixels
     *                              If the width size is proportional to the height,
     *                              the value of this variable must be the string
     *                              "relative".
     *                              $widthdest = 10px
     *                              $height = "relative"
     * @param string $heightdest   Height for the destination image (optional):
     *                              analogous to what happens with the width
     * @param array  $transparency Array with the color that will be replaced with the transparency (RGB).
     *                             This is valid for image conversion to PNG format.
     *
     * @param int    $permission   Destination file permission (Linux Like)
     *
     * @return bool Processing result
     */
    public static function convertImageToFormat(
        string $desttype,
        string $origpath,
        string $destpath,
        $widthdest = false,
        $heightdest = "relative",
        array $transparency = [],
        int $permission = 700
    ): bool {
        $desttype = strtoupper(trim($desttype));
        switch ($desttype) {
            case "PNG":
            case "JPG":
            case "JPEG":
                break;

            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Unsupported format specified in convertImageToFormat %" . $desttype . "%",
                    "app/sys"
                );
                break;
        }

        // If it's not an image
        $imgInfo = getimagesize($origpath);
        if ($imgInfo === false) {
            return false;
        }

        // If the original file does not exist
        if (!(file_exists($origpath))) {
            return false;
        }

        // If the destination directory does not exist
        if (!(is_dir(dirname($destpath)))) {
            $mkdirectory = \Modules\InsiderFramework\Core\FileTree::createDirectory(dirname($destpath), $permission);

            // If something goes wrong
            if (!($mkdirectory)) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Error on creating directory %" . $destpath . "%",
                    "app/sys"
                );
            }
        }

        // Image dimensions
        // Width
        if ($widthdest === "relative") {
            if ($heightdest !== false) {
                // Getting the height
                $heightdest = floatval(str_replace('px', null, $heightdest));
            }

            // If something goes wrong
            if ($heightdest === 0 && $heightdest === false) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Error resizing image with invalid dimensions",
                    "app/sys"
                );
            }

            // Getting the resizing percent
            $respercent = (($heightdest * 100) / $imgInfo[1]);

            // Calculation of relative image width
            $widthdest = $imgInfo[0] * ($respercent / 100);
        }

        // The original width of the image must be defined to be used
        $widthorig = $imgInfo[0];

        // Height
        if ($heightdest === "relative") {
            if ($widthdest !== false) {
                // Getting the width
                $widthdest = floatval(str_replace('px', null, $widthdest));
            }

            // If something goes wrong
            if ($widthdest === 0 && $widthdest === false) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "Error resizing image with invalid dimensions",
                    "app/sys"
                );
            }

            // Getting the resizing percent
            $respercent = (($widthdest * 100) / $imgInfo[0]);

            // Calculation of relative image height
            $heightdest = $imgInfo[1] * ($respercent / 100);
        }

        // The original height of the image must be defined to be used
        $heightorig = $imgInfo[1];

        // If the height and the width are not already defined, keep the original ones
        if ($heightdest === false && $widthdest === false) {
            $heightdest = $heightorig;
            $widthdest = $widthorig;
        }

        // If the variables heightdest or the widthdest still not defined, error!
        if ($heightdest === false || $widthdest === false) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Target image height or width not set! You must specify the value \"relative\" or " .
                "a specific value for one of the dimensions of the destination image",
                "app/sys"
            );
        }

        // Checking the image type
        switch ($imgInfo[2]) {
            case IMAGETYPE_GIF:
                $src = imagecreatefromgif($origpath);
                break;
            case IMAGETYPE_JPEG:
                $src = imagecreatefromjpeg($origpath);
                break;
            case IMAGETYPE_PNG:
                $src = imagecreatefrompng($origpath);
                break;
            case IMAGETYPE_BMP:
                $src = \Modules\InsiderFramework\Core\Image::imageCreateFromBMP($origpath);
                break;
        }

        $widthdest = str_replace("px", "", trim(strtolower($widthdest . "")));
        $heightdest = str_replace("px", "", trim(strtolower($heightdest . "")));

        // Convert the image to JPEG with 100% of quality and saving the image file
        // e salvando o arquivo final
        switch (strtoupper(trim($desttype))) {
            case "PNG":
                // Create the resource of the new image
                $newImage = imagecreatetruecolor($widthdest, $heightdest);

                // If the transparency was incorrectly defined
                if (count($transparency) !== 3 && count($transparency) !== 0) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        'Transparency must be an array with 3 ' .
                        'float values in convertImageToFormat',
                        "app/sys"
                    );
                }

                // Fill the image with a white background by default
                if (count($transparency) === 0) {
                    $R = 255;
                    $G = 255;
                    $B = 255;
                } else {
                    $R = floatval($transparency[0]);
                    $G = floatval($transparency[1]);
                    $B = floatval($transparency[2]);
                }

                $tBackground = imagecolorallocate($newImage, $R, $G, $B);
                imagefill($newImage, 0, 0, $tBackground);

                // Copies the old image object ($src) to the new one ($newImage)
                $op1 = imagecopyresampled(
                    $newImage,
                    $src,
                    0,
                    0,
                    0,
                    0,
                    $widthdest,
                    $heightdest,
                    $widthorig,
                    $heightorig
                );

                // Setting color to transparent
                if (count($transparency) === 3) {
                    imagecolortransparent(
                        $newImage,
                        ImageColorAllocate(
                            $newImage,
                            $transparency[0],
                            $transparency[1],
                            $transparency[2]
                        )
                    );
                    imagealphablending($newImage, true);
                }

                // Saving the image
                $op2 = imagepng($newImage, $destpath, 0);
                break;
            case "JPG":
            case "JPEG":
                // Create the resource of the new image
                $newImage = imagecreatetruecolor($widthorig, $heightorig);

                // Fill the image with a white background by default
                $background = imagecreatetruecolor($widthorig, $heightorig);
                $whiteBackground = imagecolorallocate($background, 255, 255, 255);
                imagefill($newImage, 0, 0, $whiteBackground);

                // Copies the old image object ($src) to the new one ($newImage)
                $op1 = imagecopyresampled(
                    $newImage,
                    $src,
                    0,
                    0,
                    0,
                    0,
                    $widthorig,
                    $heightorig,
                    $widthorig,
                    $heightorig
                );

                // Resizing the image
                $fimage = imagecreatetruecolor($widthdest, $heightdest);
                imagecopyresized(
                    $fimage,
                    $newImage,
                    0,
                    0,
                    0,
                    0,
                    $widthdest,
                    $heightdest,
                    $widthorig,
                    $heightorig
                );

                // Saving the image
                $op2 = imagejpeg($fimage, $destpath, 100);
                break;
        }

        // If the conversion worked
        if ($op2) {
            return true;
        }

        // Error
        return false;
    }

    /**
     * Create an image object with a BMP file
     *
     * @author AeroX @ aerox-studios
     * @see http://php.net/manual/pt_BR/function.imagecreatefromwbmp.php
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Image
     *
     * @param string $filename Path of the file
     *
     * @return resource Objeto de imagem
     */
    public static function imageCreateFromBMP(string $filename): resource
    {
        $file = fopen($filename, "rb");
        $read = fread($file, 10);
        while (!feof($file) && $read != "") {
            $read .= fread($file, 1024);
        }
        $temp = unpack("H*", $read);
        $hex = $temp[1];
        $header = substr($hex, 0, 104);
        $body = str_split(substr($hex, 108), 6);
        if (substr($header, 0, 4) == "424d") {
            $header = substr($header, 4);
            // Remove some stuff?
            $header = substr($header, 32);
            // Get the width
            $width = hexdec(substr($header, 0, 2));
            // Remove some stuff?
            $header = substr($header, 8);
            // Get the height
            $height = hexdec(substr($header, 0, 2));
            unset($header);
        }
        $x = 0;
        $y = 1;
        $image = imagecreatetruecolor($width, $height);
        foreach ($body as $rgb) {
            $r = hexdec(substr($rgb, 4, 2));
            $g = hexdec(substr($rgb, 2, 2));
            $b = hexdec(substr($rgb, 0, 2));
            $color = imagecolorallocate($image, $r, $g, $b);
            imagesetpixel($image, $x, $height - $y, $color);
            $x++;
            if ($x >= $width) {
                $x = 0;
                $y++;
            }
        }
        return $image;
    }
}

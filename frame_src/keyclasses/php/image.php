<?php
/**
  KeyClass\Image
*/

namespace KeyClass;

/**
  KeyClass for the image handling

  @package KeyClass\Image
  
  @author Marcello Costa
*/
class Image{
    /**
        Checks if a file it's an image
     
        @author 'Silver Moon'
        @see http://www.binarytides.com/php-check-if-file-is-an-image/
     
        @package KeyClass\Image
     
        @param  string  $path    Path of the file
     
        @return  bool  Processing result
    */
    public static function isImage(string $path) : bool {
        // Getting the size of the image
        $size = getimagesize($path);


        // Checking if the type of the image with the info inside the array
        $image_type = $size[2];

        // If the returning type is GIF, JPEG, PNG or BMP
        if (in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
        {
            // It's an image
            return true;
        }

        // It's not an image
        return false;
    }

    /**
        Converts images GIF, PNG, BMP and JPG to JPG or PNG
     
        @author Marcello Costa
     
        @package KeyClass\Image
     
        @param  string     $desttype       Output format type for image
        @param  string     $origpath       Path of the original image
        @param  string     $destpath       Path of the destination image       
        @param  string     $widthdest      Width for the destination image (optional):
                                           The value of this variable must be in pixels
                                           If the width size is proportional to the height,
                                           the value of this variable must be the string
                                           "relative".
                                           $widthdest = 10px
                                           $height = "relative"
        @param  string     $heightdest     Height for the destination image (optional):
                                           analogous to what happens with the width
        @param  array      $transparency   Array with the color that will be replaced with the transparency (RGB). 
                                           This is valid for image conversion to PNG format.
     
        @param  int        $permission     Destination file permission (Linux Like)
     
        @return  bool  Processing result
    */
    public static function convertImageToFormat(string $desttype, string $origpath, string $destpath, $widthdest=false, $heightdest="relative", array $transparency = [], int $permission=700) : bool {
        $desttype=strtoupper(trim($desttype));
        switch ($desttype) {
            case "PNG":
            case "JPG":
            case "JPEG":
            break;

            default:
                \KeyClass\Error::i10nErrorRegister("Unsupported format specified in convertImageToFormat %".$desttype."%", 'pack/sys');
            break;
        }
        
        // Se não for uma imagem
        $imgInfo = getimagesize($origpath);
        if ($imgInfo === FALSE) {
            return false;
        }

        // Se o arquivo original não existir
        if (!(file_exists($origpath))) {
            return false;
        }

        // Se o diretório de destino não existir
        if (!(is_dir(dirname($destpath)))) {
            $mkdirectory = \KeyClass\FileTree::createDirectory(dirname($destpath), $permission);

            // Se algo deu errado
            if (!($mkdirectory)) {
                \KeyClass\Error::i10nErrorRegister("Error on creating directory %".$destpath."%", 'pack/sys');
            }
        }

        // Dimensões da imagem
        // Largura
        if ($widthdest === "relative") {
            // Usando dimensão passada
            if ($heightdest !== false) {
                // Extraindo altura do valor repassado
                $heightdest=floatval(str_replace('px', null, $heightdest));
            }

            // Se algo errado ocorreu
            if ($heightdest === 0 && $heightdest === false) {
                \KeyClass\Error::i10nErrorRegister("Error resizing image with invalid dimensions", 'pack/sys');
            }

            // Porcentagem a ser redimensionada
            $respercent=(($heightdest*100)/$imgInfo[1]);

            // Calculando qual será a largura relativa de imagem
            $widthdest=$imgInfo[0]*($respercent/100);
        }

        // A largura de origem deve ser definida para uso do algoritmo
        $widthorig = $imgInfo[0];

        // Altura
        if ($heightdest === "relative") {
            // Usando dimensão passada
            if ($widthdest !== false) {
                // Extraindo largura do valor repassado
                $widthdest=floatval(str_replace('px', null, $widthdest));
            }

            // Se algo errado ocorreu
            if ($widthdest === 0 && $widthdest === false) {
                \KeyClass\Error::i10nErrorRegister("Error resizing image with invalid dimensions", 'pack/sys');
            }

            // Porcentagem a ser redimensionada
            $respercent=(($widthdest*100)/$imgInfo[0]);

            // Calculando qual será a largura relativa de imagem
            $heightdest=$imgInfo[1]*($respercent/100);
        }

        // A altura de origem deve ser definida para uso do algoritmo
        $heightorig = $imgInfo[1];

        // Se a altura e largura não foram definidas, mantem as originais
        if ($heightdest === false && $widthdest === false) {
            $heightdest=$heightorig;
            $widthdest=$widthorig;
        }

        // Se a altura ou a largura não foram definidas, erro!
        if ($heightdest === false || $widthdest === false) {
            \KeyClass\Error::i10nErrorRegister("Target image height or width not set! You must specify the value \"relative\" or a specific value for one of the dimensions of the destination image", 'pack/sys');
        }

        // Verificando tipo da imagem
        switch ($imgInfo[2]) {
            case IMAGETYPE_GIF  : $src = imagecreatefromgif ($origpath);                  break;
            case IMAGETYPE_JPEG : $src = imagecreatefromjpeg($origpath);                  break;
            case IMAGETYPE_PNG  : $src = imagecreatefrompng($origpath);                   break;
            case IMAGETYPE_BMP  : $src = \KeyClass\Image::imageCreateFromBMP($origpath);  break;
        }

        $widthdest=str_replace("px","",trim(strtolower($widthdest."")));
        $heightdest=str_replace("px","",trim(strtolower($heightdest."")));
                
        // Convertendo imagem para JPEG com 100% de qualidade
        // e salvando o arquivo final
        switch (strtoupper(trim($desttype))) {
            case "PNG":
                // Criando resource da nova imagem
                $newImage = imagecreatetruecolor($widthdest, $heightdest);
                
                // Se a transparência foi definida incorretamente
                if (count($transparency) !== 3 && count($transparency) !== 0) {
                    \KeyClass\Error::i10nErrorRegister('Transparency must be an array with 3 float values in convertImageToFormat', 'pack/sys');
                }
                
                // Preenchendo com fundo branco por padrão
                if (count($transparency) === 0) {
                    $R=255;
                    $G=255;
                    $B=255;
                }
                else {
                    $R=floatval($transparency[0]);
                    $G=floatval($transparency[1]);
                    $B=floatval($transparency[2]);
                }

                $tBackground = imagecolorallocate($newImage, $R, $G, $B); 
                imagefill($newImage, 0, 0, $tBackground);
                
                // Copiando o objeto da imagem antiga ($src) para a nova ($newImage)
                $op1=imagecopyresampled($newImage, $src, 0, 0, 0, 0, $widthdest, $heightdest, $widthorig, $heightorig);
                
                // Definindo cor como transparente
                if (count($transparency) === 3) {
                    imagecolortransparent($newImage, ImageColorAllocate($newImage, $transparency[0], $transparency[1], $transparency[2]));
                    imagealphablending($newImage, true);
                }
                
                // Salvando imagem
                $op2=imagepng($newImage, $destpath, 0);
            break;
            case "JPG":
            case "JPEG":
                // Criando resource da nova imagem
                $newImage = imagecreatetruecolor($widthorig, $heightorig);
                
                // Colocando background branco
                $background = imagecreatetruecolor($widthorig, $heightorig);
                $whiteBackground = imagecolorallocate($background, 255, 255, 255);
                imagefill($newImage, 0, 0, $whiteBackground);

                // Copiando o objeto da imagem antiga ($src) para a nova ($newImage)
                $op1=imagecopyresampled($newImage, $src, 0, 0, 0, 0, $widthorig, $heightorig, $widthorig, $heightorig);

                // Redimensionando imagem
                $fimage = imagecreatetruecolor($widthdest, $heightdest);
                imagecopyresized($fimage, $newImage, 0, 0, 0, 0, $widthdest, $heightdest, $widthorig, $heightorig);

                // Salvando imagem
                $op2=imagejpeg($fimage, $destpath, 100);
            break;
        }

        // Se a conversão/salvamento funcionou corretamente
        if ($op2) {
            return true;
        }

        // Erro
        return false;
    }

    /**
        Cria um objeto de imagem através de um arquivo BMP
      
        @author AeroX @ aerox-studios
        @see http://php.net/manual/pt_BR/function.imagecreatefromwbmp.php
     
        @package KeyClass\Image
      
        @param  string  $filename    Path of the file
     
        @return  resource  Objeto de imagem
    */
    public static function imageCreateFromBMP(string $filename) : resource {
        $file = fopen( $filename, "rb" );
        $read = fread( $file, 10 );
        while( !feof( $file ) && $read != "" )
        {
            $read .= fread( $file, 1024 );
        }
        $temp = unpack( "H*", $read );
        $hex = $temp[1];
        $header = substr( $hex, 0, 104 );
        $body = str_split( substr( $hex, 108 ), 6 );
        if ( substr( $header, 0, 4 ) == "424d" )
        {
            $header = substr( $header, 4 );
            // Remove some stuff?
            $header = substr( $header, 32 );
            // Get the width
            $width = hexdec( substr( $header, 0, 2 ) );
            // Remove some stuff?
            $header = substr( $header, 8 );
            // Get the height
            $height = hexdec( substr( $header, 0, 2 ) );
            unset( $header );
        }
        $x = 0;
        $y = 1;
        $image = imagecreatetruecolor( $width, $height );
        foreach( $body as $rgb )
        {
            $r = hexdec( substr( $rgb, 4, 2 ) );
            $g = hexdec( substr( $rgb, 2, 2 ) );
            $b = hexdec( substr( $rgb, 0, 2 ) );
            $color = imagecolorallocate( $image, $r, $g, $b );
            imagesetpixel( $image, $x, $height-$y, $color );
            $x++;
            if ( $x >= $width )
            {
                $x = 0;
                $y++;
            }
        }
        return $image;
    }
}

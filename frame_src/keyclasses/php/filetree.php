<?php
/**
  KeyClass\FileTree
*/

namespace KeyClass;

/**
  KeyClass of handle files and directories
 
  @package KeyClass\FileTree

  @author Marcello Costa
*/
class FileTree{
    /**
        Remove an directory recursively
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $path               Path of directory
        @param  int|float  $delaytry           Time (in seconds) between the remove attempts
        @param  int        $maxToleranceLoops  Maximum loop number waiting the $delaytry time
     
        @return  bool  Return true if the directory was sucessful removed
    */
    public static function deleteDirectory(string $path, $delaytry=0.15, int $maxToleranceLoops=null) : bool {
        if (!is_numeric($delaytry)) {
            primaryError('Variable delaytry is not numeric');
        }
        
        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops=1000;
            }
            else {
                $maxToleranceLoops=MAX_TOLERANCE_LOOPS;
            }
        }

        // Remove the last character "/" from the string and add an "/" in the end
        // if did not exists
        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        // Open an directory for editting
        $handle = opendir($path);

        // While the directory can be readed, he still exists
        while(false !== ($file = readdir($handle))) {
            // If the file is not "." or ".."
            if ($file != '.' and $file != '..' ) {
                // Setting the variable that stores the full path of the file
                $fullpath = $path.$file;

                // If it's an directory
                if (is_dir($fullpath)) {
                    // Calls the function again to delete the files inside the directory
                    $result=\KeyClass\FileTree::deleteDirectory($fullpath);

                    // If something went wrong
                    if ($result === false) {
                        \KeyClass\Error::i10nErrorRegister("Error trying to delete %".$fullpath."%", 'pack/sys');
                    }
                }
                // If it's an file
                else {
                    // If the file have a lock
                    $countToleranceLoops=0;
                    $idError = null;
                    while(file_exists($fullpath.".lock")) {
                        $countToleranceLoops++;

                        // If it takes longer than normal for directory deletion
                        if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
                          $countToleranceLoops=0;
                          \KeyClass\Error::i10nErrorRegister('Too long waiting time detected to deletion of directory: %'.$path.'%', 'pack/sys', LINGUAS, "LOG");
                        }

                        // Waiting for the lock is gone
                        sleep($delaytry);
                    }

                    // Removing the file
                    $result=unlink($fullpath);

                    // If something went wrong
                    if ($result === false) {
                        \KeyClass\Error::i10nErrorRegister("Error trying to delete %".$fullpath."%", 'pack/sys');
                    }
                }
            }
        }

        // Close directory edit
        closedir($handle);

        // Erasing the root directory
        $result=rmdir($path);

        // If something went wrong
        if ($result === false) {
            \KeyClass\Error::i10nErrorRegister("Error trying to delete %".$path."%", 'pack/sys');
        }

        // Returning the success
        return true;
    }

    /**
        Remove an file

        @author Marcello Costa
     
        @package KeyClass\FileTree
      
        @param  string     $path               Path of the file to be deleted
        @param  int|float  $delaytry           Time (in seconds) between the remove attempts
        @param  int        $maxToleranceLoops  Maximum loop number waiting the $delaytry time
     
        @return  bool  Processing result
    */
    public static function deleteFile(string $path, $delaytry=0.15, int $maxToleranceLoops=null) : bool {
        if (!is_numeric($delaytry)) {
            primaryError('Variable delaytry is not numeric');
        }
        
        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops=1000;
            }
            else {
                $maxToleranceLoops=MAX_TOLERANCE_LOOPS;
            }
        }

        $countToleranceLoops=0;
        $idError = null;

        // If the file have a lock
        while(file_exists($path.".lock")) {
            $countToleranceLoops++;

            // If it takes longer than normal for directory deletion
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to deletion of directory: %".$path."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Waiting for the lock is gone
            sleep($delaytry);
        }

        // Remove the file
        // If the file does exists
        if (file_exists($path)) {
            $result=unlink($path);

            // If something went wrong
            if ($result === false) {
                \KeyClass\Error::i10nErrorRegister("Error trying to delete %".$path."%", 'pack/sys');
            }

            // Returning the success
            return true;
        }

        // If the file does not exists
        else {
            return false;
        }
    }

    /**
        Creates a directory in the framework directory tree
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $path           Full path of the directory
        @param  int     $permission     Permissions of the directory (Linux Like)
        @param  bool    $recursive      Flag to recursively create (or not) the directory
        @param  array   $ignorechars    Characters that must be ignored on the path
                                        (special characters validation)
     
        @return  bool  Processing result
    */
    public static function createDirectory(string $path, int $permission, bool $recursive=true, array $ignorechars=[]) : bool {
        // Specified has been specified
        if ($path !== null) {
            // Handling the name of the directory
            $path=str_replace("\\".DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR , $path);

            // Checking if the path did exists
            if (is_dir($path)) {
                // If did exists, success
                return true;
            }

            // If did not exists
            else {
                // Creates the directory
                $createop=mkdir($path,octdec($permission),$recursive);

                // Returning the result
                return $createop;
            }
        }

        // Empty path
        return false;
    }

    /**
        Reimplementation of the require function of the PHP that allows lock for the files (.lock)
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $filepath           Path of the file
        @param  int|float  $delaytry           Time (in seconds) between the require attempts
        @param  int        $maxToleranceLoops  Maximum loop number waiting the $delaytry time
     
        @return Void
    */
    public static function requireFile(string $filepath, $delaytry=0.15, int $maxToleranceLoops=null) : void {
        if (!is_numeric($delaytry)) {
            primaryError('Variable delaytry is not numeric');
        }
        
        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops=1000;
            }
            else {
                $maxToleranceLoops=MAX_TOLERANCE_LOOPS;
            }
        }

        $countToleranceLoops=0;
        $idError=null;

        // If the file have a lock
        while(file_exists($filepath.".lock")) {
            $countToleranceLoops++;

            // If it takes longer than normal for require the file
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to require file: %".$filepath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Waiting for the lock is gone
            sleep($delaytry);
        }

        require($filepath);
    }

    /**
        Reimplementation of the require_once function of the PHP that allows lock for the files (.lock)
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $filepath            Path of the file
        @param  int|float  $delaytry            Time (in seconds) between the require attempts
        @param  int        $maxToleranceLoops   Maximum loop number waiting the $delaytry time
     
        @return void
    */
    public static function requireOnceFile(string $filepath, $delaytry=0.15, int $maxToleranceLoops=null) : void {
        if (!is_numeric($delaytry)) {
            primaryError('Variable delaytry is not numeric');
        }
        
        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops=1000;
            }
            else {
                $maxToleranceLoops=MAX_TOLERANCE_LOOPS;
            }
        }

        $countToleranceLoops=0;
        $idError=null;

        // If the file have a lock
        while(file_exists($filepath.".lock")) {
            $countToleranceLoops++;

            // If it takes longer than normal for require the file
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to require file: %".$filepath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Waiting for the lock is gone
            sleep($delaytry);
        }

        require_once($filepath);
    }

    /**
        Writes content in a file
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
      
        @param  string     $filepath           Path of the file
        @param  mixed      $data               Data to be written in the file
        @param  bool       $overwrite          Overwrite data or not
        @param  int|float  $delaytry           Time (in seconds) between the write attempts
        @param  int        $maxToleranceLoops  Maximum loop number waiting the $delaytry time
     
        @return  bool  Processing result
    */
    public static function fileWriteContent(string $filepath, $data, bool $overwrite=false, $delaytry=0.15, int $maxToleranceLoops=null) : bool {
        if (!is_numeric($delaytry)) {
            primaryError('Variable delaytry is not numeric');
        }
        
        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops=1000;
            }
            else {
                $maxToleranceLoops=MAX_TOLERANCE_LOOPS;
            }
        }

        $countToleranceLoops = 0;

        $idError = null;
        // If the file have a lock
        while(file_exists($filepath.".lock")) {
            $countToleranceLoops++;

            // If it takes longer than normal for write in the file
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long wait time to write to file: %".$filepath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Waiting for the lock is gone
            sleep($delaytry);
        }
        
        // Checking if the directory did exists
        if (!is_dir(dirname($filepath))){
            // Creating the directory
            \KeyClass\FileTree::createDirectory(dirname($filepath), 777);
            
            // Checking if the directory did exists
            if (!is_dir(dirname($filepath))){
                \KeyClass\Error::i10nErrorRegister("Unable to create directory: %".dirname($filepath)."%", 'pack/sys');
            }
        }

        // Creating the lock file until operation is done
        touch($filepath.".lock");

        // Writing the content in the file
        if ($overwrite === false) {
            $result=file_put_contents($filepath, $data, FILE_APPEND);
        }
        else {
            $result=file_put_contents($filepath, $data);
        }

        // Releasing the lock
        if (file_exists($filepath.".lock")) {
            unlink($filepath.".lock");
        }

        // Everthing ok, so returning true
        if ($result !== false) {
            return true;
        }

        // Fail
        else {
            return false;
        }
    }

    /**
        Gets the content of an file
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $filepath           Path of the file
        @param  bool       $returnstring       If true, the function will return a string, otherwise will return an array
        @param  int|float  $delaytry           Time (in seconds) between the read attempts
        @param  int        $maxToleranceLoops  Maximum loop number waiting the $delaytry time
     
        @return  string  Content of the file
    */
    public static function fileReadContent(string $filepath, bool $returnstring=true, $delaytry=0.15, int $maxToleranceLoops=null) : string {
        if (!is_numeric($delaytry)) {
            primaryError('Variable delaytry is not numeric');
        }

        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops=1000;
            }
            else {
                $maxToleranceLoops=MAX_TOLERANCE_LOOPS;
            }
        }

        if ($filepath === null) {
            \KeyClass\Error::i10nErrorRegister("The file path not specified", 'pack/sys');
        }

        $countToleranceLoops=0;
        $idError = null;
        // If the file have a lock
        while(file_exists($filepath.".lock")) {
            $maxToleranceLoops++;

            // If it takes longer than normal for read the file
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to read file: %".$filepath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Waiting for the lock is gone
            sleep($delaytry);
        }
        
        // Checking if the file did exists
        if (!file_exists($filepath) || !is_readable($filepath)) {
            return false;
        }

        // Reading the content of the file
        if ($returnstring === true) {
            // Returning a string
            $result=file_get_contents($filepath);
        }
        else {
            // Returning an array
            $result=file($filepath);
        }

        // Everything ok, file successful read
        if ($result !== false) {
            // Releasing the lock
            return $result;
        }

        // Read error
        else {
            return false;
        }
    }

    /**
        Creates a map in format of an array of a path

        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $dir        Path to be mapped
        @param  bool    $sortitems  Splits the result inside the array ordering by directories
     
        @return  array  Array of the mapped path
    */
    public static function dirTree(string $dir, bool $sortitems=false) : array {
        // Removing the last bar from the string $dir (if did exists)
        if ($dir[strlen($dir)-1] === DIRECTORY_SEPARATOR) {
            $dir=\KeyClass\Code::extractString($dir, 0, strlen($dir)-1);
        }

        // Mapping the directory
        $path = [];
        $stack[] = $dir;
        while ($stack) {
            $thisdir = array_pop($stack);
            if ($dircont = scandir($thisdir)) {
                $i=0;
                while (isset($dircont[$i])) {
                    if ($dircont[$i] !== '.' && $dircont[$i] !== '..') {
                        $current_file = "{$thisdir}".DIRECTORY_SEPARATOR."{$dircont[$i]}";
                        if (is_file($current_file)) {
                            $path[] = "{$thisdir}".DIRECTORY_SEPARATOR."{$dircont[$i]}";
                        }

                        elseif (is_dir($current_file)) {
                            $path[] = "{$thisdir}".DIRECTORY_SEPARATOR."{$dircont[$i]}";
                            $stack[] = $current_file;
                        }
                    }
                    $i++;
                }
            }
        }

        // If the result needs to be organized
        if ($sortitems === true) {
            // Calls the function which is responsable for organize the files inside the path
            $dirarray=($fileData = \KeyClass\FileTree::fillArrayWithFileNodes(new \DirectoryIterator($dir)));

            // Returning the organized result
            return $dirarray;
        }

        // Returning the result without arranging the array
        return $path;
    }

    /**
       Maps directory nodes in an array

       @author 'Peter Bailey'
       @see <http://stackoverflow.com/questions/952263/deep-recursive-array-of-directory-structure-in-php>

       @package KeyClass\FileTree    
    
       @param  \DirectoryIterator  $dir    DirectoryIterator object containing path
    
       @return  array  Array of the informed path
   */
   public static function fillArrayWithFileNodes(\DirectoryIterator $dir) : array {
       $data = array();

       foreach ( $dir as $node )
       {
           if ( $node->isDir() && !$node->isDot() )
           {
               $data[$node->getFilename()] = \KeyClass\FileTree::fillArrayWithFileNodes( new \DirectoryIterator( $node->getPathname() ) );
           }
           else if ( $node->isFile() )
           {
               $data[] = $node->getFilename();
           }
       }

       return $data;
   }

    /**
        Function for renaming files

        @author Marcello Costa
        @package KeyClass\FileTree
     
        @param  string     $origpath           Original path of the file
        @param  string     $destpath           Destination path of the copied file
        @param  bool       $overwrite          If true, when the destination file already exists, overwrite it
        @param  int|float  $delaytry           Time (in seconds) between the move attempts
        @param  int        $maxToleranceLoops  Maximum loop number waiting the $delaytry time
     
        @return bool  Processing result
    */
    public static function renameFile(string $origpath, string $destpath, bool $overwrite=false, $delaytry=0.15, int $maxToleranceLoops=null) : bool {
        if (!is_numeric($delaytry)) {
            primaryError('Variable delaytry is not numeric');
        }
        
        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops=1000;
            }
            else {
                $maxToleranceLoops=MAX_TOLERANCE_LOOPS;
            }
        }

        $countToleranceLoops=0;

        // If the file have a lock
        $idError = null;
        while(file_exists($origpath.".lock")) {
            $countToleranceLoops++;

            // If it takes longer than normal for rename the file
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to rename file: %".$origpath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Waiting for the lock is gone
            sleep($delaytry);
        }

        // Checking if the directory did exists
        if (!(is_dir(dirname($destpath)))) {
            return false;
        }

        // If the file did exists and it is to rewrite or if the file did not exists
        if (((file_exists($destpath) === true) && ($overwrite === true)) ||
             (file_exists($destpath) === false)) {

            // Renaming the file
            $rename=rename($origpath, $destpath);
        }
        else {
            $rename=false;
        }

        // Returning the result
        return $rename;
    }

    /**
        Function for copying files
        
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $origpath           Original path of the file
        @param  string     $destpath           Destination path of the copied file
        @param  bool       $overwrite          If true, when the destination file already exists, overwrite it
        @param  int|float  $delaytry           Time (in seconds) between the copy attempts
        @param  int        $maxToleranceLoops  Maximum loop number waiting the $delaytry time
     
        @return bool  Processing result
    */
    public static function copyFile(string $origpath, string $destpath, bool $overwrite=false, $delaytry=0.15, int $maxToleranceLoops=null) : bool {
        if (!is_numeric($delaytry)) {
            primaryError('Variable delaytry is not numeric');
        }
        
        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops=1000;
            }
            else {
                $maxToleranceLoops=MAX_TOLERANCE_LOOPS;
            }
        }

        $countToleranceLoops=0;

        $idError = null;
        // If the file have a lock
        while(file_exists($origpath.".lock")) {
            $countToleranceLoops++;

            // Se demorar mais do que o normal para copiar um arquivo travado
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to copy file: %".$origpath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Waiting for the lock is gone
            sleep($delaytry);
        }

        // Checking if the directory did exists
        if (!(is_dir(dirname($destpath)))) {
            return false;
        }

        // If the file did exists and it is to rewrite or if the file did not exists
        if (((file_exists($destpath) === true) && ($overwrite === true)) ||
             (file_exists($destpath) === false)) {

            // Copying the file
            $copy=copy($origpath, $destpath);
        }
        else {
            $copy=false;
        }

        // Returning the result
        return $copy;
    }

    /**
        Function that moves a file. This function is an alias for the renameFile function.
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $origpath           Original path of the file
        @param  string     $destpath           Destination path of the moved file
        @param  bool       $overwrite          If true, when the destination file already exists, overwrite it
        @param  int|float  $delaytry           Time (in seconds) between the move attempts
        @param  int        $maxToleranceLoops  Maximum loop number waiting the $delaytry time
     
        @return  bool Processing result
    */
    public static function moveFile(string $origpath, string $destpath, bool $overwrite=false, $delaytry=0.15, int $maxToleranceLoops=null) : bool {
        if (!is_numeric($delaytry)) {
            primaryError('Variable delaytry is not numeric');
        }

        if ($maxToleranceLoops === null) {
            if (!defined("MAX_TOLERANCE_LOOPS")) {
                $maxToleranceLoops=1000;
            }
            else {
                $maxToleranceLoops=MAX_TOLERANCE_LOOPS;
            }
        }

        // Moving the fiel with the rename function
        $result=\KeyClass\FileTree::renameFile($origpath, $destpath, $overwrite, $delaytry, $maxToleranceLoops);

        // Returning the result
        return $result;
    }

    /**
        Copies a directory recursively
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $src    Origin directory
        @param  string  $dst    Destination directory
     
        @return bool Processing result
    */
    public static function copyDirectory(string $src, string $dst) : bool {
        // Opening the origin directory
        $dir = opendir($src);

        // Creating the destination directory (if did not already exists)
        if (!is_dir($dst)) {
            mkdir($dst);
        }

        // While is possible read a file inside the origin directory
        while(false !== ($file = readdir($dir))) {
            // If it is not the "." or ".." directory
            if (($file != '.') && ($file != '..')) {
                // If is a directory
                if (is_dir($src.DIRECTORY_SEPARATOR.$file)) {
                    // Calls the function again
                    \KeyClass\FileTree::copyDirectory($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
                }

                // If it is a file
                else {
                    // Copy the origin file to the destination directory
                    copy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        // Closes the origin directory
        closedir($dir);

        // Returning the result
        return true;
    }

    /**
        Changes the permissions of directories and files recursively 
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $dir               Path of directory
        @param  int     $dirPermissions    New directory permissions
        @param  int     $filePermissions   New files permissions
     
        @return void Without return
    */
    public static function changePermissionRecursively(string $dir, int $dirPermissions, int $filePermissions) : void {
        // Opening the target directory
        $dp = opendir($dir);

        // While did exists files inside a directory
        while($file = readdir($dp)) {
            // If the file is "." or ".." ignore them
            if (($file == ".") || ($file == ".."))
                continue;

            // Setting the variable that stores the full path of the file
            $fullPath = $dir.DIRECTORY_SEPARATOR.$file;

            // If it's a directory
            if (is_dir($fullPath)) {
                // Changing the directory permissions
                chmod($fullPath, $dirPermissions);

                // Calling the function again
                \KeyClass\FileTree::changePermissionRecursively($fullPath, $dirPermissions, $filePermissions);
            }

            // If it's a file
            else {
                // Changing the file permissions
                chmod($fullPath, $filePermissions);
            }
        }

        // Closing the target directory
        closedir($dp);
    }

    /**
        Downloads a remote file to the local file system
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $url    File origin URL
        @param  string  $path   Directory destination for the file
     
        @return  bool Processing result
    */
    public static function downloadFile(string $url, string $path) : bool {
        $newfname = $path;
        $file = fopen ($url, 'rb');
        if ($file) {
            $newf = fopen ($newfname, 'wb');
            if ($newf) {
                while(!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }
        
        return true;
    }

    /**
       Get the absolute path of a string

       @author 'Sven Arduwie'
       @see <https://www.php.net/manual/pt_BR/function.realpath.php>

       @package KeyClass\FileTree    
    
       @param  string  $path    Path to be translated
    
       @return  string  Absolute path
   */
    public static function getAbsolutePath($path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }
}
<?php
/**
  Arquivo KeyClass\FileTree
*/

// Namespace das KeyClass
namespace KeyClass;

/**
  KeyClass de tratamento de arquivos e diretórios
 
  @package KeyClass\FileTree

  @author Marcello Costa
*/
class FileTree{
    /**
        Remove um diretório recursivamente
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $path               Caminho do diretório
        @param  int|float  $delaytry           Tempo (em segundos) entre as tentativas de remoção
        @param  int        $maxToleranceLoops  Número máximo de loops aguardando o tempo $delaytry
     
        @return  bool  Retorna true se o diretório foi apagado com sucesso
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

        // Remove o último caractere "/" da string e adiciona um "/" no final
        // caso não exista
        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        // Abre o diretório para edição
        $handle = opendir($path);

        // Enquanto o diretório puder ser lido, ele ainda existe
        while(false !== ($file = readdir($handle))) {
            // Se o arquivo não é "." ou ".."
            if ($file != '.' and $file != '..' ) {
                // Preenchendo a variável que guarda o path completo do arquivo
                $fullpath = $path.$file;

                // Se for um diretório
                if (is_dir($fullpath)) {
                    // Chama a função novamente para apagar os arquivos dentro
                    // do mesmo
                    $result=\KeyClass\FileTree::deleteDirectory($fullpath);

                    // Se algo saiu errado
                    if ($result === false) {
                        \KeyClass\Error::i10nErrorRegister("Error trying to delete %".$fullpath."%", 'pack/sys');
                    }
                }
                // Se for um arquivo
                else {
                    // Se o arquivo contiver uma trava
                    $countToleranceLoops=0;
                    $idError = null;
                    while(file_exists($fullpath.".lock")) {
                        $countToleranceLoops++;

                        // Se demorar mais do que o normal para deleção do diretório
                        if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
                          $countToleranceLoops=0;
                          \KeyClass\Error::i10nErrorRegister('Too long waiting time detected to deletion of directory: %'.$path.'%', 'pack/sys', LINGUAS, "LOG");
                        }

                        // Aguardar a trava sumir
                        sleep($delaytry);
                    }

                    // Remove o arquivo
                    $result=unlink($fullpath);

                    // Se algo saiu errado
                    if ($result === false) {
                        \KeyClass\Error::i10nErrorRegister("Error trying to delete %".$fullpath."%", 'pack/sys');
                    }
                }
            }
        }

        // Fecha a edição do diretório
        closedir($handle);

        // Apaga o diretório raiz requisitado
        $result=rmdir($path);

        // Se algo saiu errado
        if ($result === false) {
            \KeyClass\Error::i10nErrorRegister("Error trying to delete %".$path."%", 'pack/sys');
        }

        // Retornando o sucesso
        return true;
    }

    /**
        Remove um arquivo

        @author Marcello Costa
     
        @package KeyClass\FileTree
      
        @param  string     $path               Caminho do arquivo a ser excluído
        @param  int|float  $delaytry           Tempo (em segundos) entre as tentativas
                                               de remoção
        @param  int        $maxToleranceLoops  Número máximo de loops aguardando o tempo $delaytry
     
        @return  bool  Resultado da operação
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

        // Se o arquivo contiver uma trava
        while(file_exists($path.".lock")) {
            $countToleranceLoops++;

            // Se demorar mais do que o normal para deletar um arquivo
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to deletion of directory: %".$path."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Aguardar a trava sumir
            sleep($delaytry);
        }

        // Remove o arquivo
        // Se o arquivo existir
        if (file_exists($path)) {
            $result=unlink($path);

            // Se algo saiu errado
            if ($result === false) {
                \KeyClass\Error::i10nErrorRegister("Error trying to delete %".$path."%", 'pack/sys');
            }

            // Retornando o sucesso
            return true;
        }

        // Se o arquivo não existe
        else {
            return false;
        }
    }

    /**
        Cria um diretório na árvore do framework
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $path           Caminho completo do diretório
        @param  int     $permission     Permissões do diretório (Linux Like)
        @param  bool    $recursive      Criar diretório recursivamente ou não
        @param  array    $ignorechars   Caracteres que não serão recusados no path
                                        (validação de caracteres especiais)
     
        @return  bool  Resultado da operação
    */
    public static function createDirectory(string $path, int $permission, bool $recursive=true, array $ignorechars=[]) : bool {
        // Path especificado
        if ($path !== null) {
            // Tratando nome do diretório
            $path=str_replace("\\".DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR , $path);

            // Verifica se o path existe
            if (is_dir($path)) {
                // Se já existe, sucesso
                return true;
            }

            // Se ainda não existe
            else {
                //Cria o diretório
                $createop=mkdir($path,octdec($permission),$recursive);

                // Retorna o resultado
                return $createop;
            }
        }

        // Path vazio
        return false;
    }

    /**
        Reimplementação da função require do php que aceita os arquivos de trava (.lock)
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $filepath           Caminho do arquivo
        @param  int|float  $delaytry           Tempo (em segundos) entre as tentativas de require
        @param  int        $maxToleranceLoops  Número máximo de loops aguardando o tempo $delaytry
     
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

        // Se o arquivo contiver uma trava
        while(file_exists($filepath.".lock")) {
            $countToleranceLoops++;

            // Se demorar mais do que o normal para requerer um arquivo
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to require file: %".$filepath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Aguardar a trava sumir
            sleep($delaytry);
        }

        require($filepath);
    }

    /**
        Reimplementação da função require_once do php que aceita os arquivos de trava (.lock)
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $filepath    Caminho do arquivo
        @param  int|float  $delaytry    Tempo (em segundos) entre as tentativas de require
        @param  int        $maxToleranceLoops  Número máximo de loops aguardando o tempo $delaytry
     
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

        // Se o arquivo contiver uma trava
        while(file_exists($filepath.".lock")) {
            $countToleranceLoops++;

            // Se demorar mais do que o normal para requerer em um arquivo
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to require file: %".$filepath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Aguardar a trava sumir
            sleep($delaytry);
        }

        require_once($filepath);
    }

    /**
        Função que escreve em um arquivo
      
        @author Marcello Costa
     
        @package KeyClass\FileTree
      
        @param  string     $filepath    Caminho completo do arquivo
        @param  mixed      $data        Dados a serem gravados no arquivo
        @param  bool       $overwrite   Sobreescrever dados ou não
        @param  int|float  $delaytry    Tempo (em segundos) entre as tentativas de gravação
        @param  int        $maxToleranceLoops  Número máximo de loops aguardando o tempo $delaytry
     
        @return  bool  Retorna o resultado da gravação do arquivo
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
        // Se o arquivo contiver uma trava
        while(file_exists($filepath.".lock")) {
            $countToleranceLoops++;

            // Se demorar mais do que o normal para escrever em um arquivo
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long wait time to write to file: %".$filepath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Aguardar a trava sumir
            sleep($delaytry);
        }
        
        // Verificando se o diretório existe
        if (!is_dir(dirname($filepath))){
            // Criando o diretório
            \KeyClass\FileTree::createDirectory(dirname($filepath), 777);
            
            // Verificando se o diretório existe
            if (!is_dir(dirname($filepath))){
                \KeyClass\Error::i10nErrorRegister("Unable to create directory: %".dirname($filepath)."%", 'pack/sys');
            }
        }

        // Então, escrever no arquivo, travando-o para leitura e escrita
        touch($filepath.".lock");

        // Inserindo o conteúdo no arquivo
        if ($overwrite === false) {
            $result=file_put_contents($filepath, $data, FILE_APPEND);
        }
        else {
            $result=file_put_contents($filepath, $data);
        }

        // Removendo o arquivo de trava
        if (file_exists($filepath.".lock")) {
            unlink($filepath.".lock");
        }

        // Tudo certo, arquivo gravado com sucesso
        if ($result !== false) {
            return true;
        }

        // Erro na gravação
        else {
            return false;
        }
    }

    /**
        Função que recupera o conteúdo de um arquivo
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $filepath      Caminho completo do arquivo
        @param  bool       $returnstring  Se true retorna uma string, se false retorna um array
        @param  int|float  $delaytry      Tempo (em segundos) entre as tentativas de leitura
        @param  int        $maxToleranceLoops  Número máximo de loops aguardando o tempo $delaytry
     
        @return  string  Conteúdo do arquivo
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
        // Se o arquivo contiver uma trava
        while(file_exists($filepath.".lock")) {
            $maxToleranceLoops++;

            // Se demorar mais do que o normal para ler um arquivo
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to read file: %".$filepath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Aguardar a trava sumir
            sleep($delaytry);
        }
        
        // Verificando se o arquivo existe
        if (!file_exists($filepath) || !is_readable($filepath)) {
            return false;
        }

        // Lendo o conteúdo no arquivo
        if ($returnstring === true) {
            // Retorna string
            $result=file_get_contents($filepath);
        }
        else {
            // Retorna array
            $result=file($filepath);
        }

        // Tudo certo, arquivo lido com sucesso
        if ($result !== false) {
            // Removendo o arquivo de trava
            return $result;
        }

        // Erro na leitura
        else {
            return false;
        }
    }

    /**
        Função que constroe um array contendo a árvore de diretórios e arquivos
        de um caminho

        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $dir        Caminho a ser mapeado
        @param  bool    $sortitems  Divide o resultado dentro de um array
                                    organizado por diretórios
     
        @return  array  Array do caminho mapeado
    */
    public static function dirTree(string $dir, bool $sortitems=false) : array {
        // Retirando a última barra da string $dir (caso exista)
        if ($dir[strlen($dir)-1] === DIRECTORY_SEPARATOR) {
            $dir=\KeyClass\Code::extractString($dir, 0, strlen($dir)-1);
        }

        // Mapeando diretório
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

        // Se for preciso organizar o resultado obtido
        if ($sortitems === true) {
            // Chama a função que organiza os arquivos contidos no path em um array
            $dirarray=($fileData = \KeyClass\FileTree::fillArrayWithFileNodes(new \DirectoryIterator($dir)));

            // Retornando o resultado organizado
            return $dirarray;
        }

        // Retornando o resultado sem organizar o array
        return $path;
    }

    /**
       Mapeia os nós do diretório em um array

       @author 'Peter Bailey'
       @see <http://stackoverflow.com/questions/952263/deep-recursive-array-of-directory-structure-in-php>

       @package KeyClass\FileTree    
    
       @param  \DirectoryIterator  $dir    Objeto DirectoryIterator contendo o path
    
       @return  array  Array organizado do path informado
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
        Função para renomear arquivos

        @author Marcello Costa
        @package KeyClass\FileTree
     
        @param  string     $origpath    Path original do arquivo
        @param  string     $destpath    Path de destino do arquivo
        @param  bool       $overwrite   Sobreescrever arquivo
        @param  int|float  $delaytry    Tempo (em segundos) entre as tentativas de mover
        @param  int        $maxToleranceLoops  Número máximo de loops aguardando o tempo $delaytry
     
        @return bool  Resultado da operação
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

        // Se o arquivo contiver uma trava
        $idError = null;
        while(file_exists($origpath.".lock")) {
            $countToleranceLoops++;

            // Se demorar mais do que o normal para renomear um arquivo
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to rename file: %".$origpath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Aguardar a trava sumir
            sleep($delaytry);
        }

        // Verificando se o diretório de destino existe
        if (!(is_dir(dirname($destpath)))) {
            return false;
        }

        // Se o arquivo existe e é para sobreescever ou se o arquivo não existe
        if (((file_exists($destpath) === true) && ($overwrite === true)) ||
             (file_exists($destpath) === false)) {

            // Renomeando o arquivo
            $rename=rename($origpath, $destpath);
        }
        else {
            $rename=false;
        }

        // Retornando o resultado da função
        return $rename;
    }

    /**
        Função para copiar arquivos
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string     $origpath    Path original do arquivo
        @param  string     $destpath    Path de destino do arquivo
        @param  bool       $overwrite   Path de destino do arquivo
        @param  int|float  $delaytry    Tempo (em segundos) entre as tentativas de mover
        @param  int        $maxToleranceLoops  Número máximo de loops aguardando o tempo $delaytry
     
        @return bool  Resultado da operação
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
        // Se o arquivo contiver uma trava
        while(file_exists($origpath.".lock")) {
            $countToleranceLoops++;

            // Se demorar mais do que o normal para copiar um arquivo travado
            if ($countToleranceLoops > $maxToleranceLoops && $idError === null) {
              $countToleranceLoops=0;
              \KeyClass\Error::i10nErrorRegister("Too long waiting time detected to copy file: %".$origpath."%", 'pack/sys', LINGUAS, "LOG");
            }

            // Aguardar a trava sumir
            sleep($delaytry);
        }

        // Verificando se o diretório de destino existe
        if (!(is_dir(dirname($destpath)))) {
            return false;
        }

        // Se o arquivo existe e é para sobreescever ou se o arquivo não existe
        if (((file_exists($destpath) === true) && ($overwrite === true)) ||
             (file_exists($destpath) === false)) {

            // Copiando o arquivo
            $copy=copy($origpath, $destpath);
        }
        else {
            $copy=false;
        }

        // Retornando o resultado da função
        return $copy;
    }

    /**
        Função que move um arquivo. Esta função é alias da função renameFile
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $origpath     Path original do arquivo
        @param  string  $destpath     Path de destino do arquivo
        @param  bool  $overwrite      Sobreescrever arquivo
        @param  int|float  $delaytry  Tempo (em segundos) entre as tentativas de mover
        @param  int        $maxToleranceLoops  Número máximo de loops aguardando o tempo $delaytry
     
        @return  bool Resultado da operação
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

        // Movendo o arquivo com a função rename
        $result=\KeyClass\FileTree::renameFile($origpath, $destpath, $overwrite, $delaytry, $maxToleranceLoops);

        // Retornando o resultado da função
        return $result;
    }

    /**
        Copia um diretório recursivamente
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $src    Diretório de origem
        @param  string  $dst    Diretório de destino
     
        @return bool  Retorno da operação
    */
    public static function copyDirectory(string $src, string $dst) : bool {
        // Abrindo o diretório de origem
        $dir = opendir($src);

        // Criando o diretório de destino (se não existir)
        if (!is_dir($dst)) {
            mkdir($dst);
        }

        // Enquanto for possível ler um arquivo dentro do diretório de origem
        while(false !== ($file = readdir($dir))) {
            // Se não for o diretório . ou ..
            if (($file != '.') && ($file != '..')) {
                // Se é um diretório o item dentro do loop
                if (is_dir($src.DIRECTORY_SEPARATOR.$file)) {
                    // Chama a função novamente
                    \KeyClass\FileTree::copyDirectory($src.DIRECTORY_SEPARATOR.$file, $dst.DIRECTORY_SEPARATOR.$file);
                }

                // Se é um arquivo
                else {
                    // Copia o arquivo de origem para o diretório de destino
                    copy($src . DIRECTORY_SEPARATOR . $file,$dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        // Fecha o diretório de origem
        closedir($dir);

        // Retorna o sucesso
        return true;
    }

    /**
        Altera as permissões recursivamente de diretórios e arquivos
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $dir               Caminho do diretório
        @param  int     $dirPermissions    Novas permissões dos diretórios
        @param  int     $filePermissions   Novas permissões dos arquivo
     
        @return void Without return
    */
    public static function changePermissionRecursively(string $dir, int $dirPermissions, int $filePermissions) : void {
        // Abre o diretório alvo
        $dp = opendir($dir);

        // Enquanto existirem itens dentro do diretório
        while($file = readdir($dp)) {
           // Se é o diretório "." ou "..", ignora-os
            if (($file == ".") || ($file == ".."))
                continue;

            // Montando o caminho ao arquivo
            $fullPath = $dir.DIRECTORY_SEPARATOR.$file;

            // Se é um diretório
            if (is_dir($fullPath)) {
                // Altera a permissão do diretório
                chmod($fullPath, $dirPermissions);

                // Chama a funcão novamente
                \KeyClass\FileTree::changePermissionRecursively($fullPath, $dirPermissions, $filePermissions);
            }

            // Se é um arquivo
            else {
                // Altera a permissão do arquivo
                chmod($fullPath, $filePermissions);
            }
        }

        // Fecha o diretório alvo
        closedir($dp);
    }

    /**
        Realiza o download de um arquivo remoto para o servidor local
     
        @author Marcello Costa
     
        @package KeyClass\FileTree
     
        @param  string  $url    URL de origem do arquivo
        @param  string  $path   Diretório de destino do arquivo
     
        @return  bool Retorno da operação
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

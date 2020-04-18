<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta http-equiv='content-type' content='text/html; charset=<?php

    echo ENCODE; ?>'>
    <style>
        <?php
            
            $injectedCss = \Modules\InsiderFramework\Core\KernelSpace::getVariable('injectedCss', 'insiderFrameworkSystem');
            echo $injectedCss;
        ?>
    </style>
</head>
    <body>
        <div id="all" style='text-align: center;' unselectable='on' onselectstart='return false;' onmousedown='return false;'>
            <h2>O que você está fazendo aí ?</h2><br/>
            <h4>Algumas informações que seu navegador enviou para o site não está da forma como esperávamos. Talvez algum programa instalado tenha modificado algo em sua página...</h4>
            <img style='width: 15em;' src='<?php echo REQUESTED_URL . "/apps/sys/assets/img/error_attack.png"; ?>' />
        </div>
    </body>
</html>
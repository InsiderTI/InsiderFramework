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
            <h2>What are you doing there ?</h2><br/>
            <h4>Some information that your browser sent to the site is not in the way we expected. Perhaps an installed program has modified something on your page ...</h4>
            <img style='width: 15em;' src='<?php echo REQUESTED_URL . "/Apps/Sys/assets/img/error_attack.png"; ?>' />
        </div>
    </body>
</html>
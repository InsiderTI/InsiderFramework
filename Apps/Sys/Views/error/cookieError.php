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
            <h2>Where are you cookies ?!</h2><br/>
            <h3>For the website to function properly, cookies must be enabled on your browser</h3>
            <img style='width: 15em;' src='<?php echo REQUESTED_URL . "/Apps/Sys/assets/img/error_cookie.png"; ?>' />
        </div>
    </body>
</html>
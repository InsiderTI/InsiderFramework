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
            <h2>This page is experiencing some problems.</h2><br/>
            <h3>Come back later please.</h3>
            <img style='width: 15em;' src='<?php echo REQUESTED_URL . "/Apps/Sys/assets/img/500_user_error.png"; ?>' />
        </div>
    </body>
</html>
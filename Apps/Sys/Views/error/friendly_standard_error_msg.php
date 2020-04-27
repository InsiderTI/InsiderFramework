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
            <h2>Esta página está enfretando alguns problemas.</h2><br/>
            <h3>Volte mais tarde por favor.</h3>
            <img style='width: 15em;' src='<?php echo REQUESTED_URL . "/apps/sys/assets/img/error_frame_cartoon.png"; ?>' />
        </div>
    </body>
</html>
<!DOCTYPE html>
<html lang='pt-br'>
<head>
    <meta http-equiv='content-type' content='text/html; charset=<?php echo ENCODE; ?>'>
    <style>
        <?php
            global $kernelspace;
            $injectedCss = $kernelspace->getVariable('injectedCss', 'insiderFrameworkSystem');
            echo $injectedCss;
        ?>
    </style>
</head>
    <body>
        <div id="all" style='text-align: center;' unselectable='on' onselectstart='return false;' onmousedown='return false;'>
            <h2>Onde estão vocês cookies ?!</h2><br/>
            <h3>Para que o site funcione corretamente, é necessário que os cookies estejam ativados em seu navegador</h3>
            <img style='width: 15em;' src='<?php echo REQUESTED_URL."/packs/sys/assets/img/error_cookie.png"; ?>' />
        </div>
    </body>
</html>
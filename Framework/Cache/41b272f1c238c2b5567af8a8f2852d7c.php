<?php
                        \Modules\InsiderFramework\Sagacious\Lib\SgsView::InitializeViewCode('["5eae499780e83","5eae499780e95"]');
                    ?>
<!DOCTYPE HTML>
<html>
    <head>
        
        <link rel="apple-touch-icon" sizes="180x180" href="<?php

        echo REQUESTED_URL; ?>/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo REQUESTED_URL; ?>/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo REQUESTED_URL; ?>/favicon-16x16.png">
        <link rel="manifest" href="<?php echo REQUESTED_URL; ?>/site.webmanifest">
        <link rel="mask-icon" href="<?php echo REQUESTED_URL; ?>/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="theme-color" content="#ffffff">
        
        
        <meta charset="<?php echo ENCODE; ?>"/>
        
        
        <?php \Modules\InsiderFramework\Sagacious\Lib\SgsView::executeComponentFunction('titletest', 'renderComponent'); ?>
        
        
        <script type='text/javascript' src='<?php echo REQUESTED_URL; ?>/apps/sys/assets/js/moment.js/2.22.1/moment-with-locales.min.js'></script>
        <script>
            moment.locale('<?php echo LINGUAS; ?>');
        </script>

        
        <script type='text/javascript' src='<?php echo REQUESTED_URL; ?>/apps/sys/assets/js/js_only_insiderframework.js'></script>

        <?php
        if (DEBUG) {
            ?>
            
            <link rel="stylesheet" href='<?php echo REQUESTED_URL; ?>/apps/sys/assets/css/debug.css' />
            <?php
        }
        ?>

        <style>
            #mcontent{
                text-align: center;
                padding-top: 50px;
                font-weight: bold;
                font-size: 1.1em;
                font-family: Verdana;
            }
            #descapp{
                font-size: 20px;
            }
            
            #sidebar{
                border: 1px solid #000;
                width: 100px;
                height: 100%;
                display: none;
            }
            #topbar{
                border: 1px solid #000;
                height: 100px;
                width: 30px;
                display: none;
            }
        </style>
        <meta charset="<?php echo ENCODE; ?>"/>

        
        <?php 
                                
                                $injectedScripts = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                                    "injectedScripts",
                                    "insiderFrameworkSystem"
                                );
                                echo $injectedScripts; ?>
        <?php 
                                
                                $injectedCss = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                                    "injectedCss",
                                    "insiderFrameworkSystem"
                                );
                                echo $injectedCss; ?>

        
        
<style>
    .grey{
        color: #777;
    }
    #moreinfo{
        font-size: 12px;
    }
    #logo{
        width: 96px;
        margin-bottom: 10px;
    }
</style>

    </head>

    <body>
        <div id='sidebar'></div>
        <div id='topbar'></div>
        <div id='mcontent'>
            <div>
                
                
    <br/><br/>
    <span style="font-size: 14px;">
        <div style="width: 100%;">
            <div>
                <img id='logo' src='android-chrome-192x192.png' /><br/>
                <span class="grey">Welcome to</span> <h1>Insider Framework</h1>
            </div>
            <br/><br/>

            <br/>
            <span class="grey">The site is under construction</span>
	    <br/><br/>
            <span id='moreinfo'>
                Access 
		<a href="https://github.com/marcelloti/InsiderFramework">the official repository</a> 
		for more information
            <span>
        </div>
    </span>

    <?php \Modules\InsiderFramework\Sagacious\Lib\SgsView::executeComponentFunction('viewaux', 'renderComponent'); ?>

            </div>
        </div>

        
        <?php 
                                
                                $injectedHtml = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                                    "injectedHtml",
                                    "insiderFrameworkSystem"
                                );
                                echo $injectedHtml; ?>
        
        
        
    </body>
</html>
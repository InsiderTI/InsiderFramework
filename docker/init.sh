#!/bin/bash 

/etc/init.d/apache2 restart &

FILE=/init-running
FILE2=/init-complete

if [ ! -f "$FILE" ] && [ ! -f "$FILE2" ]; then
    touch $FILE;

    mv /var/www/insiderframework/Web/index.php /var/www/insiderframework/Web/index_tmp.php
    cp /tmp/welcomeInstall.php /var/www/insiderframework/Web/index.php
    
    # Changing chown and permissions
    CACHEDIR=/var/www/insiderframework/Framework/Cache
    if [ ! -d "$CACHEDIR" ]; then
        mkdir -p /var/www/insiderframework/Framework/Cache
    fi
    chown -R www-data:www-data /var/www/insiderframework
    chmod 770 -R /var/www/insiderframework/Framework

    # Installing php modules
    cd /var/www/insiderframework/Framework/Modules && composer install
    
    rm $FILE;
    rm /var/www/insiderframework/Web/index.php;
    rm /tmp/welcomeInstall.php
    mv /var/www/insiderframework/Web/index_tmp.php /var/www/insiderframework/Web/index.php
    touch $FILE2;
fi

exit 0;

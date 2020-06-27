#!/bin/bash 

/etc/init.d/apache2 restart &

FILE=/init-running
FILE2=/init-complete

if [ ! -f "$FILE" ] && [ ! -f "$FILE2" ]; then
    touch $FILE;
    
    indexwait="<html><head><meta http-equiv='refresh' content='50'></head><body><div style='text-align: center;'><img src='favicon.png'/><br/><h2>Installing system...</h2><br/>Wait until all dependencies are downloaded</div></body></html>";
    mv /var/www/insiderframework/Web/index.php /var/www/insiderframework/Web/index_tmp.php
    echo $indexwait > /var/www/insiderframework/Web/index.php
    
    # Changing chown and permissons
    CACHEDIR=/var/www/insiderframework/Framework/cache
    if [ ! -d "$CACHEDIR" ]; then
        mkdir -p /var/www/insiderframework/Framework/cache
    fi
    chown -R www-data:www-data /var/www/insiderframework
    chmod 770 -R /var/www/insiderframework/framework

    # Installing php modules
    cd /var/www/insiderframework/Framework/Modules && composer install
    
    rm $FILE;
    rm /var/www/insiderframework/Web/index.php;
    mv /var/www/insiderframework/Web/index_tmp.php /var/www/insiderframework/Web/index.php
    touch $FILE2;
fi

exit 0;

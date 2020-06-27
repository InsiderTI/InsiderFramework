FROM php:7.4-apache
MAINTAINER InsiderTI <contato@insiderti.com.br>

# **********************************************************************************************************
###### Updating mirrors ######
RUN apt-get update
# **********************************************************************************************************


# **********************************************************************************************************
####### Time Zone for Sao_Paulo/Brazil #######
# RUN cat '/usr/share/zoneinfo/America/Sao_Paulo' > /etc/localtime
# **********************************************************************************************************


# **********************************************************************************************************
####### Installing apt-utils ############
RUN apt-get install -y --no-install-recommends apt-utils
# **********************************************************************************************************

# **********************************************************************************************************
####### Installing php extensions ############
RUN docker-php-ext-install pdo pdo_mysql mysqli 

####### Xdebug ############
RUN pecl install xdebug-2.8.1 \
    && docker-php-ext-enable xdebug

RUN echo "xdebug.remote_enable=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.default_enable=on"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_handler=dbgp"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_host=localhost" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_port=9001" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_connect_back=on" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo ";xdebug.idekey=netbeans-xdebug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=docker" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.extended_info = 1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.remote_autostart=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
# **********************************************************************************************************

# **********************************************************************************************************
####### Vim and git ############
RUN apt-get install vim git -y
# **********************************************************************************************************


# **********************************************************************************************************
####### Locales for Brazil ##########
# RUN apt-get install -y locales && rm -rf /var/lib/apt/lists/* && localedef -i pt_BR -c -f UTF-8 -A /usr/share/locale/locale.alias pt_BR.UTF-8
# ENV LANG pt_BR.UTF-8
# **********************************************************************************************************


# **********************************************************************************************************
####### Composer #######
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');" && \
    chmod +x /usr/local/bin/composer
# **********************************************************************************************************

# System utils
RUN apt install zip -y

# **********************************************************************************************************
###### Vhost ######
ADD insiderframework.conf /etc/apache2/sites-available/
RUN a2ensite insiderframework; exit 0
RUN a2dissite 000-default; exit 0;

RUN a2enmod rewrite
RUN a2enmod headers
# **********************************************************************************************************

# Init script
ADD init.sh /usr/local/bin/init.sh
ADD welcomeInstall.php /tmp/welcomeInstall.php
RUN chmod +x /usr/local/bin/init.sh
ENTRYPOINT /usr/local/bin/init.sh && /bin/bash

FROM php:7.3-apache
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
####### Vim and git ############
RUN apt-get install vim git sudo -y
# **********************************************************************************************************


# **********************************************************************************************************
####### Locales for Brazil ##########
# RUN apt-get install -y locales && rm -rf /var/lib/apt/lists/* && localedef -i pt_BR -c -f UTF-8 -A /usr/share/locale/locale.alias pt_BR.UTF-8
# ENV LANG pt_BR.UTF-8
# **********************************************************************************************************


# **********************************************************************************************************
####### Composer #######
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');" && \
    chmod +x /usr/local/bin/composer
# **********************************************************************************************************

# System utils
RUN apt install zip -y

# **********************************************************************************************************
###### Vhost ######
ADD insiderframework-site.conf /etc/apache2/sites-available/
RUN a2ensite insiderframework-site; exit 0

RUN a2enmod rewrite
RUN a2enmod headers
# **********************************************************************************************************

# Init script
ADD init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh
ENTRYPOINT /usr/local/bin/init.sh && /bin/bash

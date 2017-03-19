#!/bin/bash

echo "Running installation steps for Verify via SMS"

apt-get -y upgrade
apt-get install apache2  php7.0 libapache2-mod-php7.0 curl  -y
/etc/init.d/apache2 restart

# PHP Composer setup 
cd /opt/

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

php -r "if (hash_file('SHA384', 'composer-setup.php') === '669656bab3166a7aff8a7506b8cb2d1c292f042046c5a994c43155c0be6190fa0355160742ab2e1c88d40d5be660b410') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

php composer-setup.php
php -r "unlink('composer-setup.php');"

# Twilio API SDK set up
cd /var/www/html
php /opt/composer.phar require twilio/sdk:5.6.0

# This source
wget https://raw.githubusercontent.com/teepeeoz/verifyviasms/master/web/index.php

wget https://raw.githubusercontent.com/teepeeoz/verifyviasms/master/web/settings.php

# Bootstrap
wget https://raw.githubusercontent.com/teepeeoz/verifyviasms/master/docs/bootstrap.zip

# unpack
unzip /var/www/html/bootstrap.zip

# delete file
rm /var/www/html/bootstrap.zip

# delete default Apache file
rm /var/www/html/index.html 

# Create location to store counter and tracking
mkdir -p /opt/www.data
chown www-data:www-data /opt/www.data

echo "Base installation executed. Please check for errors".
echo "Next step is to configure your TWILIO account details"
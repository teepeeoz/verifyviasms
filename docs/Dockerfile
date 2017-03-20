# Dockerfile
FROM nimmis/apache-php5

MAINTAINER teepeeoz <teepeeoz@gmail.com>

RUN apt-get -y upgrade 

RUN wget -O /var/www/html/index.php  https://raw.githubusercontent.com/teepeeoz/verifyviasms/master/web/index.php && wget -O /var/www/html/settings.php  https://raw.githubusercontent.com/teepeeoz/verifyviasms/master/web/settings.php && rm /var/www/html/index.html 

RUN cd /var/www/html && wget https://raw.githubusercontent.com/teepeeoz/verifyviasms/master/docs/bootstrap.zip && unzip /var/www/html/bootstrap.zip && rm /var/www/html/bootstrap.zip

RUN mkdir -p /opt/www.data && chown www-data:www-data /opt/www.data

#COPY settings.php /var/www.html/settings.php

EXPOSE 80
EXPOSE 443

RUN cd /var/www/html && composer require twilio/sdk:5.6.0

ENV TWILIO_SID=
ENV TWILIO_TOKEN=
ENV TWILIO_NUMBER=

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]

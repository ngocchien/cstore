## Install php-intl extension for php
---
```
yum --enablerepo=remi,remi-php55 install php-intl
```

## Create new directory for upload source code
---
```
1. cd /var/www/ && mkdir megavitaV2 && cd cstore && mkdir html logs
2. Upload source code into `/var/www/cstore/html/` directory
```

## Install new vhost
---
```
server {
       listen   80;
       server_name dev.cstore.com;
       access_log off;
       error_log /var/www/cstore/logs/error.log;
       root /var/www/cstore/html/public;
       index index.php;

       location / {
           if (!-f $request_filename) {
            rewrite ^(.*)$ /index.php?q=$1 last;
            break;
           }
       }

       location ~ /\.{deny  all;}

       location ~ \.php$ {
           include /etc/nginx/fastcgi_params;
           fastcgi_pass 127.0.0.1:9000;
           fastcgi_param APPLICATION_ENV dev;
           fastcgi_index index.php;
           fastcgi_param SCRIPT_FILENAME /var/www/cstore/html/public$fastcgi_script_name;
       }
}
server {
       listen   80;
       server_name dev.st.cstore.com;
       error_log /var/www/cstore/logs/static_error.log;
       root /var/www/cstore/html/static;
       add_header Pragma public;
       add_header Cache-Control "public, must-revalidate, proxy-revalidate";
       expires           max;
       access_log off;
       log_not_found     off;

       location ~ /\.{deny  all;}

       location ~* \.(eot|ttf|woff|otf|svg)$ {
         add_header "Access-Control-Allow-Origin" "*";
       }

       # Deny access to sensitive files.
       location ~ (\.inc\.php|\.tpl|\.sql|\.tpl\.php|\.db)$ {
         deny all;
       }
       location ~* /js {
         concat on;
         concat_max_files 30;
       }
       location ~* /css {
         concat on;
         concat_max_files 30;
       }
       location ~ /\.ht {
         deny  all;
       }
}
```
and then **restart Nginx Web Server** through the command

`service nginx restart`

## Downloading the Composer Executable and install library
---
```
1. cd /var/www/cstore/html/
2. curl -sS https://getcomposer.org/installer | php
3. php composer.phar install
```

## Sample Database
---
1. create new database named `cstore`
2. Import `megavitaV2.sql` database file in DATA directory

## modify hosts and access the website
---
1. xxx.xxx.xxx.xxx dev.cstore.vn dev.st.cstore.vn
2. Go to `http://dev.cstore.vn` for access the website
3. Login into backend with 

email: `admin@megavita.vn`

password : `123123`

# cstore
cstore

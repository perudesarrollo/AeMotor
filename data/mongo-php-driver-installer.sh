#!/bin/bash

wget http://pecl.php.net/get/mongo-1.4.0.tgz 
tar -xzf mongo-1.4.0.tgz
sh -c "cd mongo-1.4.0 && phpize && ./configure && sudo make install"
echo 'extension=mongo.so' >> $HOME/.phpenv/versions/$(phpenv version-name)/etc/php.ini
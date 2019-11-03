If on a MAC 

Download and install Vagrant https://www.vagrantup.com/downloads.html

Download and install VirtualBox https://www.virtualbox.org/wiki/Downloads

Open up terminal

cd into your favorite directory where you like to store your projects 

clone this repo

vagrant up

vagrant ssh

cd /var/www

composer install

yarn install

./node_modules/.bin/encore dev

./bin/console doctrine:schema:update --force

./bin/console hautelook:fixtures:load

add the following to your /etc/hosts file

192.168.56.109 p3.test
192.168.56.109 www.p3.test

That's it!

Visit www.p3.test or p3.test in your browser

to compile your assets 

vagrant ssh and cd into /var/www and run ./node_modules/.bin/encore dev



Make sure to setup a random string on Asset URLs so we can bust cache on prod server
# PROJECT SETUP

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

yarn dev

./bin/console doctrine:schema:update --force

./bin/console hautelook:fixtures:load

add the following to your /etc/hosts file

192.168.56.110 p3.test
192.168.56.110 www.p3.test

That's it!

Visit www.p3.test or p3.test in your browser



# TIPS:


### COMPILE ASSETS


vagrant ssh and cd into /var/www and run yarn dev

OR:

vagrant ssh and cd into /var/www and run yarn watch

### Markdown cheat-sheet 

https://github.com/tchapi/markdown-cheatsheet/blob/master/README.md

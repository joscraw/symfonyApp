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
    
    ./bin console doctrine:fixtures:load --append

    ./bin/console hautelook:fixtures:load --append

add the following to your /etc/hosts file

192.168.56.117 ibm-api-lead-integration.test
192.168.56.117 www.ibm-api-lead-integration.test

That's it!

Visit www.ppn.test or ppn.test in your browser



# TIPS:

### TREAT EACH ENTRY FILE IN WEBPACK.CONFIG.JSON LIKE IT'S OWN INDEPENDENT ENVIRONMENT

Check out this article for more details why:
https://symfonycasts.com/screencast/webpack-encore/single-runtime-chunk#play

**This isn't required but is recommended as best practice**

### COMPILE ASSETS


vagrant ssh and cd into /var/www and run yarn dev

OR:

vagrant ssh and cd into /var/www and run yarn watch

### Browser Compatibility


This project uses **Babel** for backwards compatibility for browsers 
This project uses **autoprefixer** to add vendor prefixes for browser support so you don't have to!

Example:

-webkit-box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);
  box-shadow: 0 1px 6px rgba(0, 0, 0, 0.175);

**(We do all that for you out of the box!!!!)**

This project also uses **browserlist** to make sure your CSS and JS is supported by browsers

This configuration is inside package.json:

    "browserslist": [
      "> 1%"
    ]

This says: I want to support all browsers that have at least 1% of the global browser usage

Tutorial here on how to configure this: https://github.com/browserslist/browserslist

Anytime you change the browserlist config you need to clear the cache so these changes take affect:

    rm -rf node_modules/.cache/babel-loader/
    
    yarn dev

If you use code or new JS features that are not supported in certain browsers, babel
is smart enough to download or include the reference to a polyfill for that code
to be used in the browsers supported in your **browserlist** config in package.json.
Pretty much this says: **Please, automatically import polyfills when you see that I'm using a new feature**

### Markdown cheat-sheet 

https://github.com/tchapi/markdown-cheatsheet/blob/master/README.md

### Production deploy tips

https://symfonycasts.com/screencast/webpack-encore/production#play


### API Tips

https://symfonycasts.com/screencast/symfony-security/entry-point#play
https://symfonycasts.com/screencast/symfony-rest2/validation-errors-response


### Serializing tips 
Whenever you get an Undefined index error then you should validate your schema
/bin/console doctrine:schema:validate
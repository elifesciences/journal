
Tips and Tricks for Windows users
---------------------------------

When using Windows to bypass the main errors we recommend to follow the next :

1. Make sure you use Windows Linux Subsystem (WSL).

  Guide to use WSL : https://adamtheautomator.com/windows-subsystem-for-linux/

2. Use the recomanded versions for PHP + exensions and Composer :

   * `PHP 7.3.33-7+ubuntu22.04.1+deb.sury.org+2 (cli) (built: Sep 29 2022 22:23:16) ( NTS )`
     
     How to install : [Guide 1](https://5balloons.info/how-to-install-php-v-7-3-on-ubuntu-20-04) / [Guide 2](https://computingforgeeks.com/how-to-install-php-ubuntu-debian/) 
     
   * `Composer version 1.10.26 2022-04-13 16:39:56`
     
     How to install : [Step 1](https://getcomposer.org/download/) & [Step 2](https://serverpilot.io/docs/how-to-downgrade-to-composer-version-1/)
 
 3. After you cloned the repo unitl you start running the commands from the [readme](https://github.com/elifesciences/journal/blob/develop/README.md) make sure that you use the correct line endings for the unit (.ci/phpunit) and behat (.ci/behat) scripts that run tests.
 
    * [Explanation](https://stackoverflow.com/a/71209401)
    * [Easy fix](https://stackoverflow.com/a/71731542)
   * IMPORTANT : Make sure you switch back the line endings of the files before you commit anything to github.
# WikiLeaks Email Dump
> Obtains, parses and statistically analyzes the recently dumped and available set of emails from Presidential Candidate, Hillary Clinton's email servers (made available on WikiLeaks) - all in a REST-ful JSON API server!

## Environment Details
This software currently relies on PHP 7.0.8 with the following default extensions:
[bcmath, bz2, calendar, cli, ctype, dom, fileinfo, filter, ipc, json, mbregex, mbstring, mhash, pcntl, pcre, pdo, phar, posix, readline, sockets, tokenizer, xml, curl, zip, openssl, opcache]

### Setting up a local PHP dev environment - [Complicated]
You can easily setup your own local development environment using [PHPBrew](https://github.com/phpbrew/phpbrew) along with [VirtPHP](https://github.com/virtphp/virtphp). PHPBrew allows you to easily manage and install multiple versions of PHP as well as PECL packages (basically, a version manager), whereas VirtPHP, just like Python's VirtualEnv, allows you to localize those PECL package and PHP installations to some local installation folder.

 * Simply follow the installation instructions for PHPBrew and VirtPHP
 * Install PHP 7.0.8 with PHPBrew and hop to that version - `phpbrew install 7.0.8 && phpbrew use 7.0.8`
 * Localize this environment with VirtPHP - `virtphp create <whateverEnvironmentName> --install-path=<virtualPhpLocation>`
 * Hop into that localized PHP interpreter - `source <virtualPhpLocation>/<whateverEnvironmentName>/bin/activate`
 
 Now, any PECL package and PHP extension installations are localized to this environment!
 
 When you want to deactivate this local environment, simply run `deactivate`. Notice that even if you switch back to the system version of PHP globally (with PHPBrew), when you re-activate your local environment, your version of PHP stays the same - localized PHP environment to the rescue!

### Using the provided Docker environment - [Simple]
A containerized version of the required PHP environment is provided to get you even more quickly up to speed. Simply run `docker-compose build` and `docker-compose up` while currently in the directory where this repository is and watch the magic go! Make sure to have [Docker](https://www.docker.com) and [Docker-Compose](https://docs.docker.com/compose/) installed!

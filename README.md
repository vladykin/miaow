# Miaow

Miaow is a reworked and improved version of the content management system (CMS) originally developed
for the http://rain.ifmo.ru/cat/ website.

It has been developed as an educational project back in 2006-2007.


## Requirements

  * PHP 5
  * PEAR with DB package (to be replaced with PDO soon, see [issue 11])
  * MySQL (other databases could work to, but we never tested)
  * PHPUnit 3, if you want to run the unit tests

Most Linux boxes already have all these installed, except PHPUnit. Refer to [PHPUnit installation instructions](http://www.phpunit.de/manual/current/en/installation.html).

On Windows you may use one of those all-on-one web-server distributions, for example [VertrigoServ](http://vertrigo.sourceforge.net/). In this case PEAR packages have to be installed manually.

## Installing Miaow

  1. Copy `config.example` to `config.php` and edit the latter file to match your environment.
  2. `SITE_URL`, `SKIN`, `DB_CHARSET`, `DB_CONNECT_STRING` must be set in `config.php`. Specified database and user must exist.
  3. Run `install.php` by typing `SITE_URL/install.php` in your browser. If no errors show up, Miaow is installed successfully.
  4. Verify installation by running tests: `cd tests && for f in *.php; do phpunit $f; done`
  5. Open `SITE_URL/index.php` in your browser. This is your website's front page.

## Troubleshooting

When nothing works and even the tests fail...

First of all, check DB_CHARSET value you set in config.php.

  * It must be the name of charset in which PHP gets data from database.
  * It must be something that iconv function can understand.

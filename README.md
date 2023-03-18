# kjg Ticketing

This project is a WordPress Plugin.
It provides a ticketing system for the KjG theater, where theater tickets can be managed for the upcoming show.

More detailed information about requirements etc. is written in `kjg-ticketing.php`.

# Installation (for development)

## Install and run a WordPress Server on your machine

1. Install and run a software bundle with Apache, MySQL and PHP (like LAMP or XAMPP) on your machine.
2. In MySQL, create a new user named `wp_user` and a database named `wordpress` and grant the `wp_user` all permissions
   on that database.
3. Download and install WordPress under `server_root/wordpress`. Use the WordPress installation guide for this.
4. Ensure that WordPress has write permissions to its files. If you are prompted by WordPress to insert FTP credentials,
   there is a problem.

## Modify it to be a local copy of the kjg Theater webserver

1. Remove all default sites
2. Install the `Twenty Fourteen` Theme, which is used on the kjg Theater Website.
3. Go to the real kjg Theater Website and on the `Tools -> data export` page in the admin dashboard, download an export
   of the whole content.
4. On your local WordPress Server, go to `Tools -> data import -> WordPress` on the admin dashboard and upload the
   export XML file.

## Install this plugin

Open a terminal and go to the root of your WordPress installation, which should be `server_root/wordpress` if you
followed the previous steps. There, execute

```
git clone https://github.com/Schadowpen/kjg-ticketing.git ./wp-content/plugins/kjg-ticketing
```

Then the plugin should already occur on the WordPress Plugins Dashboard, where you can activate and deactivate it.

## IDE Configuration

When using PHPStorm as IDE, please refer
to [this page](https://www.jetbrains.com/help/phpstorm/using-wordpress-content-management-system.html).

## Postman

Postman supports to automatically sync your changes to a Postman API to GitHub.
For more information about this,
click [here](https://learning.postman.com/docs/designing-and-developing-your-api/versioning-an-api/using-external-git-repo/)

For this Repository, a separate `postman` branch exists to which should be the main branch for all updates to the
Postman API. The Postman API is stored in the `postman` folder (which is by the way the default folder where Postman
stores APIs).

A logged in WordPress user is necessary for all requests, which is authenticated via Cookies.
So ensure to get all necessary Cookies from your Browser.

For API requests, a Postman collection exists called `kjg-ticketing API`.
It includes all public AJAX requests as well as all download requests.

# Create a release

## Set release version

In the `kjg-ticketing.php` file, change the version number at two places:

1. Inside the header comment
2. On the command `define('KJG_TICKETING_VERSION', '0.0.0');`

## Create a .zip-file containing the plugin

In the plugin directory, execute following command:

````shell
tar -a -c -f kjg-ticketing.zip  admin includes languages lib public CHANGELOG.md index.php kjg-ticketing.php LICENSE
````

Then you should find a `kjg-ticketing.zip` file with the correct content.

## Upload a released version to a WebServer

On the `Plugins -> install` page in the admin dashboard, there is the possibility to upload a plugin as a .zip-file.
Pass the .zip-file from the previous step here.

## Upload a released version to the WordPress archive

TODO

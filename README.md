# kjg Ticketing
This project is a WordPress Plugin.
It provides a ticketing system for the KjG theater, where theater tickets can be managed for the upcoming show.

# Installation (for development)
TODO

## Install and run a Wordpress Server on your machine
1. Install and run a software bundle with Apache, MySQL and PHP (like LAMP or XAMPP) on your machine.
2. In MySQL, create a new user named `wp_user` and a database named `wordpress` and grant the `wp_user` all permissions on that database.
3. Download and install Wordpress under `server_root/wordpress`. Use the WordPress installation guide for this.
4. Ensure that WordPress has write access to its files. If you are prompted by Wordpress to insert FTP credentials, there is a problem.

## Modify it to be a local copy of the kjg Theater webserver
1. Remove all default sites
2. Install the `Twenty Fourteen` Theme, which is used on the kjg Theater Website.
3. Go to the real kjg Theater Website and on the `Tools -> data export` page in the admin dashboard, download an export of the whole content.
4. On your local Wordpress Server, go to `Tools -> data import -> WordPress` on the admin dashboard and upload the export XML file.

## Install this plugin
Open a terminal and go to the root of your WordPress installation, which should be `server_root/wordpress` if you followed the previous steps. There, execute
```
git clone https://github.com/Schadowpen/kjg-ticketing.git ./wp-content/plugins/kjg-ticketing
```
Then the plugin should already occur on the WordPress Plugins Dashboard, where you can activate and deactivate it.

## IDE Configuration
When using PHPStorm as IDE, please refer to [this page](https://www.jetbrains.com/help/phpstorm/using-wordpress-content-management-system.html).


# Create a release
TODO

# Upload a released version to a WebServer
TODO

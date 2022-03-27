# Laravel on Replit

> Important: This project uses the built-in PHP webserver. This is not advised by the Laravel project maintainers. Additional code for this to work in Replit was placed in Providers\AppServiceProvider.

> This project is not meant for production deployments or to be publicly accessible, however it can be. If that's the case I recommend modifying the start script to start Laravel Octane rather than the built-in server.

> Nix is still in beta and subject to changes by Replit.

This project serves as a template to run a full-stack development Laravel application on Replit using Nix, PHP 8, MariaDB, and Redis. This project should support all Laravel addons including Jetstream, Horizon, Sanctum, and more.

# Get Started
Before doing anything, some configuring is required first. I will go over the directory structure first.

* `src` - The directory containing the source of the Laravel application.
  * `src/.env` - File containing Laravel environment variables. Follow the whole guide, as this will be moved to Replit secrets later.


* `system` - The directory containing all configuration files and sockets.
  * `system/config` - The directory containing configuration files for MariaDB, Redis, etc.
  * `system/(package name)` - The directory containing the sockets and data for each package. This shouldn't be modified.


Because some files do not support dynamic slugs, specific files needs to be altered to your project.

Make sure "Show config files" is enabled (three vertical dots on Files)

Inside ``my.cnf``, replace all occurrences with your slug:
```
pid-file	= /home/runner/YOUR_SLUG/system/mariadb/mysqld.pid
socket		= /home/runner/YOUR_SLUG/system/mariadb/mysqld.sock
datadir		= /home/runner/YOUR_SLUG/system/mariadb
lc-messages-dir	= /home/runner/YOUR_SLUG/system/mariadb/usr

```

That should be all for configuring everything. Now, wait until the Nix environment starts up. This might take a few minutes.

Next up is configuring your application URL in Laravel. In the `src` directory, open the `.env` file, and change the APP_URL to your where your Replit will live. By default it's ``https://YOUR_SLUG.YOUR_USERNAME.repl.co``. This is **extremely important**, otherwise Laravel will not know where to direct requests to your application.

If you are going to be hosting a public/production app in Replit, it is highly advisable to turn APP_DEBUG to false. Leaving debug mode on can expose all your environment variables to an end user in the event of an exception.

Also, it might be wise to update your packages before starting. In the "Shell" tab, cd into the ``src`` directory and run ``composer update`` to get the latest version of everything. I will periodically update this with the latest packages, but it is good practice to do this.



# Starting Laravel
Provided everything is configured properly, that should be all there is to it. All you have to do now is click the "Run" button and watch your Laravel app spring to life! You should see the generic welcome screen in Laravel.

This template contains a plain Laravel project, ready for you to use. **But wait we're not done yet!** There's still some more configuring to do if you want proper functionality and security.

**Optional but highly recommended -** Regenerate your application key by opening the "Shell" tab in Replit and running the following commands:
```sh
cd src
php artisan key:generate
```
The default application key is placeholder and should not be used in your project, as it is used for functions like encryption, etc.

# Creating a Database
> Even if the repl is private, it is not recommended to store sensitive data. For more production-like tasks, a hosted database being provided elsewhere is likely a good idea.

Next up is creating the MySQL database. Open the "Shell" tab in Replit, and execute mysql.sh using ``./mysql.sh``. You may or may not have to grant executable permissions via chmod first (``chmod +x mysql.sh``).

You should then be dropped into the MariaDB shell. To create a database, execute the following commands:
```sql
CREATE DATABASE yourdbname;
USE yourdbname;
```

It should be as simple as that to create a database, but it's no good if you don't have a user! Let's go ahead and do that now. Make sure you choose a strong password (ideally random). Next, you'll need to grant privileges to this user on the database.
```sql
CREATE USER 'username'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON yourdbname.* TO 'username'@'%';
```

You should be done here! Type exit in the command line to exit MariaDB.

Next up, go to your ``src/.env`` file and change the following as mentioned:
```
DB_DATABASE=yourdbname
DB_USERNAME=username
DB_PASSWORD=password
```

Your Laravel app should now be successfully connected to your database! Now you can run migrations and do other database operations.


# Installing Addons
Installing addons via Composer is the same way you would always do it.
```sh
cd src
composer require (package)
```

# Moving to Replit Secrets
> This is optional, but highly recommended for security.

If you've made it this far, you might have noticed something. Our environment variables are publicly accessible in ``src/.env``! This is not good for a handful of reasons on Replit (anyone can see these if your repl is public!) Let's change that now.

In the root directory of your project there is a ``.env.example.json`` file. In that file contains the sample environment variables in JSON format ready for you. Copy this JSON file and open it in a local text editor (DO NOT edit this in your replit project.) Now it's just a matter of copying over the environment variables you have set (app_url, debug, database) over.

After you have copied over your environment variables and they are in valid JSON format, open the "Secrets" tab and Open the raw editor. Copy the entire JSON, paste it in and click save. You should then see all of your secrets be put here.

After this, delete the ``src/.env`` file and stop your project. In the Replit shell, type ``kill 1`` to reload the Nix environment. 

If you still don't see your secrets or get an exception, try opening one, click Save then reload the Nix environment again. Check troubleshooting section for more info.

# Working in Local Environments (Git)

Working in a local Git environment should be pretty straightforward. [Reference the Laravel documentation](https://laravel.com/docs/8.x#your-first-laravel-project) for setting up a developer environment on your system.

On your local machine, enter the src directory, create a .env file with appropriate values, and run ```composer update```, followed by running Sail, Octane, or ```php artisan serve```.

Any changes pulled from GitHub should take effect immediately. Make sure to add any new environment variables inside Secrets.

# Troubleshooting
* Repl will not stop
  * Try running ``pkill mysqld`` in the Shell.
* The app does not see my secrets (Server Error/exception on app start).
  * In the Replit shell, check to see if your secrets are indeed loaded by typing ``echo $APP_ENV``.
      * If it is blank, make sure this exists, then try typing ``kill 1`` in the Shell to reload the Nix environment.





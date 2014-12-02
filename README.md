# Library Status

----

Library Status is an online tool for publicly displaying the current status of your library systems.

Libraries rely on a number of different online and other software tools to provide services to our patrons. Often those services go down or have problems for reasons beyond our control, and sharing this information can be difficult. Sending out email blasts for every database maintenance update would quickly have your staff inundated, and doesn't help patrons know when to expect problems. We deicded we needed a centralized place to show the current status of all of our tools so that all of our users, staff and patrons alike, could be informed.

We added the ability to subscribe to updated with RSS or email (using Feedburner), and included the ability to report new problems right from the interface. By making the tool visible on the public web and making it the place to go to report problems, we make sure that everyone has a chance to see the current health of our systems.

![GVSU Library Status App](resources/img/status_app.png)

You can view GVSU's Library Status app live at [http://gvsu.edu/library/status](http://gvsu.edu/library/status).

### Features

----

* Publicly display the current status of an unlimited number of tools or systems
* Show a detailed record of status updates for each problem
* Easily allow users to subscribe to updates through RSS or Email
* Integrated problem reporting interface that will email new tickets to any email address(es)

### Installation

----

Library Status requires a server running PHP 5.4 or up and MySQL. If you don't have a server like this avalable, a basic hosting package from [Dreamhost](http://dreamhost.com) or something similar would work.

There are two ways to install Library Status: with or without git.

#### Download the files from Github, upload to your server

1. Download the [zip file of Library Status](https://github.com/gvsulib/library-Status/archive/master.zip), and unzip it somewhere on your machine.
2. Open up the file resources/secret/config.sample.php in your favorite text editor (not Word!)
3. First, fill in your database connection details. (For host, you might need to consult your host's documentation. It's often just localhost, but not always.) For database table, put "status". You'll need the username and password of the username that has permissions to connect to the database. This is often the same as the username and password you use to log into the server, but not always. If in doubt, check the help section at your web host. Next, you can set the following parameters to help customize the tool for your institution:
    * *$library_name*: This is the name of your library, as it will display at the top of the app (and in the &lt;title&gt; element).
    * *$header_image*: The URL of the logo you'd like to appear in the upper left hand corner.
    * *$header_url*: The URL you'd like the logo to link to.
    * *$banner_color*: The hex value of the background color you'd like the banner to be.
    * *$rss_url*: The URL of the RSS feed folks will subscribe to. The Library status app has it's own RSS feed, but you may want to route that through something like [feedburner](http://feedburner.com) to gather stats.
    * *$email_subscription_url*: The URL for folks to subscribe via email. We use feedburner for this service.
    * *$to_email*: The email you'd like the problem reports to be sent to. (We have ours go to Asana, our project management app.) If you want them to go to multiple emails, separate them with commas.
    * *$from_email*: The email address your problem reports will be sent from.
    * *$email_subject*: The subject line of your problem report email address.
    * *$use_native_login: Use native login or not. If false, $non_native_login_url must be set.
    * *$non_native_login_url: URL to redirect to the non-native login system of your choosing. Be sure that your login system sets a $_SESSION['username'] variable. This is how the app knows whether you are logged in or not. There are ways to do this [with EZProxy](http://matthew.reidsrow.com/articles/20). (If you have experience connecting apps to campus LDAP systems, consider contributing that feature!)
4. Save the file as resources/secret/config.php.
5. Now upload all of the files to your server, where you want them to live.
6. On your server, open up phpMyAdmin or the like, and create a database called status.
7. Import the file status.sql into your new database, and watch it magically populate the tables.
8. To get started, you need to populate some of the database tables. You'll need to add at least one user who can post and update statuses, and you'll need to add your list of systems. Here are the specific tables and fields to work on:
    * Table **systems**. We have added a few sample systems so you can see how this works. For each system you would like to show, add an entry with:
      * *system_id* will be automatically updated by the database. Just leave it blank.
      * *system_name*: the display name of the system. (e.g. Library catalog)
      * *system_url*: the URL where you can find the system, if it's online (optional)
    * Table **user**. You'll need at least one user who can log in and update the statuses.
      * *user_id* will be automatically updated by the database. Just leave it blank.
      * *user_username* must match whatever username your authentication system will pass to the status app.
      * *password can be NULL, as it is only used with the native login system.
      * *user_fn* is the user's first name.
      * *user_ln* is the user's last name.
      * *user_email* is the user's email address.
      * *user_delete* is a boolean value. 0 is false (not deleted). Someday we will add the ability to "delete" users without removing their records for the database. You can do this manually by setting this value to 1 (true).
      * *user_notifications* is a boolean value for toggling notifications on or off. It is currently unused.
      * *user_access* is a way to set different access levels for users. Currently, set users at 9, which is full access. In a future release we will add user restrictions.
9. Now load the status app in your browser and test it out!

#### With Git/Github

I'm assuming here that you have a server somewhere that not only has PHP and MySQL, but also Git. I also assume you know a bit about ssh, and that you have [Git ssh keys](https://help.github.com/articles/generating-ssh-keys) set up on your server. If that doesn't make sense, you can shoot me an email or maybe try the other installation method.

1. Fork the [Library Status repository](http://github.com/gvsulib/library-Status), so that you have a version in your own Github account. (If you don't have an account, you'll need to sign up for one. It's free!). If "forking" doesn't make any sense to you, don't worry! Github has excellent online help, and [they will walk you through it](https://help.github.com/articles/fork-a-repo).
2. Now you'll want to ssh in to your server, and navigate to the parent directory of where you want the status app to live.
3.  Clone your new fork of the library status app, replacing YOUR_USERNAME with your Git username:  

    `git clone git@github.com:YOUR_USERNAME/library-Status.git`

4. Now you'll need to create the MySQL database. The [status.sql](https://github.com/gvsulib/library-Status/blob/master/status.sql) file can be imported right into something like phpMyAdmin once you create the database, or you can do it manually from the command line. We're working hard on making this process easy with a GUI installer.
5. To get started, you need to populate some of the database tables. You'll need to add at least one user who can post and update statuses, and you'll need to add your list of systems. Here are the specific tables and fields to work on:
    * Table **systems**. We have added a few sample systems so you can see how this works. For each system you would like to show, add an entry with:
        * *system_id* will be automatically updated by the database. Just leave it blank.
        * *system_name*: the display name of the system. (e.g. Library catalog)
        * *system_url*: the URL where you can find the system, if it's online (optional)
    * Table **user**. You'll need at least one user who can log in and update the statuses.
        * *user_id* will be automatically updated by the database. Just leave it blank.
        * *user_username* must match whatever username your authentication system will pass to the status app.
        * *password can be NULL, as it is only used with the native login system.
        * *user_fn* is the user's first name.
        * *user_ln* is the user's last name.
        * *user_email* is the user's email address.
        * *user_delete* is a boolean value. 0 is false (not deleted). Someday we will add the ability to "delete" users without removing their records for the database. You can do this manually by setting this value to 1 (true).
        * *user_notifications* is a boolean value for toggling notifications on or off. It is currently unused.
        * *user_access* is a way to set different access levels for users. Currently, set users at 9, which is full access. In a future release we will add user restrictions.
6. Now you need to update the configuration of the app. In your ssh window, navigate to `resources/secret/config.sample.php`. You'll want to edit this file, so open it up in your editor of choice, for example:  

    `nano config.sample.php`

    First, fill in your database connection details. (For host, you might need to consult your host's documentation. It's often just localhost, but not always.) Next, you can set the following parameters to help customize the tool for your institution:

    * *$library_name*: This is the name of your library, as it will display at the top of the app (and in the &lt;title&gt; element).
    * *$header_image*: The URL of the logo you'd like to appear in the upper left hand corner.
    * *$header_url*: The URL you'd like the logo to link to.
    * *$banner_color*: The hex value of the background color you'd like the banner to be.
    * *$rss_url*: The URL of the RSS feed folks will subscribe to. The Library status app has it's own RSS feed, but you may want to route that through something like [feedburner](http://feedburner.com) to gather stats.
    * *$email_subscription_url*: The URL for folks to subscribe via email. We use feedburner for this service.
    * *$to_email*: The email you'd like the problem reports to be sent to. (We have ours go to Asana, our project management app.) If you want them to go to multiple emails, separate them with commas.
    * *$from_email*: The email address your problem reports will be sent from.
    * *$email_subject*: The subject line of your problem report email address.
    * *$use_native_login: Use native login or not. If false, $non_native_login_url must be set.
    * *$non_native_login_url: URL to redirect to the non-native login system of your choosing. Be sure that your login system sets a $_SESSION['username'] variable. This is how the app knows whether you are logged in or not. There are ways to do this [with EZProxy](http://matthew.reidsrow.com/articles/20). (If you have experience connecting apps to campus LDAP systems, consider contributing that feature!)
7. Save the file as config.php.
8. Now your setup is done! Test out your app by visiting it in your browser.

### Further Configuration

----

Library Status also provides an interface for sending along problem reports to any email address. By default, names and email addresses are optional. (The form does include a honey pot and some robust naughtiness checking before sending emails, so spam is minimized if not eliminated.) If you want to require email address and/or names when folks submit tickets, you can do so with the following steps:

1. Open resources/php/functions.php in your favorite text editor, and uncomment the relevant line(s) for making the fields required: line 23 for email addresses, line 30 for names.
2. In feedback.php, edit the form fields to have the attribute `required="required"`, which will tell modern browsers to require the field. (This form is your submission form is JavaScript fails or is not enabled by the user.)
3. Open up index.php and look for the small bit of jQuery code at the end of the file (line 586). Here you'll also need to add the `required="required"` attribute.
4. You'll probably want to add some client-side form validation, since form contents are not currently repopulated if they fail server validation. While modern browsers will work well with the `required` attribute, not all browsers support this.

We'll be making the failed submission recovery more user-friendly in future releases. Feel free to help our if you can!

### Roadmap  

----

Here are features we have planned for future releases. If you'd like to help in developing these features, or would like to suggest other improvements, let us know in the [issue tracker](http://github.com/gvsulib/library-Status/issues).

* UI for setting up the MySQL database
* Native log-in system for folks who do not have CAS or the ability to integrate their existing login system with the app. (Perhaps [using EZProxy as an option](http://matthew.reidsrow.com/articles/20) as well?)
* The ability to delete a status or update
* Add weekly reminders for open tickets
* Better scheduling of resolutions for those defined maintenance periods (e.g. Lexis Nexis will be offline for maintenance from 12-2am)
* Add help tooltips for each system to better help patrons understand what they are.
* Beef up accessibility with better ARIA support, more clear tabindexes, and better keyboard navigation
* Build a UI for administering users and systems

### Support

----

The authors are happy to help you get Library Status up and running. You can [post a request in the issue tracker](http://github.com/gvsulib/library-Status/issues), or [shoot Matthew an email](mailto:reidsmam@gvsu.edu) or a [tweet](http://twitter.com/mreidsma).


### Contribute

----

We welcome contributions to the library status app. If you are a developer, you can contribute code. If you are not, you can always report bugs through the issue tracker, or help us improve documentation or localization.

* Issue Tracker: [http://github.com/gvsulib/library-Status/issues](http://github.com/gvsulib/library-Status/issues)
* Source Code: [http://github.com/gvsulib/library-Status](http://github.com/gvsulib/library-Status)


### License  

----

This tool is copyright 2014 Grand Valley State University Libraries.

This tool is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This tool is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this tool. If not, see [http://www.gnu.org/licenses](http://www.gnu.org/licenses/).

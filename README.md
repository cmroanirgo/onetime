# Onetime Messaging System
A uber lightweight one time message submission system for php. Great for sending passwords securely.
Yes, another one.

* No database required! (Flat files are used)
* This project doesn't require composer or use autoloaders. It's old school and small.
* Messages expire after a period of time
* All messages are deleted, the contents are never kept once they're read.
* No records of deleted messages are stored except (optionally):
  * The ID of the message
  * The IP of the person who read the message (for security validation requests)
  * The time the message expired or was read

* If password protection is used and the password is incorrect a number of times, the message is also deleted.


# Requirements

* PHP (5 or 7+)
* Apache or similar
* php mail set up and configured
* php mcrypt. (Note that mcrypt itself isn't used as it's now considered legacy)
* A fresh subdomain. The `.htaccess` doesn't play well in sub diretories.

# Installation

It's assumed you'll want the messages stored outside the publc folder of your webserver. If you can't do that, then this project probably isn't for you!

* Download/clone the repo.
* Copy/rename `private/includes/config.sample.php` to `private/includes/config.php` and edit a couple of settings.
* If your `private` folder isn't beside your `public_html`, you'll need to edit `index.php` and change `OT_SRC_PATH` to point to the correct location.
* Profit!





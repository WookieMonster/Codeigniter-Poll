# Codeigniter Poll Library

## Key features:

* Supports multiple polls
* Prevents multiple votes through logging ip address to the database
* Supports multiple options
* Maximum and minimum number of options can be defined in config
* Multiple votes allowed or not can be set in config
* Interval between votes can be set in config
* Close and open polls
* Included jQuery for handling variable poll option inputs

## Installation:

1. Run database.sql to generate required tables - note that the database engine should be InnoDB (not MyISAM)
2. Upload the files included to your server
3. Edit config/poll.php (see comments)
4. Visit <yourserver>/ci_installation/poll to view sample application

Tested with CI 2.1.3

License: [http://opensource.org/licenses/gpl-license.php](http://opensource.org/licenses/gpl-license.php) GNU Public License
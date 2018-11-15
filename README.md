# tv-serve
Serves TV recordings from DVB server.

Note to future developers: Ridiculous color names preferred.

2018-06-06
Now with ![SubStation Alpha](SSASuport.png "SubStation Alpha") support!

Note to future developers: As a rule, if you come accross an unsupported SSA or ASS tag in broadcast television, you must add support for it.

## Set Up
1. `./setup.sh` - Creates sqlite database from setup.sql file.
2. `php setup.php` - Retrieves first time MD5s, listings, etc... from Schedules Direct
3. Figure out the larger remainder of stuff yourself.

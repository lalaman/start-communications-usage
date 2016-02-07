# Start Communications Internet Usage Tracker
This simple tool uses the Start Communications API to grab your latest usage statistics and puts it into a (somewhat) nice UI.

# USAGE

At the top of index.php, you will find:

```php
/*	Configure this first! Update the $usage_url to your own:
*	URL should look something like: www.start.ca/support/usage/api?key=123456ABCDEFG12345ABCDEF
*	For more information, visit http://www.start.ca/support/usage/api */
$usage_url = 'https://your-own-start-communications-usage-api-url';

//	Set your timezone
date_default_timezone_set("America/New_York");

//	Set your monthly limit in GB. The default below is 200GB.
$limit = 200;

//	Show daily usage - Adds a section below to view your daily usage (only works on your current Start Communications network)
$show_daily_usage = false;
```

Plug in your own Start Communications usage API url, timezone, and monthly limit to view your current internet usage this month.

# Note
This tool is only for those who have a limited amount of bandwidth every month. Those who have unlimited and still want to check their usage, go to your Start Communications page to view this information.

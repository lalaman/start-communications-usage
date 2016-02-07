<?php
	
/*	*********************************************************************
	* Configure here first
	*********************************************************************/
	//Configure this first! Update the $usage_url to your own:
	//URL should look something like: www.start.ca/support/usage/api?key=123456ABCDEFG12345ABCDEF
	//For more information, visit http://www.start.ca/support/usage/api
	$usage_url = "";
	
	//Set your timezone
	date_default_timezone_set("America/New_York");
	
	//Set your monthly limit in GB. The default below is 200GB.
	$limit = 200;
	
	//Show daily usage - Adds a section below to view your daily usage (only works on your current Start Communications network)
	$show_daily_usage = false;
	
/*	**********************************************************************
	* End of configuration
	**********************************************************************/
	
	if ($usage_url == "") {
		die("Please enter your Start Communications URL which can be found <a href='http://www.start.ca/support/usage/api'>here</a>");
	}
	
	$monthly_limit = "";
	$bandwidth_used = "";
	$bandwidth_remaining = "";
	$bandwidth_remaining_percentage = 0.0;
	
	function get_string_between($string, $start, $end) {
		$pos = stripos($string, $start);
		$str = substr($string, $pos);
		$str_two = substr($str, strlen($start));
		$second_pos = stripos($str_two, $end);
		$str_three = substr($str_two, 0, $second_pos);
		$unit = trim($str_three); // remove whitespaces
		return $unit;
	}
	
	function get_usage($url) {
		
		global $limit;
		global $monthly_limit;
		global $bandwidth_used;
		global $bandwidth_remaining;
		global $bandwidth_remaining_percentage;
		
		$usage_stats = @file_get_contents($url);

		if ($usage_stats === "false") {
			get_usage($url);
		} else {
			
            $limit = $limit * 1000000000.0;
            
			$total = get_string_between($usage_stats, "<total>", "</total>");
			$total = floatval(get_string_between($total, "<download>", "</download>"));
			
			$used = get_string_between($usage_stats, "<used>", "</used>");
			$used = floatval(get_string_between($used, "<download>", "</download>"));		

			$free = get_string_between($usage_stats, "<grace>", "</grace>");
			$free = floatval(get_string_between($free, "<download>", "</download>"));

			$monthly_limit =  number_format($limit / 1000000000, 2); //. " GB";
			$bandwidth_used = number_format($used / 1000000000, 2); //. " GB";
			$bandwidth_remaining =  number_format(($limit - $used) / 1000000000, 2); //. " GB";
			$bandwidth_remaining_percentage = number_format($used * 100.0/ $limit, 1);
		}
	}
	
	function get_estimated_usage() {
		//Get how much you have used so far
		global $bandwidth_used;		
		
		//Get number of days passed in month
		$current_day = date('j');
		
		//Divide how much you have used so far by the number of days passed in the month
		$average_bandwidth_per_day = $bandwidth_used / $current_day * 1.0;
		
		//With the result above, multiply it by the number of days in the given month		
		$days_in_current_month = date('t');
		$estimated_usage = number_format($average_bandwidth_per_day * $days_in_current_month, 2);
		
		return $estimated_usage . " GB";
	}
	
	function days_remaining() {
		$days_in_current_month = date('t');
		$current_day = date('j');
		$days_until_renewal = $days_in_current_month - $current_day + 1;
		
		return $days_until_renewal;
	}
	
	function get_balanced_usage() {
		global $monthly_limit;
		global $bandwidth_used;
		
		$days_in_current_month = date('t');
		$current_day = date('j');
		
		$daily_bandwidth = $monthly_limit * 1.0 / $days_in_current_month;
		$max_bandwidth_today = $daily_bandwidth * $current_day;

		if ($bandwidth_used > $monthly_limit) {	
			return "<span class='overage'>Over Usage</span>";
		} else if ($bandwidth_used < $max_bandwidth_today - 5.0) {
			return "<span class='good-standing'>Excellent</span>";
		} else if ($bandwidth_used >= $max_bandwidth_today - 5.0 && $bandwidth_used <= $max_bandwidth_today + 5.0) {
			return "<span class='moderate-standing'>Moderate</span>";
		} else {
			return "<span class='bad-standing'>Poor</span>";
		}
	}
	
	function return_daily_url() {
		$current_month = date("n");
		$current_year = date("Y");
		
		return "https://www.start.ca/support/usage/?year=" . $current_year . "&month=" . $current_month;
	}
	
	function get_daily_usage() {
		
		$usage_stats = @file_get_contents(return_daily_url());
		
		if ($usage_stats === false) {
			get_daily_usage();
		} else {
			return get_string_between($usage_stats, "<table>", "</table>");
		}
	}

	get_usage($usage_url);
	
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Start Communications Usage</title>
		<link rel="icon" type="image/png" href="favicon.png">
		<link rel="stylesheet" type="text/css" href="style.css">
		<link href='http://fonts.googleapis.com/css?family=Roboto:400,300,700,900' rel='stylesheet' type='text/css'>
	</head>
	<body>
		<div class="container">
			<div class="start-title">Start Communications Usage</div>
			<div class="usage-stats">
				<strong>Monthly Limit:</strong> <?php echo $monthly_limit . " GB"; ?><br /><br />
				
				<strong>Bandwidth Remaining:</strong> <?php echo $bandwidth_remaining . " GB"; ?><br /><br />
				
				<strong>Days Until Renewal:</strong> <?php echo days_remaining(); ?><br /><br />
				
				<strong>Estimated Usage:</strong> <?php echo get_estimated_usage(); ?><br /><br />
				
				<strong>Usage Status:</strong> <?php echo get_balanced_usage(); ?>
			</div>
			<hr>
			<strong>Bandwidth Used:</strong> <?php echo $bandwidth_used  . " GB (" . $bandwidth_remaining_percentage . "% used)"; ?><br />
			<div class="bar">
				<div class="percentage" style="width: <?php echo $bandwidth_remaining_percentage; ?>%;">&nbsp;</div>
			</div>
		</div>
		<?php if ($show_daily_usage) { ?>
			<div>
				<table class="daily-usage-container"> 
					<tbody>
						<?php echo get_daily_usage(); ?>
						<tr height="60px">
							<td valign="bottom" colspan="2"><a href="<?php echo return_daily_url(); ?>" target="_blank">Check daily usage</a></td>
							<td colspan="2">&nbsp;</td>
						</tr>
					</tbody>
				</table>
			</div>
		<?php } ?>
	</body>
</html>

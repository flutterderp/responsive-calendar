<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>Mini Calendar Thing</title>

		<link href="fa59-all.min.css" rel="stylesheet">
		<link href="fonts.css" rel="stylesheet">
		<link href="style.css" rel="stylesheet">
  </head>
  <body>
		<div class="wrapper">
			<h1>Mini Calendar Thing</h1>

			<?php echo buildMiniCalendar(); ?>
		</div><!-- End of container -->
  </body>
</html>

<?php
/**
 * Function to build HTML output for a “mini” calendar thing
 *
 * @version 0.x.x
 * @return calendar string
 */
function buildMiniCalendar()
{
	$calendar    = array();
	$tz          = new DateTimeZone('UTC');
	$input_year  = 0;
	$input_month = 0;
	$input_day   = 1;
	$input_cal   = filter_input(INPUT_GET, 'cal', FILTER_SANITIZE_STRING, array('options' => array('default' => 'day')));

	if(count($_GET) > 0)
	{
		$input_year  = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
		$input_month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT, array('options' => array('default' => 0)));
		$input_day   = filter_input(INPUT_GET, 'day', FILTER_VALIDATE_INT, array('options' => array('default' => 1)));
	}

	if($input_year > 0 && $input_month > 0)
	{
		$input_datestring = $input_year . '-' . $input_month . '-' . $input_day;
		$date             = DateTime::createFromFormat('Y-n-j', $input_datestring, $tz);
	}
	else
	{
		$date = new DateTime(null, $tz);
	}

	$year        = $date->format('Y');
	$month       = $date->format('m');
	// $week        = $date->format('W');
	$day         = $date->format('d');
	$beginning   = DateTime::createFromFormat('Y-m-d', "$year-$month-01");
	$prev_date   = clone $beginning;
	$next_date   = clone $beginning;
	$interval    = new DateInterval('P1M');
	$prev_date->sub($interval);
	$next_date->add($interval);
	$num_days    = $date->format('t');
	$current_day = 1;
	$column      = 0;

	$txt_query          = array();
	$txt_query['cal']   = $input_cal;
	$txt_query['year']  = (int) $year;
	$txt_query['month'] = (int) $month;
	$txt_query['week']  = null;

	// Calendar wrapper
	$calendar[] = '<div class="minicalendar">';

	// Top links
	$calendar[] = '<div class="days nav">';
	$calendar[] = '<div><a href="?cal=' . $input_cal . '&amp;year=' . $prev_date->format('Y') . '&amp;month=' . $prev_date->format('n') . '&amp;week=' . $prev_date->format('W') . '"><span class="fa fa-angle-double-left"></span></a></div>';
	$calendar[] = '<div>' . $date->format('M') . '</div>';
	$calendar[] = '<div><a href="?cal=' . $input_cal . '&amp;year=' . $next_date->format('Y') . '&amp;month=' . $next_date->format('n') . '&amp;week=' . $next_date->format('W') . '"><span class="fa fa-angle-double-right"></span></a></div>';
	$calendar[] = '</div>';
	$calendar[] = '<div class="days"><div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div></div>';
	// End top links

	// Fill in blank cells before first of the month
	for($column; $column < $beginning->format('w'); ++$column)
	{
		$calendar[] = $column === 0 ? '<div class="week">' : null;
		$calendar[] = '<div class="day collapse"></div>';
		$calendar[] = $column === 6 ? '</div>' : null;
	}

	for($i = 1; $i <= $num_days; ++$i)
	{
		$column            = ($column > 6) ? 0 : $column;
		$txt_query['week'] = DateTime::createFromFormat('Y-m-j', "$year-$month-$i")->format('W');
		$txt_query['day']  = $i;
		$calendar[]        = ($column === 0) ? '<div class="week">' : null;

		$day_class = 'day';
		$day_class .= ($i === (int) $date->format('j')) ? ' current' : '';

		$calendar[] = '<div class="' . $day_class . '">';
		$calendar[] = '<a href="?' . http_build_query($txt_query) . '"></a>';
		$calendar[] = '<div class="date">' . $i . '</div>';
		$calendar[] = '</div>';

		if($column === 6 && $i < $num_days)
		{
			$calendar[] = '</div>';
			// $column = 0;
		}

		++$column;
	}

	// Fill in blank cells after last of month as necessary
	if($column > 6)
	{
		$calendar[] = '</div>';
	}
	else
	{
		while($column <= 6)
		{
			$calendar[] = '<div class="day collapse"></div>';
			$calendar[] = ($column === 6) ? '</div>' : null;
			++$column;
		}
	}

	// End of calendar wrapper
	$calendar[] = '</div>';

	// Trim the NULL values from the calendar array
	array_walk($calendar, function($v, $k) use (&$calendar) { if(is_null($v)) { unset($calendar[$k]); } });
	$calendar = implode(PHP_EOL, $calendar);

	return $calendar;
}

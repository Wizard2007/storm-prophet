<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

$dir = __DIR__.'/forecast/';
if(isset($_GET['file_name'])) {
	$fp = fopen($dir.urldecode($_GET['file_name']), 'r');
	$forecast = array('structured' => array(), 'values' => array(), 'size' => 1);
	while($data = fgetcsv($fp)) {
		$date = trim(array_shift($data));
		if(!isset($start_date)) {
			$start_date = $date;
		}
		$year = (int) substr($date, 0, 4);
		$month = (int) substr($date, 5, 2);
		$day = (int) substr($date, 8, 2);
		$hour = (int) substr($date, 11, 2);
		if(!isset($forecast['structured'][$year])) {
			$forecast['structured'][$year] = array();
		}
		if(!isset($forecast['structured'][$year][$month])) {
			$forecast['structured'][$year][$month] = array();
		}
		if(!isset($forecast['structured'][$year][$month][$day])) {
			$forecast['structured'][$year][$month][$day] = array();
		}
		if(!isset($forecast['structured'][$year][$month][$day][$hour])) {
			$forecast['structured'][$year][$month][$day][$hour] = array();
		}
		$forecast['structured'][$year][$month][$day][$hour] = array_map('floatval', $data);
		$forecast['values'][] = array($year, $month, $day, $hour);
		if(count($data) > $forecast['size']) {
			$forecast['size'] = count($data);
		}
	}
	fclose($fp);
	header('Content-type: text/json');
	$_GET['start_date'] = $start_date;
	$_GET['end_date'] = $date;
	$forecast['start_date'] = $start_date;
	$forecast['end_date'] = $date;
	echo json_encode(array('kyoto' => include('kyoto.php'), 'forecast' => $forecast));
}
else {
	$files = scandir($dir);
	$demo = array();
	foreach($files as $file_name) {
		if(substr($file_name, -4) == '.csv') {
			$fp = fopen($dir.$file_name, 'r');
			$first_line = fgets($fp);
			$pos = -2;
			while(fgetc($fp) != "\n") {
				fseek($fp, $pos, SEEK_END);
				$pos = $pos - 1;
			}
			$last_line = fgets($fp);
			fclose($fp);
			$first_data = array_map('trim', explode(',', $first_line));
			$last_data = array_map('trim', explode(',', $last_line));
			$demo[] = array('file_name' => $file_name, 'date_start' => $first_data[0], 'date_end' => $last_data[0], 'forecast' => count($last_data) - 1);
		}
	}
	echo json_encode($demo);
}
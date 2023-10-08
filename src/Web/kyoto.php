<?php
/* Actually, not only Kyoto anymore.
 * Kyoto + Dscovr */

function curLurk($url, $post = null) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_PORT, (substr($url, 0, 8) == 'https://')?443:80);
	if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	}
	curl_setopt($ch, CURLOPT_HEADER, true);
	if(!empty($post)) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	$content = curl_exec($ch);
	curl_close($ch);
	$status = 100;
	while($status == 100) {
		$break_pos = strpos($content, "\r\n\r\n");
		if($break_pos === false) {
			$break_pos = strpos($content, "\n\n");
			$headers = explode("\n", substr($content, 0, $break_pos));
			$content = substr($content, $break_pos + 2);
		}
		else {
			$headers = explode("\r\n", substr($content, 0, $break_pos));
			$content = substr($content, $break_pos + 4);
		}
		$status = (int) preg_replace('~\S+\s+(\d{3})\s.*$~', '$1', $headers[0]);
	}
	return array('status' => $status, 'headers' => $headers, 'content' => $content);
}

$dir = __DIR__.'/kyoto';
$csv = __DIR__.'/kyoto.csv';
$dscvr = __DIR__.'/dsc/';
if(!is_dir($dir)) {
	mkdir($dir);
}
$files = scandir($dir);

foreach($files as $file_name) {
	if(preg_match('~^(\d{4})-(\d{2})\.txt$~', $file_name, $matches)) {
		$rows = file($dir.'/'.$file_name);
		foreach($rows as $sub_day => $row) {
			if(trim($row) == '') break;
			for($hour = 0; $hour < 24; $hour++) {
				$h = sprintf('%02d', $hour);
				$value = substr($row, 20 + $hour*4, 4);
				if($value == 9999) {
					unlink($dir.'/'.$file_name);
					break(2);
				}
			}
		}
	}
}

$part = 'provisional';
$year = 2017;
$current_year = (int) date('Y');
$max = 12;
while($year <= $current_year) {
	$y = substr($year, 2);
	$month = 1;
	if($year === $current_year) {
		$part = 'realtime';
		$max = (int) date('m');
	}
	while($month <= $max) {
		$m = sprintf('%02d', $month);
		$file = $dir.'/'.$year.'-'.$m.'.txt';
		if(!is_file($file)) {
			$link = 'https://wdc.kugi.kyoto-u.ac.jp/dst_'.$part.'/'.$year.$m.'/dst'.$y.$m.'.for.request';
			$response = curLurk($link);
			file_put_contents($file, $response['content']);
		}
		$month++;
	}
	$year++;
}

$files = scandir($dir);
if(isset($_GET['csv'])) {
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment;filename=kyoto'.(isset($_GET['minute'])?'_minute':'').'.csv');
	$fp = fopen($csv, 'w');
	foreach($files as $file_name) {
		if(preg_match('~^(\d{4})-(\d{2})\.txt$~', $file_name, $matches)) {
			$rows = file($dir.'/'.$file_name);
			foreach($rows as $sub_day => $row) {
				if(trim($row) == '') break;
				$d = sprintf('%02d', $sub_day + 1);
				for($hour = 0; $hour < 24; $hour++) {
					$h = sprintf('%02d', $hour);
					$value = intval(substr($row, 20 + $hour*4, 4));
					if($value == 9999) continue;
					if(isset($_GET['minute'])) {
						if(isset($previous_value) && isset($previous_hour)) {
							$step = ($value - $previous_value) / 60;
							for($minute = 0; $minute < 60; $minute++) {
								if($minute < 30) {
									fputcsv($fp, array($previous_hour.sprintf('%02d', $minute + 30).':00', $previous_value + $step * $minute));
								}
								else {
									fputcsv($fp, array($matches[1].'-'.$matches[2].'-'.$d.' '.$h.':'.sprintf('%02d', $minute - 30).':00', $previous_value + $step * $minute));
								}
							}
						}
						$previous_value = intval($value);
						$previous_hour = $matches[1].'-'.$matches[2].'-'.$d.' '.$h.':';
					}
					else {
						fputcsv($fp, array($matches[1].'-'.$matches[2].'-'.$d.' '.$h.':00:00', $value));
					}
				}
			}
		}
	}
	if(isset($_GET['minute'])) {
		fputcsv($fp, array($previous_hour.'30:00', $previous_value));
	}
	fclose($fp);
	echo file_get_contents($csv);
}
elseif($_SERVER['REQUEST_URI'] == '/view.php') {
	$fp = fopen($csv, 'r');
	$dsts = array();
	while($data = fgetcsv($fp, 26, ",")) {
		if(empty($data[0])) {
			continue;
		}
		$year = (int) substr($data[0], 0, 4);
		$month = (int) substr($data[0], 5, 2);
		$day = (int) substr($data[0], 8, 2);
		$hour = (int) substr($data[0], 11, 2);
		$dst = (int) $data[1];
		if(!isset($dsts[$year])) {
			$dsts[$year] = array();
		}
		if(!isset($dsts[$year][$month])) {
			$dsts[$year][$month] = array();
		}
		if(!isset($dsts[$year][$month][$day])) {
			$dsts[$year][$month][$day] = array();
		}
		$dsts[$year][$month][$day][$hour] = $dst;
	}
	fclose($fp);
	return $dsts;
}
elseif(isset($_GET['y']) && isset($_GET['m'])) {
	$y = (int) $_GET['y'];
	$m = (int) $_GET['m'];
	$fp = fopen($csv, 'r');
	$return = array('dsts' => array(), 'dscvr' => array());
	while($data = fgetcsv($fp, 26, ",")) {
		if(empty($data[0])) {
			continue;
		}
		$year = (int) substr($data[0], 0, 4);
		$month = (int) substr($data[0], 5, 2);
		if($year != $y || $month != $m) {
			continue;
		}
		$day = (int) substr($data[0], 8, 2);
		$hour = (int) substr($data[0], 11, 2);
		$dst = (int) $data[1];
		if(!isset($return['dsts'][$day])) {
			$return['dsts'][$day] = array();
		}
		$return['dsts'][$day][$hour] = $dst;
	}
	fclose($fp);
	$stat = $dscvr.'stat_'.$_GET['y'].'_'.$_GET['m'].'.csv';
	$stat2 = $dscvr.'stat2_'.$_GET['y'].'_'.$_GET['m'].'.csv';
	if(is_file($stat)) {
		$fp = fopen($stat, 'r');
		$values = array();
		while($data = fgetcsv($fp, 33000, ",")) {
			$year = (int) substr($data[0], 0, 4);
			$month = (int) substr($data[0], 5, 2);
			$day = (int) substr($data[0], 8, 2);
			$hour = (int) substr($data[0], 11, 2);
			if(!isset($return['dscvr'][$day])) {
				$return['dscvr'][$day] = array();
				$values[$day] = array();
			}
			if(!isset($return['dscvr'][$day][$hour])) {
				$return['dscvr'][$day][$hour] = array('zero_count' => 0, 'dynamic' => 0, 'average' => 0, 'values' => '', 'count' => 0);
				$values[$day][$hour] = array();
			}
			$return['dscvr'][$day][$hour]['zero_count'] += $data[1];
			$return['dscvr'][$day][$hour]['dynamic'] += $data[2];
			$return['dscvr'][$day][$hour]['average'] += $data[3];
			$return['dscvr'][$day][$hour]['values'] .= $data[5];
			$return['dscvr'][$day][$hour]['count']++;
			$values[$day][$hour] = array_map('floatval', array_merge($values[$day][$hour], explode('|', $data[5])));
		}
		foreach($return['dscvr'] as $day => $hours) {
			foreach($hours as $hour => $indexes) {
				$values_count = count($values[$day][$hour]);
				$average_value = 0;
				$deviation = 0;
				if($values_count) {
					$average_value = array_sum($values) / $values_count;
					foreach($values[$day][$hour] as $value) {
						$deviation += pow($value - $average_value, 2);
					}
					$deviation = sqrt($deviation / $values_count);
				}
				$return['dscvr'][$day][$hour]['average'] /= $return['dscvr'][$day][$hour]['count'];
				unset($return['dscvr'][$day][$hour]['count']);
				$return['dscvr'][$day][$hour]['deviation'] = $deviation;
			}
		}
		fclose($fp);
	}
	else {
		$fr = fopen($dscvr.'dsc_fc_summed_spectra_'.$_GET['y'].'_v01.csv', 'r');
		$fw = fopen($stat, 'w');
		$fw2 = fopen($stat2, 'w');
		$values = array();
		while($data = fgetcsv($fr, 550, ",")) {
			$year = (int) substr($data[0], 0, 4);
			$month = (int) substr($data[0], 5, 2);
			if($year != $y || $month != $m) {
				$prev = $data;
				continue;
			}
			$day = (int) substr($data[0], 8, 2);
			$hour = (int) substr($data[0], 11, 2);
			if(!isset($return['dscvr'][$day])) {
				$return['dscvr'][$day] = array();
				$values[$day] = array();
			}
			if(!isset($return['dscvr'][$day][$hour])) {
				$return['dscvr'][$day][$hour] = array('zero_count' => 0, 'dynamic' => 0, 'average' => 0, 'values' => '', 'count' => 0, 'x' => 0, 'y' => 0, 'z' => 0);
				$values[$day][$hour] = array();
			}
			$zero_count = 0;
			$values_minute = array();
			for($i = 4; $i < 54; $i++) {
				if($data[$i] == 0) {
					$zero_count++;
				}
				else {
					$value = (float) $data[$i];
					$values_minute[] = $value;
					$values[$day][$hour][] = $value;
				}
			}
			$values_count = count($values_minute);
			$average_value = 0;
			$deviation = 0;
			if($values_count) {
				$average_value = array_sum($values_minute) / $values_count;
				foreach($values_minute as $value) {
					$deviation += pow($value - $average_value, 2);
				}
				$deviation = sqrt($deviation / $values_count);
			}
			$delta = 0;
			if(isset($prev) && is_numeric($prev[1]) && is_numeric($prev[2]) && is_numeric($prev[3]) && is_numeric($data[1]) && is_numeric($data[2]) && is_numeric($data[3])) {
				$data_x = (float) $data[1];
				$data_y = (float) $data[2];
				$data_z = (float) $data[3];
				$delta = sqrt(pow($data_x - $prev[1], 2) + pow($data_y - $prev[2], 2) + pow($data_z - $prev[3], 2));
				$return['dscvr'][$day][$hour]['x'] += $data_x;
				$return['dscvr'][$day][$hour]['y'] += $data_y;
				$return['dscvr'][$day][$hour]['z'] += $data_z;
			}
			$values_implode = implode('|', $values_minute);
			fputcsv($fw, array($data[0], $zero_count, $delta, $average_value, $deviation, $values_implode));
			$return['dscvr'][$day][$hour]['zero_count'] += $zero_count;
			$return['dscvr'][$day][$hour]['dynamic'] += $delta;
			$return['dscvr'][$day][$hour]['average'] += $average_value;
			$return['dscvr'][$day][$hour]['values'] .= '|'.$values_implode;
			$return['dscvr'][$day][$hour]['count']++;
			$prev = $data;
		}
		foreach($return['dscvr'] as $day => $hours) {
			foreach($hours as $hour => $indexes) {
				$values_count = count($values[$day][$hour]);
				$average_value = 0;
				$deviation = 0;
				if($values_count) {
					$average_value = array_sum($values) / $values_count;
					foreach($values[$day][$hour] as $value) {
						$deviation += pow($value - $average_value, 2);
					}
					$deviation = sqrt($deviation / $values_count);
				}
				$return['dscvr'][$day][$hour]['average'] /= $return['dscvr'][$day][$hour]['count'];
				unset($return['dscvr'][$day][$hour]['count']);
				$return['dscvr'][$day][$hour]['deviation'] = $deviation;
				$return['dscvr'][$day][$hour]['values'] = $return['dscvr'][$day][$hour]['values'];
				fputcsv($fw2, array($_GET['y'].'-'.sprintf('%02d', $_GET['m']).'-'.sprintf('%02d', $day).' '.sprintf('%02d', $hour).':00:00',
					$return['dscvr'][$day][$hour]['x'],
					$return['dscvr'][$day][$hour]['y'],
					$return['dscvr'][$day][$hour]['z'],
					$return['dscvr'][$day][$hour]['dynamic'],
					$return['dscvr'][$day][$hour]['average'],
					$deviation,
					$return['dscvr'][$day][$hour]['zero_count'])
				);
			}
		}
		fclose($fr);
		fclose($fw);
		fclose($fw2);
	}
	return $return;
}
else {
	if(isset($_GET['start_date']) && isset($_GET['end_date'])) {
		$datetime = new DateTime($_GET['start_date']);
		$date_start = array((int) $datetime->format('Y'), (int) $datetime->format('m'), (int) $datetime->format('d'), (int) $datetime->format('h'));
		$month_end = substr($_GET['end_date'], 0, 4).substr($_GET['end_date'], 5, 2);
		$structured = $values = array();
		while($datetime->format('Y').$datetime->format('m') != $month_end) {
			$year = (int) $datetime->format('Y');
			$month = (int) $datetime->format('m');
			if(!isset($structured[$year])) {
				$structured[$year] = array();
			}
			$structured[$year][$month] = array();
			$rows = file($dir.'/'.$datetime->format('Y-m').'.txt');
			foreach($rows as $sub_day => $row) {
				if(trim($row) == '') break;
				$day = $sub_day + 1;
				$structured[$year][$month][$day] = array();
				$rows[] = array();
				for($hour = 0; $hour < 24; $hour++) {
					$value = substr($row, 20 + $hour*4, 4);
					if($value == 9999) continue;
					$structured[$year][$month][$day][$hour] = (int) $value;
					$values[] = array($year, $month, $day, $hour);
				}
			}
			$datetime->modify('+1 month');
		}
		$year = (int) $datetime->format('Y');
		$month = (int) $datetime->format('m');
		if(!isset($structured[$year])) {
			$structured[$year] = array();
		}
		$structured[$year][$month] = array();
		$rows = file($dir.'/'.$datetime->format('Y-m').'.txt');
		foreach($rows as $sub_day => $row) {
			if(trim($row) == '') break;
			$day = $sub_day + 1;
			$structured[$year][$month][$day] = array();
			$rows[] = array();
			for($hour = 0; $hour < 24; $hour++) {
				$value = substr($row, 20 + $hour*4, 4);
				if($value == 9999) continue;
				$structured[$year][$month][$day][$hour] = (int) $value;
				$values[] = array($year, $month, $day, $hour);
			}
		}
		$date_end = array((int) $datetime->format('Y'), (int) $datetime->format('m'), (int) $datetime->format('d'), (int) $datetime->format('h'));
		
		foreach($values as $i => $value) {
			if($value == $date_start) {
				$start = $i;
			}
			if($value == $date_end) {
				$end = $i;
			}
		}
		return array('structured' => $structured, 'values' => $values, 'start' => $start, 'end' => $end);
	}
	else {
		$datetime = new DateTime();
		$structured = $values = array();
		if((int) $datetime->format('d') <= 30) {
			$datetime->modify('-30 days');
			$year = (int) $datetime->format('Y');
			$month = (int) $datetime->format('m');
			$structured[$year] = array();
			$structured[$year][$month] = array();
			$rows = file($dir.'/'.$datetime->format('Y-m').'.txt');
			foreach($rows as $sub_day => $row) {
				if(trim($row) == '') break;
				$day = $sub_day + 1;
				$structured[$year][$month][$day] = array();
				$rows[] = array();
				for($hour = 0; $hour < 24; $hour++) {
					$value = substr($row, 20 + $hour*4, 4);
					if($value == 9999) continue;
					$structured[$year][$month][$day][$hour] = (int) $value;
					$values[] = array($year, $month, $day, $hour);
				}
			}
		}
		$datetime->modify('+30 days');
		$year = (int) $datetime->format('Y');
		$month = (int) $datetime->format('m');
		if(!isset($structured[$year])) {
			$structured[$year] = array();
		}
		if(!isset($structured[$year][$month])) {
			$structured[$year][$month] = array();
		}
		$rows = file($dir.'/'.$datetime->format('Y-m').'.txt');
		foreach($rows as $sub_day => $row) {
			if(trim($row) == '') break;
			$day = $sub_day + 1;
			$structured[$year][$month][$day] = array();
			$rows[] = array();
			for($hour = 0; $hour < 24; $hour++) {
				$value = substr($row, 20 + $hour*4, 4);
				if($value == 9999) continue;
				$structured[$year][$month][$day][$hour] = (int) $value;
				$values[] = array($year, $month, $day, $hour);
			}
		}
		$values = array_values($values);
		echo json_encode(array('structured' => $structured, 'values' => $values));
	}
	/*
	$count = count($values);
	foreach($values as $i => $value) {
		if($i < $count - 120) {
			unset($structured[$value[0]][$value[1]][$value[2]][$value[3]]);
			unset($values[$i]);
		}
	}
	foreach($structured as $year => $year_values) {
		foreach($year_values as $month => $month_values) {
			foreach($month_values as $day => $day_values) {
				if(empty($day_values)) {
					unset($structured[$year][$month][$day]);
				}
			}
			if(empty($structured[$year][$month])) {
				unset($structured[$year][$month]);
			}
		}
		if(empty($structured[$year])) {
			unset($structured[$year]);
		}
	}
	 */
}
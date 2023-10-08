<?php
$hour = (int) date('H');
$structured = $values = array();
for($d = 0; $d < 6; $d++) {
	$datetime = new DateTime('+'.$d.' day');
	$year = (int) $datetime->format('Y');
	$month = (int) $datetime->format('m');
	$day = (int) $datetime->format('d');
	if(!isset($structured[$year])) {
		$structured[$year] = array();
	}
	if(!isset($structured[$year][$month])) {
		$structured[$year][$month] = array();
	}
	if(!isset($structured[$year][$month][$day])) {
		$structured[$year][$month][$day] = array();
	}
	$h_min = 0;
	$h_max = 24;
	if($d == 0) {
		$h_min = $hour;
	}
	for($h = $h_min; $h < $h_max; $h++) {
		if(!empty($values)) {
			$previous = $values[count($values) - 1];
			$previuos_value = $structured[$previous[0]][$previous[1]][$previous[2]][$previous[3]];
			if($previuos_value > 50) {
				$value = rand($previuos_value - 20, $previuos_value);
			}
			elseif($previuos_value < -100) {
				$value = rand($previuos_value - 10, $previuos_value + 20);
			}
			elseif($previuos_value < -200) {
				$value = rand($previuos_value, $previuos_value + 20);
			}
			else {
				$value = rand($previuos_value - 20, $previuos_value + 20);
			}
		}
		else {
			$value = rand(-20, 10);
		}
		$structured[$year][$month][$day][$h] = $value;
		$values[] = array($year, $month, $day, $h);
	}
}
echo json_encode(array('structured' => $structured, 'values' => $values));
<?php
$dscovr = __DIR__.'/dsc/';
$summed = $dscovr.'summed.csv';
$fw = fopen($summed, 'w');
$files = scandir($dscovr);
natsort($files);
foreach($files as $file_name) {
	if(preg_match('~^stat2_(\d{4})_(\d{1,2})\.csv$~', $file_name, $matches)) {
		if(!isset($_GET['all'])) {
			if($matches[1] <= 2019 || $matches[1] == 2020 && $matches[2] < 9) {
				continue;
			}
		}
		$fr = fopen($dscovr.$file_name, 'r');
		while($row = fread($fr, 150)) {
			fputs($fw, $row);
		}
		fclose($fr);
	}
}
fclose($fw);
header('Content-Type: text/csv');
if(isset($_GET['all'])) {
	header('Content-Disposition: attachment;filename=discovr_summed_all.csv');
}
else {
	header('Content-Disposition: attachment;filename=discovr_summed.csv');
}
echo file_get_contents($summed);
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no" />
		<title>View</title>
		<link rel="stylesheet" href="/style.css" />
		<style type="text/css" id="switch-style">svg text { display: block !important; }</style>
	</head>
	<body>
		<?php
		$data = include('kyoto.php');
		$marks = array(
			array('index' => 50, 'text' => 'ok'),
			array('index' => 0, 'text' => 'ok'),
			array('index' => -50, 'text' => 'moderate storm'),
			array('index' => -100, 'text' => 'strong storm'),
			array('index' => -200, 'text' => 'extreme storm')
		);
		if(isset($_GET['y']) && isset($_GET['m'])) {
			$dateObj = DateTime::createFromFormat('!m', $_GET['m']);
			echo '<div class="month"><h1><a href="view.php" title="back">←</a> '.$dateObj->format('F').' '.$_GET['y'].' <button class="switch" title="show sensor loss">&empty;</button> <button class="switch" title="show vector dynamic">&Delta;</button> <button class="switch" title="show sensors average">x&#x0305;</button> <button class="switch" title="show sensors deviation">&sigma;</button> <button class="switch" title="show absolute">| |</button></h1>';
			echo '<svg viewBox="0 0 24 350" class="scale" width="24" height="350" xmlns="http://www.w3.org/2000/svg">';
			foreach($marks as $mark) {
				$text = explode(' ', $mark['text']);
				foreach($text as $i => $word) {
					echo '<text x="22px" y="'.(99 - $mark['index'] + $i * 5).'" text-anchor="end" class="back">'.$word.'</text><text x="22px" y="'.(99 - $mark['index'] + $i * 5).'" text-anchor="end">'.$word.'</text>';
				}
			}
			echo '</svg>';
			foreach($data['dsts'] as $day => $hours) {
				echo '<svg viewBox="0 0 24 350" width="24" height="350" xmlns="http://www.w3.org/2000/svg"><line x1="0" y1="50" x2="0" y2="350"></line><line x1="24" y1="50" x2="24" y2="350"></line>';
				foreach($marks as $mark) {
					echo '<line x1="0" y1="'.(100 - $mark['index']).'" x2="24" y2="'.(100 - $mark['index']).'"></line>';
				}
				foreach($hours as $hour => $dst) {
					$height = $dst;
					if($height < 0) {
						$height = -$height;
						$y = 100;
					}
					else {
						$y = 100 - $height;
						echo '<rect x="'.$hour.'" y="100" width="1px" height="'.$height.'px" class="abs"></rect>';
					}
					if(isset($data['dscvr'][$day]) && isset($data['dscvr'][$day][$hour])) {
						if(isset($data['dscvr'][$day][$hour]['zero_count'])) {
							echo '<rect x="'.$hour.'" y="50" width="1px" height="'.($data['dscvr'][$day][$hour]['zero_count'] / 60).'px" class="zero"></rect>';
						}
						if(isset($data['dscvr'][$day][$hour]['dynamic'])) {
							echo '<rect x="'.$hour.'" y="50" width="1px" height="'.($data['dscvr'][$day][$hour]['dynamic'] / 8).'px" class="dynamic"></rect>';
						}
						if(isset($data['dscvr'][$day][$hour]['average'])) {
							echo '<rect x="'.$hour.'" y="50" width="1px" height="'.($data['dscvr'][$day][$hour]['average'] / 10).'px" class="average"></rect>';
						}
						if(isset($data['dscvr'][$day][$hour]['deviation'])) {
							echo '<rect x="'.$hour.'" y="50" width="1px" height="'.($data['dscvr'][$day][$hour]['deviation'] / 10).'px" class="deviation"></rect>';
						}
					}
					echo '<rect x="'.$hour.'" y="'.$y.'" width="1px" height="'.$height.'px"></rect>';
				}
				echo '<text x="1px" y="49px">'.$day.'</text>';
				echo '</svg>';
			}
			echo '</div>';
		}
		else {
			echo '<div id="full">';
			foreach($data as $year => $months) {
				echo '<h2>'.$year.' <button class="extend" title="load extended data">&#x1F6F0;&#xFE0F;</button> <button class="switch on" title="show text">T</button> <button class="switch" title="show sensor loss">&empty;</button> <button class="switch" title="show vector dynamic">&Delta;</button> <button class="switch" title="show sensors average">x&#x0305;</button> <button class="switch" title="show sensors deviation">&sigma;</button></h2>';
				foreach($months as $month => $days) {
					echo '<a href="?y='.$year.'&m='.$month.'"><svg id="m'.$year.'_'.$month.'" viewBox="0 0 31 31" width="31" height="31" xmlns="http://www.w3.org/2000/svg">';
					$count = count($days) * 24;
					$sum = 0;
					$x_dst_month = 0;
					foreach($days as $day => $hours) {
						$x_dst_day = 0;
						foreach($hours as $hour => $dst) {
							$sum += $dst;
							if($dst < -50) {
								$x_dst_day += 50 - $dst;
							}
						}
						$x_dst_month += $x_dst_day;
						$height = $x_dst_day / 24 / 200 * 31;
						echo '<rect x="'.($day - 1).'" y="'.(31 - $height).'" width="1px" height="'.$height.'px"></rect>';
					}
					$avg = round($sum / $count);
					$dev_sum = 0;
					foreach($days as $day => $hours) {
						foreach($hours as $hour => $dst) {
							$dev_sum += abs($dst - $avg);
						}
					}
					$dev = round($dev_sum / $count);
					echo '<text x="30px" y="3px" text-anchor="end" class="back">x-dst: '.$x_dst_month.'</text><text x="30px" y="3px" text-anchor="end">x-dst: '.$x_dst_month.'</text>';
					//echo '<text x="30px" y="7px" text-anchor="end" class="back">average: '.$avg.'</text><text x="30px" y="7px" text-anchor="end">average: '.$avg.'</text>';
					//echo '<text x="30px" y="11px" text-anchor="end" class="back">abs. dev.: '.$dev.'</text><text x="30px" y="11px" text-anchor="end">abs. dev.: '.$dev.'</text>';
					$dateObj = DateTime::createFromFormat('!m', $month);
					echo '<text x="1px" y="3px" class="back">'.$dateObj->format('M').'</text><text x="1px" y="3px">'.$dateObj->format('M').'</text></svg></a>';
				}
			}
			echo '<div>';
		?>
		<script type="text/javascript">
			var full = document.getElementById('full');
			function appendNote(svg, text) {
				let childNodes = svg.childNodes;
				let textCount = 0;
				for(let i = 0; i < childNodes.length; i++) {
					if(childNodes[i].tagName == 'text' && childNodes[i].getAttribute('class') != 'back') {
						textCount++;
					}
				}
				let noteBack = document.createElementNS('http://www.w3.org/2000/svg', 'text');
				let note = document.createElementNS('http://www.w3.org/2000/svg', 'text');
				noteBack.setAttribute('x', '30px');
				noteBack.setAttribute('y', 4*textCount - 1);
				noteBack.setAttribute('text-anchor', 'end');
				noteBack.setAttribute('class', 'back');
				noteBack.appendChild(document.createTextNode(text));
				note.setAttribute('x', '30px');
				note.setAttribute('y', 4*textCount - 1);
				note.setAttribute('text-anchor', 'end');
				note.appendChild(document.createTextNode(text));
				svg.appendChild(noteBack);
				svg.appendChild(note);
				
			}
			function appendRecord(svg, day, value, className) {
				let rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
				rect.setAttribute('x', day - 1);
				rect.setAttribute('y', 0);
				rect.setAttribute('width', '1px');
				rect.setAttribute('height', value);
				rect.setAttribute('class', className);
				svg.insertBefore(rect, svg.childNodes[0]);
			}
			function extend(a) {
				let month = a
				if(a.nodeName != 'A') {
					month = a.shift();
				}
				if(month.className == 'complete' || document.getElementsByClassName('loading').length > 0) {
					return;
				}
				full.className = 'loading';
				month.className = 'loading';
				let request = new XMLHttpRequest();
				request.responseType = 'json';
				request.open('GET', '/ajax.php' + month.getAttribute('href'));
				request.send();
				request.onload = function() {
					if(request.status == 200) {
						let dscvr = request.response.dscvr;
						let svg = month.childNodes[0];
						let zero_count = 0;
						let hours_count = 0;
						let zero_days = [];
						let dynamic = 0;
						let average = 0;
						let values = [];
						let averages = [];
						for(let i = 1; i < 32; i++) {
							if(!dscvr[i]) continue;
							zero_days[i] = 0;
							let dynamic_day = 0;
							let average_day = 0;
							values[i] = [];
							averages[i] = 0;
							for(let h = 0; h < dscvr[i].length; h++) {
								zero_days[i] += dscvr[i][h].zero_count;
								dynamic_day += dscvr[i][h].dynamic;
								averages[i] += dscvr[i][h].average;
								average += dscvr[i][h].average;
								values[i] = values[i].concat(dscvr[i][h].values.split('|').map(Number));
								hours_count++;
							}
							averages[i] = averages[i] / 24;
							appendRecord(svg, i, zero_days[i] / 60 / 50, 'zero');
							appendRecord(svg, i, dynamic_day / 60 / 8, 'dynamic');
							appendRecord(svg, i, averages[i] / 20, 'average');
							zero_count += zero_days[i];
							dynamic += dynamic_day;
						}
						average = average / hours_count;
						let deviation = 0;
						let values_count = 0;
						for(let i = 1; i < 32; i++) {
							if(!values[i]) continue;
							let deviation_day = 0;
							for(let v = 0; v < values[i].length; v++) {
								if(!values[i][v]) continue;
								deviation_day += Math.pow(averages[i] - values[i][v], 2);
								deviation += Math.pow(average - values[i][v], 2);
								values_count++;
							}
							deviation_day = Math.sqrt(deviation_day / values[i].length);
							appendRecord(svg, i, deviation_day / 20, 'deviation');
						}
						deviation = Math.sqrt(deviation / values_count);
						appendNote(svg, '0/hour: ' + (zero_count ? Math.round(zero_count/hours_count) : 0));
						appendNote(svg, 'dynamic: ' + Math.round(dynamic));
						appendNote(svg, 'average: ' + Math.round(average * 100) / 100);
						appendNote(svg, 'deviation: ' + Math.round(deviation * 100) / 100);
					}
					month.className = 'complete';
					full.className = '';
					if(a.length) {
						extend(a);
					}
				};
			}
			var months = document.getElementsByTagName('a');
			for(let i = 0; i < months.length; i++) {
				let month = months[i];
				month.addEventListener('mouseover', function() {extend(month, null);});
			}
		</script>
		<?php
		}
		?>
		<script type="text/javascript" src="/script.js"></script>
	</body>
</html>
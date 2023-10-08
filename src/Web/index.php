<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no" />
    <title>Storm prophet</title>
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<script src="https://cesium.com/downloads/cesiumjs/releases/1.110/Build/Cesium/Cesium.js"></script>
	<link href="https://cesium.com/downloads/cesiumjs/releases/1.110/Build/Cesium/Widgets/widgets.css" rel="stylesheet">
    <style>
      html,
      body,
      #cesiumContainer {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
      }
	#sun {
		position: absolute;
		top: 50%;
		left: 10vw;
		margin: -16px;
		filter: drop-shadow(0 0 5px rgba(255,255,255,1)) drop-shadow(0 0 10px rgba(255,255,255,1));
	}
	#datetime {
		position: absolute;
		top: 1em;
		width: 100%;
		text-align: center;
		color: #FFF;
		font-size: 3em;
		font-weight: bold;
		font-family: 'Courier New', monospace;
		text-shadow: 0 0 3px #FFF;
	}
	#datetime:after {
		content: ' (UTC+0)';
		font-size: 0.5em;
		opacity: 0.5;
	}
	#demo-menu {
		position: absolute;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		box-sizing: border-box;
		padding: 1em 0;
		overflow: auto;
		color: #FFF;
	}
	#demo-menu div {
		width: fit-content;
		margin: 1em auto;
		padding: 1em;
		background-color: rgba(255,255,255,0.5);
		border: 0.2em solid rgba(255,255,255,0.8);
		border-radius: 2em;
		font-size: 2em;
		cursor: pointer;
	}
	#demo-menu div h3 {
		margin: 0 1em 0.5em 0;
	}
	#demo-menu div p {
		margin: 0;
	}
	#demo-menu div {
		width: fit-content;
		margin: 1em auto;
		padding: 1em;
		background-color: rgba(255,255,255,0.4);
		border: 0.2em solid rgba(255,255,255,0.8);
		border-radius: 2em;
		font-size: 2em;
		text-shadow: 0 0 1px #000;
		backdrop-filter: blur(10px);
	}
	#demo-menu div:hover {
		background-color: rgba(255,255,255,0.2);
		border: 0.2em solid rgba(255,255,255,1);
		text-shadow: 0 0 3px #000;
	}
	#demo-menu-open {
		position: absolute;
		left: 0;
		top: 0;
		padding: 0 0.5em;
		font-size: 2em;
		color: #FFF;
		transform: rotate(180deg);
		cursor: pointer;
	}
	#demo-menu-open:hover {
		text-shadow: 0 0 3px #FFF;
	}
	
	
	#dst {
		position: absolute;
		bottom: 1em;
		right: 1em;
		text-align: center;
		color: #73B1FF;
		font-size: 3em;
		font-weight: bold;
		font-family: 'Courier New', monospace;
		text-shadow: 0 0 3px #FFF;
	}
	#dst.kyoto {
		color: #FC3;
		opacity: 0.5;
	}
	#graph-wrapper {
		height: 100%;
		position: absolute;
		right: 50vw;
		top: 50vh;
		overflow: hidden;
	}
	#graph {
		width: auto;
		height: 100%;
	}
	#graph rect {
		fill: #73B1FF;
		display: none;
	}
	#graph rect.kyoto {
		fill: #FC3;
		display: block;
	}
	#graph-legend-wrapper {
		height: 100%;
		position: absolute;
		left: 50vw;
		top: 50vh;
	}
	#graph-legend {
		width: auto;
		height: 100%;
	}
	#graph-legend text {
		fill: #FFF;
		font-size: 10px;
		font-family: monospace;
	}
	#graph-legend text.warning {
		text-shadow: 0 0 3px #FFF;
	}
	#graph line, #graph-legend line {
		stroke: rgba(255,255,255,0.5);
		stroke-width: 1px;
	}
	.cesium-viewer-bottom, .cesium-viewer-timelineContainer {
		z-index: 1;
	}
	#rss {
		position: absolute;
		left: 0;
		top: 0;
		z-index: 777;
	}
	#rss img {
		width: 10vw;
		margin: 1vw 2vw;
	}
    </style>
	<style type="text/css" id="switch-style"></style>
  </head>
  <body>
    <div id="cesiumContainer" class="fullSize"></div>
	<a href="/rss.php" id="rss" target="_blank"><img src="/rss-148289_1920.png" alt="RSS" /></a>
	<svg id="sun" viewBox="0 0 32 32" width="32" height="32" xmlns="http://www.w3.org/2000/svg" class="cluster"><circle cx="16" cy="16" r="16" fill="#f3c94c"></circle></svg>
	<div id="datetime" style="display: none;"></div>
	<div id="dst" style="display: none;"></div>
	<div id="graph-wrapper" style="display: none;"><svg id="graph" viewBox="0 0 0 400" width="0" height="400" xmlns="http://www.w3.org/2000/svg" class="cluster"></svg></div>
	<div id="graph-legend-wrapper" style="display: none;"><svg id="graph-legend" viewBox="0 0 70 400" width="70" height="400" xmlns="http://www.w3.org/2000/svg"><text x="0" y="27.5" class="">ok</text><text x="0" y="52.5" class="">ok</text><text x="0" y="77.5" class="warning">moderate</text><text x="5" y="87.5" class="warning">storm</text><text x="0" y="102.5" class="warning">strong</text><text x="5" y="112.5" class="warning">storm</text><text x="0" y="152.5" class="warning">extreme</text><text x="5" y="162.5" class="warning">storm</text></svg></div>
    <div id="demo-menu-open" style="display: none;" title="back to demo menu">&#8618;</div>
	<div id="demo-menu"></div>
	<script>
		const demoMenu = document.getElementById('demo-menu');
		const demoMenuOpen = document.getElementById('demo-menu-open');
		const datetime = document.getElementById('datetime');
		const dst = document.getElementById('dst');
		const graph = document.getElementById('graph');
		const graphWrapper = document.getElementById('graph-wrapper');
		const graphLegend = document.getElementById('graph-legend');
		const graphLegendWrapper = document.getElementById('graph-legend-wrapper');
		const kyoto = <?php include('kyoto.php') ?>;
		const forecast = <?php include('forecast.php') ?>;
		const demo = <?php include('demo.php') ?>;
		const marks = [
			{index: 50, text: 'ok', className: ''},
			{index: 0, text: 'ok', className: ''},
			{index: -50, text: 'moderate storm', className: 'warning'},
			{index: -100, text: 'strong storm', className: 'warning'},
			{index: -200, text: 'extreme storm', className: 'warning'}
		];
		var columnLength;
		var columnWidth;
		var forecastSize;
		
		function appendRecord(key, value, className) {
			let x = key * 2;
			let height = value / 2;
			let y = 50 - height;
			if(value < 0) {
				height = -height;
				y = 50;
			}
			let rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
			rect.setAttribute('x', x);
			rect.setAttribute('y', y);
			rect.setAttribute('width', 2);
			rect.setAttribute('height', height);
			if(className) {
				rect.setAttribute('class', className);
			}
			graph.appendChild(rect);
		}
		
		function demo_init(demo) {
			for(let i = 0; i < demo.length; i++) {
				let div = document.createElement('div');
				div.innerHTML = '<h3>' + demo[i].file_name + '</h3><p>From: ' + demo[i].date_start.substr(0, demo[i].date_start.length - 3) + '</p><p>To: ' + demo[i].date_end.substr(0, demo[i].date_end.length - 3) + '</p><p>Forecast: ' + demo[i].forecast + ' hour' + (demo[i].forecast == 1 ? '' : 's') + '</p>';
				div.addEventListener('click', function() {
					demoMenu.style.display = 'none';
					demoMenuOpen.style.display = 'block';
					let request = new XMLHttpRequest();
					request.responseType = 'json';
					request.open('GET', '/demo.php?file_name=' + demo[i].file_name);
					request.send();
					request.onload = function() {
						if(request.status == 200) {
							let kyoto = request.response.kyoto;
							let forecast = request.response.forecast;
							let forecast_length = forecast.values.length + forecast.size;
							let kyoto_length = forecast_length + 200;
							let width = kyoto_length * 2;
							let legend_width = forecast.size * 2 + 71;
							graph.setAttribute('viewBox', '0 0 ' + width + ' 400');
							graph.setAttribute('width', width);
							graph.removeAttribute('style');
							graphLegend.setAttribute('viewBox', '0 0 ' + legend_width + ' 400');
							graphLegend.setAttribute('width', legend_width);
							while(graph.lastChild) {
								graph.removeChild(graph.lastChild);
							}
							while(graphLegend.lastChild) {
								graphLegend.removeChild(graphLegend.lastChild);
							}
							for(let i = 0; i < marks.length; i++) {
								if(marks[i].index !== 0) {
									let line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
									line.setAttribute('x1', 0);
									line.setAttribute('y1', 50 - marks[i].index/2);
									line.setAttribute('x2', width);
									line.setAttribute('y2', 50 - marks[i].index/2);
									graph.appendChild(line);
								}
								let words = marks[i].text.split(' ');
								for(let w = 0; w < words.length; w++) {
									let record = document.createElementNS('http://www.w3.org/2000/svg', 'text');
									record.setAttribute('x', forecast.size * 2 + 10 + w * 5);
									record.setAttribute('y', 50 - marks[i].index/2 + 2.5 + w * 10);
									record.setAttribute('class', marks[i].className);
									let text = document.createTextNode(words[w]);
									record.appendChild(text);
									graphLegend.appendChild(record);
								}
							};
							let line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
							line.setAttribute('x1', 0.5);
							line.setAttribute('y1', 0);
							line.setAttribute('x2', 0.5);
							line.setAttribute('y2', 400);
							graphLegend.appendChild(line);
							
							columnLength = kyoto_length;
							forecastSize = forecast.size;
							
							setTimeout(function() {
								columnWidth = graph.getBoundingClientRect().width / (columnLength - 1);
								graphLegendWrapper.style.marginLeft = (-forecastSize * columnWidth) + 'px';
								graph.removeAttribute('style');
								clock.currentTime = clock.startTime;
								timeline.updateFromClock();
							});
							
							graphLegend.style.display = 'block';
							
							for(let i = 0; i < kyoto_length; i++) {
								let key = kyoto.start + i;
								let date = kyoto.values[key];
								if(date) {
									let value = kyoto.structured[date[0]][date[1]][date[2]][date[3]];
									appendRecord(i, value, 'kyoto');
								}
							}
							
							for(let i = 0; i < forecast_length; i++) {
								let key = kyoto_length - i;
								let date = forecast.values[forecast_length - i - 1];
								if(date) {
									let value = forecast.structured[date[0]][date[1]][date[2]][date[3]];
									for(let v = 0; v < value.length; v++) {
										appendRecord(key + v, value[v], 'forecast_' + date.join('_'));
									}
								}
							}

							graphWrapper.style.display = 'block';
							graphLegendWrapper.style.display = 'block';
							datetime.style.display = 'block';
							//dst.style.display = 'block';
							clock.currentTime = Cesium.JulianDate.fromIso8601(demo[i].date_start.substr(0, 10));
							clock.startTime = Cesium.JulianDate.fromIso8601(forecast.start_date.substr(0, 10));
							clock.stopTime = Cesium.JulianDate.fromIso8601(forecast.values[forecast.values.length - 1][0]+'-'+pad(forecast.values[forecast.values.length - 1][1])+'-'+pad(forecast.values[forecast.values.length - 1][2]));
							timeline.updateFromClock();
							clock.currentTime = Cesium.JulianDate.fromIso8601(demo[i].date_start.substr(0, 10));
							timeline.zoomTo(clock.startTime, clock.stopTime);
							fit_earth(clock);
						}
					};
				});
				demoMenu.appendChild(div);
			}
		}
		demo_init(demo);
		
		demoMenuOpen.addEventListener('click', function() {
			demoMenu.style.display = 'block';
			demoMenuOpen.style.display = 'none';
		});
		
		function pad(num) {
			var s = '0' + num;
			return s.substr(s.length - 2);
		}
		
		Cesium.Ion.defaultAccessToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJqdGkiOiI0M2QwMzBhNS0yMTM2LTRjNzItODM5NC1hNjcwZjIzNTdkYTkiLCJpZCI6MTY4NzE1LCJpYXQiOjE2OTU3NTI5NjV9.6vKryk0jEWQiG0NLicjCMbYL7ktaHVPr8_5iGm9I_EI';

		const JulianDateNow = Cesium.JulianDate.now();
		const JulianDateStart = Cesium.JulianDate.addDays(JulianDateNow, -5, new Cesium.JulianDate());
		const JulianDateEnd = Cesium.JulianDate.addDays(JulianDateNow, 5, new Cesium.JulianDate());
		const clock = new Cesium.Clock({
		   startTime : JulianDateStart,
		   currentTime: JulianDateNow,
		   stopTime : JulianDateEnd,
		   clockRange : Cesium.ClockRange.LOOP_STOP
		});

		const viewer = new Cesium.Viewer("cesiumContainer", {
		  animation: false,
		  baseLayerPicker: false,
		  fullscreenButton: false,
		  geocoder: false,
		  homeButton: false,
		  infoBox: false,
		  sceneModePicker: false,
		  selectionIndicator: false,
		  navigationHelpButton: false,
		  scene3DOnly: true,
		  clockViewModel: new Cesium.ClockViewModel(clock),
		  automaticallyTrackDataSourceClocks: false,
		  baseLayer: new Cesium.ImageryLayer.fromProviderAsync(
			Cesium.IonImageryProvider.fromAssetId(3812)
		  ),
		});

		const scene = viewer.scene;
		const camera = viewer.camera;
		const timeline = viewer.timeline;

		scene.screenSpaceCameraController.enableRotate = false;
		scene.screenSpaceCameraController.enableTranslate = false;
		scene.screenSpaceCameraController.enableZoom = false;
		scene.screenSpaceCameraController.enableTilt = false;
		scene.screenSpaceCameraController.enableLook = false;

		function fit_earth(to_clock) {
		  let time = Cesium.JulianDate.toGregorianDate(to_clock.currentTime);
		  let seconds = time.second + time.minute * 60 + time.hour * 3600;
		  let max = 60*60*24;
		  camera.flyTo({
			  destination : Cesium.Cartesian3.fromDegrees(((max - seconds) * 360 / max) + 90, 180, camera.positionCartographic.height),
			  duration : 0
		  });
		  camera.lookLeft(Cesium.Math.toRadians(20));
		  datetime.innerHTML = time.year +'-' + pad(time.month) + '-' + pad(time.day) + ' ' + pad(time.hour) + ':' + pad(time.minute);
		  
		  let style = document.getElementById('switch-style');
		  let sheet = style.sheet ? style.sheet : style.styleSheet;
		  if(sheet.cssRules) {
			for(let i = 0; i < sheet.cssRules.length; i++) {
			  sheet.deleteRule(i);
			} 
		  }
		  sheet.insertRule('#graph rect.forecast_' + time.year + '_' + time.month + '_' + time.day + '_' + time.hour + ' { display: block !important; }', sheet.cssRules.length);
		  graph.style.marginRight = (-columnWidth * ((clock.stopTime.dayNumber * 3600 *24 + clock.stopTime.secondsOfDay - clock.currentTime.dayNumber * 3600 *24 - clock.currentTime.secondsOfDay) / 3600 + 8.5)) + 'px';
		}
		
		function fit_graph() {
			columnWidth = graph.getBoundingClientRect().width / (columnLength - 1);
			graphLegendWrapper.style.marginLeft = (-forecastSize * columnWidth) + 'px';
			graph.style.marginRight = (-columnWidth * ((clock.stopTime.dayNumber * 3600 *24 + clock.stopTime.secondsOfDay - clock.currentTime.dayNumber * 3600 *24 - clock.currentTime.secondsOfDay) / 3600 + 8.5)) + 'px';
		}
		
		window.onresize = fit_graph;

		viewer.timeline.addEventListener('settime', function (e) {
		  fit_earth(e.clock);
		}, false);

		const imageryLayers = viewer.imageryLayers;
		const nightLayer = imageryLayers.get(0);
		const dayLayer = Cesium.ImageryLayer.fromProviderAsync(
		  Cesium.IonImageryProvider.fromAssetId(3845)
		);
		imageryLayers.add(dayLayer);
		imageryLayers.lowerToBottom(dayLayer);

		dayLayer.show = true;
		viewer.scene.globe.enableLighting = true;
		viewer.clock.shouldAnimate = true;
		nightLayer.dayAlpha = 0.0;

		fit_earth(clock);
    </script>
  </body>
</html>
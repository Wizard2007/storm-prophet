<?php
header("Content-Type: text/xml");
$date = new DateTime();
?>
<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>Storm Prophet</title>
	<id>https://storm-prophet.synergist.kiev.ua/</id>
	<link rel="alternate" href="https://storm-prophet.synergist.kiev.ua/"/>
	<link href="https://storm-prophet.synergist.kiev.ua/rss.php" rel="self"/>
	<updated><?php $date->format(DateTime::DATE_RFC7231); ?></updated>
	<author>
		<name>Storm Prophet</name>
	</author>
	<entry>
		<title>Magnetic storm alert</title>
		<link rel="alternate" type="text/html" href="https://storm-prophet.synergist.kiev.ua/"/>
		<id>https://storm-prophet.synergist.kiev.ua/</id>
		<published><?php $date->format(DateTime::DATE_RFC7231); ?></published>
		<updated><?php $date->format(DateTime::DATE_RFC7231); ?></updated>
		<content type="html">A moderate magnetic storm is expected in 16 hours with a 95% probability</content>
	</entry>
</feed>
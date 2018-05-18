<?php
echo "
	<html>
	<head>
	<meta charset='UTF-8'>
	<link rel='stylesheet' type='text/css' href='style.css'>
	<link href='https://fonts.googleapis.com/css?family=Hind:300,400|Playfair+Display' rel='stylesheet'>
	</head>
	<body>
	<div id='content'>
";
//$secret = "nfscg";

$secret = (isset($_POST['secret'])) ? $_POST['secret']:null;

if(!$secret){
	print_form();
}

function print_form(){
	echo "<h1>Nyckel</h1><form method='post'>
		Fyll i din nyckel: <input type='password' name='secret'>
		<input type='submit' value='Visa studieresultat'>
	</form>";	
	die();
}


$csv = "https://docs.google.com/spreadsheets/d/e/2PACX-1vTo0Pd7EVue30BPN85Tb53RDTM3Ov6kr38DyZdjZ5KIiiiZpvc57FJinR31XB2wl-lZKqHmQhY-MF9b/pub?gid=0&single=true&output=csv"; //Här ska rätt URL in

$row = 1;
if (($handle = fopen($csv, "r")) !== FALSE) {
    while (($d = fgetcsv($handle, 1000, ",")) !== FALSE) {
    	if($row == 1) $titles = $d;
    	$row++;
    	if($d[0] == $secret){ 
    		$data = $d; 
    		$login = true;
    		break;
    	}
    }
    fclose($handle);
}

foreach($titles as $k => $v){
	$info[$v] = $data[$k];
}

echo json_encode($info);

if(!isset($login)){
	echo "Nyckeln hittades inte, försök igen...";
	print_form();
	die();
}
/*
echo "
	<h1>Dina studieresultat</h1>
	<p>Nedan listas dina betyg på varje examinationsmoment samt slutbetyg på de kurser som avslutats.</p>
	<ul>
		<li>G = Godkänd</li>
		<li>VG = Väl godkänd</li>
		<li>IG = Icke godkänd</li>
		<li>K = Kompletering</li>
		<li>S = Inlämnad men inte rättad</li>
		<li>SK = Inlämnad komplettering men inte rättad</li>
		<li>X = uppgiften har inte lämnats in</li>
	</ul>
	<p>Observera att inlämningar inte registreras här direkt eftersom den hanteringen sker manuellt.</p>
	<p>Uppgifterna numreras och döps i stil med u1 för uppgift 1 eller gu1 för gruppuppgift 1.</p>
	<p>De rättade uppgifterna och feedback på dessa kan du hitta på den här länken som är din personliga och som du inte bör sprida eftersom den är publik utan inloggning: <a href='{$info['dropbox']}'>{$info['dropbox']}</a>
";

$low = "#E97F02";
$medium = "#C4E071";
$high = "#91C46C";

$status = substr($info['status'],0,strlen($info['status'])-1);

if($status < 60){
	$color = $low;
}elseif($status < 90){
	$color = $medium;
}else{
	$color = $high;
}

echo "
<h2>Övergripande status</h2>
<p>Så här ligger du till som helhet i utbildningen baserat på andelen examinationsmoment du är klar med.</p>
	<div style='background: #ddd; padding: 4px;'>
		<div style='width: {$info['status']}; background: $color; text-align: right; color: #fff;'>{$info['status']}&nbsp;</div>
	</div>
";


$courses = [
	'K1' => [
		'name' => '1. Introduktion till digitala kommunikationskalaner'
	],
	'K2' => [
		'name' => '2. Juridik inom digitala medier'
	],
	'K3' => [
		'name' => '3. Digital kommunikation'
	],
	'K4' => [
		'name' => '4. Presentationsteknik och paketering'
	],
	'K5' => [
		'name' => '5. Digital strategi'
	],
	'K6' => [
		'name' => '6. Taktisk planering av digital kommunikation'
	],
	'K7' => [
		'name' => '7. Dialog, krishantering och etik'
	]
];

echo "<h2>Träffar</h2>";
print_course("T", $info);


echo "<h2>Kurser</h2>";

foreach ($courses as $key => $value) {
	echo "<h3>{$value['name']}</h3>";
	print_course($key, $info);
}


function print_course($course_id, $info){
	$course_data = array_filter($info, function($key) use (&$course_id){ return substr($key,0,strlen($course_id)) == $course_id;}, ARRAY_FILTER_USE_KEY);
	$headers = "";
	$values = "";
	foreach($course_data as $k => $v){
		$headers .= "<td>".substr($k,strlen($course_id))."</td>";
		$class = ($v == 'X') ? "IG":$v;
		$values .= "<td class='$class'>".$v."</td>";
	}
	echo "
	<table class='grades'>
		<tr class='head'>$headers</tr>
		<tr>$values</tr>
	</table>
	";
}
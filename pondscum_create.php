#!/usr/bin/php
<?php
include('pondscum.php');

if ($argc < 1 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
	print "Usage: ";
	exit;
}

$lily = array();
$output = "";
foreach ($argv as $index=>$arg) {
	if ($index < 1) {
		continue;
	} else if ($index == 1 ) {
		$lily = processFile($arg);
	} else {
		list($key, $value) = explode("=", $arg);
		if ($key == 'output') {
			$output = $value;
		} else {
			$lily['outputoptions'][$key] = $value;
		}
	}
}
$lily = buildLayout($lily);
// print_r($lily);

// $output = createOutput($lily);
file_put_contents($output, createOutput($lily));

?>

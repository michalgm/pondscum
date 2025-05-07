#!/usr/bin/php
<?php
include('../pondscum.php');

$lilydir = "../blo/";
$output = "./output/";
$dirs = array(
	'current'=>'sheetmusic',
	'oldcharts'=>'sheetmusic_archive',
	'working'=>'sheetmusic_working'
);
$lilies = array();

if (isset($argv[1])) { 
	if($argv[1] == 'all') { 
		foreach(array_keys($dirs) as $dir) {
			$outdir = $output.$dirs[$dir];
			rrmdir("$outdir");
			mkdir("$outdir");

			$dirh = opendir($dir);
			while (($file = readdir($dirh)) !== false) {
				if ($file == 'include.ly' || ! preg_match("/\.ly$/", $file)) { continue; }
				$lilies[] = processFile("$dir/$file");
			}
		}
	} else {
		array_shift($argv);
		foreach($argv as $file) {
			if ($file == 'include.ly' || ! preg_match("/\.ly$/", $file)) { continue; }
			$lilies[] = processFile(preg_replace("/^\.+\//", "", $file));
		}
	}
} else {
	print "include lilypond files as arguments, or 'all' for all songs\n\n"; 
	exit;
}

usort($lilies, 'lilysort');
$index = "";
foreach ($lilies as $lily) { 
	$musicdir = "";
	if ($lily) { 
		#set current date as tagline
		$lily['source'] = preg_replace("/^\s+tagline ?=.*$/m", "", $lily['source']);
		$lily['source'] = preg_replace("/\\\header\s*{/", "\\header { \n\ttagline = ".date('"n/j/Y"'), $lily['source']);

		$title = str_replace(" ", "_", $lily['title']);
		$pathinfo = pathinfo($lily['path']); 
		$musicdir = $dirs[$pathinfo['dirname']];
		if (! $musicdir) { die("Unknown directory $pathinfo[dirname]"); }
		$musicdir = "$output/$musicdir/";
		$dir = "$musicdir/".$title;
		print getcwd()."\n";
		print "$dir\n";
		if (is_dir($dir)) {
			rrmdir($dir);
		}
		mkdir($dir);
		print "$title: ";
		foreach (array('midi', 'score', 'source') as $part) {
			print "\n$part\n ";
			mkdir("$dir/$part");
			$lily['outputoptions']['key'] = "";
			generateFile($lily, $title, $part);
		}
		if (isset($lily['description'])) {
			file_put_contents("$dir/description.txt", $lily['description']);
		}
		foreach (array_keys($lily['parts']) as $part) {
			$lily['outputoptions']['part'] = $part;
			if ($part == 'words' || $part == 'lyrics') { 
				mkdir("$dir/$part");
				generateFile($lily, $title, $part);
			} else {
				mkdir("$dir/$part");
				foreach (array_keys($keys) as $key) {
					$lily['outputoptions']['key'] = $key;
					foreach ($clefs as $clef) {
						$lily['outputoptions']['clef'] = $clef;
						if ($clef == 'alto' || $clef == 'tenor') { continue; }
						if ($clef == 'bass' && $key != 'C') { continue; }
						foreach (array_keys($layouts) as $layout) {
							$lily['outputoptions']['page'] = $layout;
							$lily['outputoptions']['octave'] = 0;
							$filename = generateFile($lily, $title, $part);
							print "\t$filename\n";
						}
					}
				}
			}
		}
		print "$title.zip\n";
		chdir("$musicdir");
		system("zip -qr \"$title/$title\".zip	\"$title\"");
		chdir("../../");
		print "\n";
	}
}

function generateFile($lily, $title, $part) {
	global $musicdir;
	$dir = "$musicdir/$title/$part/";
	$lily['outputoptions']['part'] = $part;
	$lily = buildLayout($lily);
	if ($part == 'midi') {
		$ext = 'mid';
	} else if ($part == 'source') { 
		$ext = 'ly';
	} else { $ext = 'pdf'; }
	if ($ext == 'pdf') {
		$title .= "-$part";
		if ($part != 'score') {
			$title .= "-".$lily['outputoptions']['key']."-".$lily['outputoptions']['clef']."_clef-".$lily['outputoptions']['page'];
		}	
	}

	$filename = "$dir/$title.$ext";
	$output = createOutput($lily);
	file_put_contents($filename, $output);
	return $filename;
}

function rrmdir($path) {
	return is_file($path)?
		@unlink($path):
		array_map('rrmdir',glob($path.'/*'))==@rmdir($path)
	;
}

?>

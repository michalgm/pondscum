#!/usr/bin/php
<?php
include('../pondscum.php');

$lilydir = "../blo/";
$newmusicdir = "sheetmusic/";
$oldmusicdir = "sheetmusic_archive/";
$workingmusicdir = "sheetmusic_working/";
$musicdir = "";
if (isset($argv[1])) { 
	if($argv[1] == 'all') { 
		rrmdir($musicdir);
		mkdir($musicdir);

		$dirh = opendir($lilydir);
		while (($file = readdir($dirh)) !== false) {
			if ($file == 'include.ly' || ! preg_match("/\.ly$/", $file)) { continue; }
			$lilies[] = processFile($file, $lilydir);
		}
	} else {
		array_shift($argv);
		foreach($argv as $song) {
			#$song = preg_replace("/\.ly$/", "", $song);
			$lilies[] = processFile($song);
		}
	}
} else {
	print "include lilypond files as arguments, or 'all' for all songs\n\n"; 
	exit;
}

usort($lilies, 'lilysort');
$index = "";
foreach ($lilies as $lily) { 
	if ($lily) { 
		$title = str_replace(" ", "_", $lily['title']);
		if (strpos($lily['path'], "oldcharts") !== false) { 
			$musicdir = $oldmusicdir;
		} else if (strpos($lily['path'], "working") !== false) { 
			$musicdir = $workingmusicdir;
		} else { 
			$musicdir = $newmusicdir;
		}
		$dir = "$musicdir/".$title;
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
		foreach ($lily['parts'] as $part) {
			$lily['outputoptions']['part'] = $part;
			if ($part == 'words' || $part == 'lyrics') { 
				mkdir("$dir/$part");
				generateFile($lily, $title, $part);
			} else {
				mkdir("$dir/$part");
				foreach (array_keys($keys) as $key) {
					$lily['outputoptions']['key'] = $key;
					if ($key == 'F') { continue; }
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
		chdir("../");
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

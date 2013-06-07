<?php
$list = array();
$songs = scandir("$dir");
foreach($songs as $song) { 
	if ($song == '.' || $song == "..") { continue; }
	$songinfo = array();
	$title = ucwords(str_replace("_", " ", $song));
	$songinfo['title'] = $title;
	$songinfo['parts'] = array();
	$songinfo['files'] = array();
	$songinfo['dir'] = "$dir$song";
	if (file_exists("$dir$song/description.txt")) { 
		$songinfo['description'] = file_get_contents("$dir$song/description.txt");
	}	
	if (file_exists("$dir$song/$song.zip")) { 
		$songinfo['zip'] = "$dir$song/$song.zip";
	}
	$parts = scandir("$dir$song");
	foreach ($parts as $part) { 
		if (stripos($part, '.pdf')) { 
			$songinfo['files'][$part] = $part;
			continue;
		}
		if (stripos($part, '.mus')) { 
			$songinfo['mus'] = "$dir$song/$part";
			continue;
		}
		$partdir = "$dir/$song/$part/";
		if ($part == '.' || $part == ".." || ! is_dir($partdir)) { continue; }
		$partinfo = array(
			'name'=>ucwords(str_replace('_', ' ', $part)),	
			'keys'=>array()
		);

		$files = scandir("$partdir");
		foreach ($files as $file) { 
			if ($file == '.' || $file == "..") { continue; }
			$file = "$partdir$file";
			if ($part == 'score') { 
				$songinfo['score'] = $file;
			} elseif($part == 'source') {
				$songinfo['source'] = $file;
			} elseif($part == 'midi') {
				$songinfo['midi'] = $file;
			} elseif($part == 'words' || $part == 'lyrics') {
				$songinfo['lyrics'] = $file;
			} else { 
				$info = pathinfo($file);
				$filename = $info['filename'];
				if (strtolower($info['extension']) == 'pdf') { 
					preg_match("/$part-([^\-\.]+)-?([^\-\.]+)?-?([^\-\.]+)?$/i", $filename, $pieces);
					if(! isset($pieces[1])) { 
						print "huh? $filename\n";
					}
					list($null, $key, $clef, $page) = $pieces;
					$page = $page ? $page : 'letter'; 
					if (! isset($partinfo['keys']["$key-$clef"])) { 
						$partinfo['keys']["$key-$clef"] = array('name'=>ucwords("$key - ".str_replace('_', ' ', $clef)));
					}
					$partinfo['keys']["$key-$clef"][$page] = "$file";
				}
			}
		}
		if (! in_array("$part", array('score', 'source', 'midi', 'words', 'lyrics'))) { 
			$songinfo['parts'][$part] =  $partinfo;
			ksort($songinfo['parts'][$part]['keys']);
		}
	}
	array_push($list, $songinfo);
}
?>
<script type='text/javascript'>
</script>
<?php
print "<div id='songlist'>";
$counter = 0;
foreach ($list as $song) { 
	$title = $song['title'];
	$link_title = urlencode($title);
	$counter++;
	$id = "song_$counter";
	print "
		<div id='$id'>
		<h3><a href='#$link_title' name='$link_title'>$title</a></h3>";
	if(isset($song['description'])) {
		print "<div class='description'>$song[description]</div>";
	}
	print "
	<a href='#' onclick=\"$$('#$id .songinfo')[0].toggle(); return false;\">Sheet Music</a>
	<ul style='display:none;' class='songinfo'>";
	if (isset($song['score'])) {
		print "\t\t<li><a class=\"pdf\" href=\"$song[score]\">Score</a></li>\n";
	}
	if (isset($song['source'])) {
		print "\t\t<li><a class=\"lily\" href=\"$song[source]\">Lilypond Source</a></li>\n";
	}
	if (isset($song['mus'])) {
		print "\t\t<li><a class=\"mus\" href=\"$song[mus]\">Finale Source</a></li>\n";
	}
	if (isset($song['midi'])) {
		print "\t\t<li><a class=\"midi\" href=\"$song[midi]\">Midi</a></li>\n";
	}
	if (isset($song['lyrics'])) {
		print "\t\t<li><a class=\"pdf\" href=\"$song[lyrics]\">Lyrics</a></li>\n";
	}
	if (isset($song['zip'])) {
		print "\t\t<li><a class=\"zip\" href=\"$song[zip]\">Zipfile containing all files</a></li>\n";
	}
	if (reset($song['files'])) { 
		print "
		<li>Files
		<ul class='files'>
		";
		foreach($song['files'] as $file) { 
			print "\t\t<li><a class=\"pdf\" href=\"$song[dir]/$file\">$file</a></li>\n";
		}
		print "\n\t</ul>\n";
	}
	if (reset($song['parts'])) { 
		print "
			<li>Parts
			<ul class='parts'>
		";
		foreach($song['parts'] as $part) { 
			print "
				<li class='part'>$part[name]
				<ul class='keys'>";
			foreach($part['keys'] as $key) { 
				print "
					<li class='key'>$key[name]
					<ul class='formats'>	";
				foreach(array_keys($key) as $page) {
					if ($page == 'name') { continue; }
					print "
						<li class=\"format\"><a class=\"pdf\" href=\"$key[$page]\">$page</a></li>";
				}
				print "
					</ul></li>";
			}
			print "
				</ul></li>";
		}
		print "
			</ul></li>";
	}
	print "
	</ul></div>";
}
print "</div>";			   		
#print_r($list);

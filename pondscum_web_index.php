<?php

function printHTTPHeader($lily, $part) {
  global $_REQUEST;
  if (isset($_REQUEST['debug'])) {
    header('Content-Type: text/html; charset=utf-8');
    return;
  }
  $filename = $lily['filename'];
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

  if ($part =='midi') {
    header('Content-type: audio/midi');
    header('Content-Disposition: attachment; filename="'.$filename.'.mid"');
  } elseif ($part =='source') {
    header('Content-type: text/x-lilypond');
    header('Content-Disposition: attachment; filename="'.$filename.'.ly"');
  } else {
    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename="'.$filename.'.pdf"');
  }
}

function printFileSelect($lily) {
  global $keys, $clefs, $octaves, $layouts;
  $output = "<a name='$lily[file]'><div style='position: relative;'><form>
    <input name='file' type='hidden' value='$lily[file]'>
    <input name='dir' type='hidden' value='$lily[dir]'>
    <b>".html_encode($lily['title'])."</b>";
  $partselect = "";
  $keyselect = "";
  $octaveselect = "";
  $clefselect = "";
  $layoutselect = "";
  foreach ($lily['parts'] as $part) {
    $partselect .= "<option value='$part'>".ucwords($part)."</option>";
  }
  foreach (array_keys($keys) as $key) { $keyselect .= "<option value='$key'>".ucwords($key)."</option>"; }
  foreach ($clefs as $clef) { $clefselect .= "<option value='$clef'>".ucwords($clef)."</option>"; }
  foreach (array_keys($octaves) as $octave) { $default = $octave == 0 ? "selected='selected'" : ''; $octaveselect .= "<option $default value='$octave'>".ucwords($octave)."</option>"; }
  foreach (array_keys($layouts) as $layout) { $layoutselect .= "<option value='$layout'>".ucwords($layout)."</option>"; }

  $output.= "<br><a href='?file=$lily[file]&dir=$lily[dir]&part=midi'>play midi</a>";
  $output.= " - <a href='?file=$lily[file]&dir=$lily[dir]&part=score'>score </a>";
  $output.= " - <a href='?file=$lily[file]&dir=$lily[dir]&part=source'>source</a>";
  $output.= "<div class='options' style='position: relative;'> <div class='left'>
        <div class='part'>Part: <select name='part'>$partselect</select></div>
        <div class='key'>Key: <select name='key'>$keyselect</select></div>
        <div class='clef'>Clef: <select name='clef'>$clefselect</select></div>
      </div>
      <div class='right' style='position: absolute; left: 180px; top: 0px;'>
        <div class='octave'>Octave: <select name='octave'>$octaveselect</select></div>
        <div class='layout'>Layout: <select name='page'>$layoutselect</select></div>
    ";
  if ($lily['words']) {
    $output.= "<div class='lyrics'>Include Lyrics: <input type='checkbox' name='words'></div>";
  }
  $output.= "<div class='naturalize'>Naturalize Accidentals: <input type='checkbox' name='naturalize' checked='1'></div>";
  $output.= " 	<br>Debug <input type='checkbox' name='debug' value='1'><br>
        </div>
        <input style='margin: auto; text=align: center;' type='submit' value='Download PDF'>
      </form></div></div>
      ";
  return $output;
}

function html_encode($var) {
    return htmlentities($var, ENT_QUOTES, 'UTF-8') ;
}

?>

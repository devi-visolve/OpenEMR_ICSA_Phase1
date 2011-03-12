<?php
// Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");

function scanned_notes_report($pid, $useless_encounter, $cols, $id) {
 global $webserver_root, $web_root, $encounter;

 // In the case of a patient report, the passed encounter is vital.
 $thisenc = $useless_encounter ? $useless_encounter : $encounter;

 $count = 0;

 $data = sqlQuery("SELECT * " .
  "FROM form_scanned_notes WHERE " .
  "id = '$id' AND activity = '1'");

 if ($data) {
  echo "<table cellpadding='0' cellspacing='0'>\n";

  if ($data['notes']) {
   echo " <tr>\n";
   echo "  <td valign='top'><span class='bold'>Comments: </span><span class='text'>";
   echo nl2br($data['notes']) . "</span></td>\n";
   echo " </tr>\n";
  }

  for ($i = -1; true; ++$i) {
    $suffix = ($i < 0) ? "" : "-$i";
    $imagepath = $GLOBALS['OE_SITE_DIR'] .
      "/documents/$pid/encounters/${thisenc}_$id$suffix.jpg";
    $imageurl  = "$web_root/sites/" . $_SESSION['site_id'] .
      "/documents/$pid/encounters/${thisenc}_$id$suffix.jpg";
    if (is_file($imagepath)) {
      echo " <tr>\n";
      echo "  <td valign='top'>\n";
      echo "   <img src='$imageurl' />\n";
      echo "  </td>\n";
      echo " </tr>\n";
    }
    else {
      if ($i >= 0) break;
    }
  }

  echo "</table>\n";
 }
}
?>

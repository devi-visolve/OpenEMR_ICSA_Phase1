<?php
 include_once("../../globals.php");
 include_once("$srcdir/forms.inc");
 include_once("$srcdir/billing.inc");
 include_once("$srcdir/pnotes.inc");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/report.inc");
 require_once("$srcdir/options.inc.php");
 include_once(dirname(__file__) . "/../../../library/classes/Document.class.php");
 include_once(dirname(__file__) . "/../../../library/classes/Note.class.php");

 $N = 6;
 $first_issue = 1;

 function postToGet($arin) {
  $getstring="";
  foreach ($arin as $key => $val) {
   $getstring.=urlencode($key)."=".urlencode($val)."&";
  }
  return $getstring;
 }
?>
<html>
<head>
<?php html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>

<body bgcolor="#ffffff" topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<p>
<?php
 if (sizeof($_GET) > 0) {
  $ar = $_GET;
 } else {
  $ar = $_POST;
 }

 $titleres = getPatientData($pid, "fname,lname,providerID");
 /********************************************************************
 $sql = "select * from facility where billing_location = 1";
 ********************************************************************/
 if ($_SESSION['pc_facility']) {
 	$sql = "select * from facility where id=" . $_SESSION['pc_facility'];
 } else {
 	$sql = "select * from facility where billing_location = 1";
 }
 /*******************************************************************/
 $db = $GLOBALS['adodb']['db'];
 $results = $db->Execute($sql);
 $facility = array();
 if (!$results->EOF) {
  $facility = $results->fields;
 }

 $practice_logo = "../../../custom/practice_logo.gif";
 if (file_exists($practice_logo)) {
  echo "<img src='$practice_logo' align='left'>\n";
 }
?>
<h2><?=$facility['name']?></h2>
<?=$facility['street']?><br>
<?=$facility['city']?>, <?=$facility['state']?> <?=$facility['postal_code']?><br clear='all'>
<?=$facility['phone']?><br>
</p>

<a href="javascript:window.close();"><font class=title><?print $titleres{"fname"} . " " . $titleres{"lname"};?></font></a><br>
<span class=text><? xl('Generated on','e'); ?>: <?print date("Y-m-d");?></span>
<br><br>

<?
 //$provider = getProviderName($titleres['providerID']);

 //print "Provider: " . $provider  . "</br>";

 $inclookupres = sqlStatement("select distinct formdir from forms where pid='$pid'");
 while($result = sqlFetchArray($inclookupres)) {
  include_once("{$GLOBALS['incdir']}/forms/" . $result{"formdir"} . "/report.php");
 }

 $printed = false;

 foreach ($ar as $key => $val) {
  if (!$printed && strpos($key, "newpatient_") === 0) {
   $billing = getPatientBillingEncounter($pid, $val);
   foreach ($billing as $b) {
    if(!empty($b['provider_name'])) {
     echo "Provider: " . $b['provider_name'] . "<br>";
     $printed = true;
     break;
    }
   }
  }
 }

 foreach ($ar as $key => $val) {

  /****
  // WTF??  Redo this.
  if (!empty($ar['newpatient'])){
   foreach ($ar['newpatient'] as $be) {
    $ta = split(":", $be);
    $billing = getPatientBillingEncounter($pid, $ta[1]);
    if(!$printed) {
     foreach ($billing as $b) {
      if(!empty($b['provider_name'])) {
       echo "Provider: " . $b['provider_name'] . "<br>";
       $printed = true;
       break;
      }
     }
    }
   }
  }
  ****/

  if (stristr($key,"include_")) {
  //print "include: $val<br>\n";

   if ($val == "demographics") {

    print "<br><font class=bold>".xl('Patient Data').":</font><br>";
    printRecDataOne($patient_data_array, getRecPatientData ($pid), $N);

   } elseif ($val == "history") {

    print "<br><font class=bold>".xl('History Data').":</font><br>";
    printRecDataOne($history_data_array, getRecHistoryData ($pid), $N);

   } elseif ($val == "employer") {

    print "<br><font class=bold>".xl('Employer Data').":</font><br>";
    printRecDataOne($employer_data_array, getRecEmployerData ($pid), $N);

   } elseif ($val == "insurance") {

    print "<br><font class=bold>".xl('Primary Insurance Data').":</font><br>";
    printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"primary"), $N);		
    print "<font class=bold>".xl('Secondary Insurance Data').":</font><br>";	
    printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"secondary"), $N);
    print "<font class=bold>".xl('Tertiary Insurance Data').":</font><br>";
    printRecDataOne($insurance_data_array, getRecInsuranceData ($pid,"tertiary"), $N);

   } elseif ($val == "billing") {

    print "<br><font class=bold>".xl('Billing Information').":</font><br>";
    if (count($ar['newpatient']) > 0) {
     $billings = array();
     echo "<table>";
     echo "<tr><td width=\"400\" class=bold>Code</td><td class=bold>".xl('Fee')."</td></tr>\n";
     $total = 0.00;
     $copays = 0.00;
     foreach ($ar['newpatient'] as $be) {
      $ta = split(":",$be);
      $billing = getPatientBillingEncounter($pid,$ta[1]);
      $billings[] = $billing;
      foreach ($billing as $b) {
       echo "<tr>\n";
       echo "<td class=text>";
       echo $b['code_type'] . ":\t" . $b['code'] . "&nbsp;&nbsp;&nbsp;" . $b['code_text'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
       echo "</td>\n";
       echo "<td class=text>";
       echo $b['fee'];
       echo "</td>\n";
       echo "</tr>\n";
       $total += $b['fee'];
       if ($b['code_type'] == "COPAY") {
        $copays += $b['fee'];
       }
      }
     }
     echo "<tr><td>&nbsp;</td></tr>";
     echo "<tr><td class=bold>".xl('Sub-Total')."</td><td class=text>" . sprintf("%0.2f",$total) . "</td></tr>";
     echo "<tr><td class=bold>".xl('Paid')."</td><td class=text>" . sprintf("%0.2f",$copays) . "</td></tr>";
     echo "<tr><td class=bold>".xl('Total')."</td><td class=text>" . sprintf("%0.2f",($total - $copays)) . "</td></tr>";
     echo "</table>";
     echo "<pre>";
     //print_r($billings);
     echo "</pre>";
    }
    else {
     printPatientBilling($pid);
    }


    /****
		} elseif ($val == "allergies") {
			print "<font class=bold>".xl('Patient Allergies').":</font><br>";
			printListData($pid, "allergy", "1");
		} elseif ($val == "medications") {
			print "<font class=bold>".xl('Patient Medications').":</font><br>";
			printListData($pid, "medication", "1");
		} elseif ($val == "medical_problems") {
				print "<font class=bold>".xl('Patient Medical Problems').":</font><br>";
				printListData($pid, "medical_problem", "1");
    ****/

   } elseif ($val == "immunizations") {
    print "<font class=bold>".xl('Patient Immunization').":</font><br>";
    $sql = "select i1.immunization_id as immunization_id, if(i1.administered_date,concat(i1.administered_date,' - ') ,substring(i1.note,1,20) ) as immunization_data from immunizations i1 where i1.patient_id = $pid order by administered_date desc";
    $result = sqlStatement($sql);
    while ($row=sqlFetchArray($result)) {
     echo "<span class=text> " . $row{'immunization_data'} .
	  generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']) .
	  "</span><br>\n";
    }

   } elseif ($val == "notes") {

    print "<font class=bold>".xl('Patient Notes').":</font><br>";
    printPatientNotes($pid);

   } elseif ($val == "transactions") {

    print "<font class=bold>".xl('Patient Transactions').":</font><br>";
    printPatientTransactions($pid);

   }

  } else {

   if ($key == "documents") {
    echo "<br><br>";
    foreach($val as $valkey => $valvalue) {
     $document_id = $valvalue;
     if (!is_numeric($document_id)) continue;
     $d = new Document($document_id);
     $fname = basename($d->get_url());
     $extension = substr($fname, strrpos($fname,"."));
     echo "Document '" . $fname ."'<br>";
     $notes = Note::notes_factory($d->get_id());
     echo "<table>";
     foreach ($notes as $note) {
      echo '<tr>';
      echo '<td>Note #' . $note->get_id() . '</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>Date: '.$note->get_date().'</td>';
      echo '</tr>';
      echo '<tr>';
      echo '<td>'.$note->get_note().'<br><br></td>';
      echo '</tr>';
     }
     echo "</table>";
     if ($extension == ".png" || $extension == ".jpg" || $extension == ".jpeg" || $extension == ".gif") {
      echo '<img src="' . $GLOBALS['webroot'] . "/controller.php?document&retrieve&patient_id=&document_id=" . $document_id . '"><br><br>';
     }
     else {
      // echo "<b>NOTE</b>: ".xl('Document')." '" . $fname ."'".xl('cannot be displayed inline becuase its type is not supported by the browser.')."<br><br>";

      // This requires ImageMagick to be installed.
      $from_file = $d->get_url_filepath();
      $to_file = substr($from_file, 0, strrpos($from_file, '.')) . '_converted.jpg';
      if (! is_file($to_file)) exec("convert -density 200 '$from_file' -append -resize 850 '$to_file'");
      if (is_file($to_file)) {
       $to_url = $GLOBALS['webroot'] . "/sites/" . $_SESSION['site_id'] .
        "/documents/$pid/" . basename($to_file);
       echo "<img src='$to_url'><br><br>\n";
      } else {
       echo "<b>NOTE</b>: " . xl('Document') . "'" . $fname . "' " .
        xl('cannot be converted to JPEG. Perhaps ImageMagick is not installed?') .
        "<br><br>";
      }

     }
    }
   }

   else if (strpos($key, "issue_") === 0) {

    if ($first_issue) {
     $first_issue = 0;
     echo "<br>\n";
    }
    preg_match('/^(.*)_(\d+)$/', $key, $res);
    $rowid = $res[2];
    $irow = sqlQuery("SELECT type, title, comments, diagnosis " .
     "FROM lists WHERE id = '$rowid'");
    $diagnosis = $irow['diagnosis'];
    echo "<span class='bold'>" . $irow['title'] . ":</span><span class='text'> " .
     $irow['comments'] . "</span><br>\n";
    // Show issue's chief diagnosis and its description:
    if ($diagnosis) {
     $crow = sqlQuery("SELECT code_text FROM codes WHERE " .
      "code = '$diagnosis' AND " .
      "(code_type = 2 OR code_type = 4 OR code_type = 5)" .
      "LIMIT 1");
     echo "<span class='bold'>&nbsp;Diagnosis: </span><span class='text'>" .
      $irow['diagnosis'] . " " . $crow['code_text'] . "</span><br>\n";
    }

   }

   // Otherwise we have an "encounter form" form field whose name is like
   // dirname_formid, with a value which is the encounter ID.
   //
   else {

    $form_encounter = $val;
    preg_match('/^(.*)_(\d+)$/', $key, $res);
    $form_id = $res[2];
    $formres = getFormNameByFormdir($res[1]);
    $dateres = getEncounterDateByEncounter($form_encounter);
    if ($res[1] == 'newpatient') print "<br>\n";
    print "<span class='bold'>" . $formres{"form_name"} .
     "</span><span class=text>(" . date("Y-m-d",strtotime($dateres{"date"})) .
     ")" . "</span><br>\n";
    call_user_func($res[1] . "_report", $pid, $form_encounter, $N, $form_id);
    if ($res[1] == 'newpatient') {
     $bres = sqlStatement("SELECT date, code, code_text FROM billing WHERE " .
      "pid = '$pid' AND encounter = '$form_encounter' AND activity = 1 AND " .
      "(code_type = 'CPT4' OR code_type = 'OPCS') " .
      "ORDER BY date");
     while ($brow=sqlFetchArray($bres)) {
      echo "<span class='bold'>&nbsp;Procedure: </span><span class='text'>" .
        $brow['code'] . " " . $brow['code_text'] . "</span><br>\n";
     }
    }

   }
  }
 }

 print "</br></br>".xl('Signature').": _______________________________</br>";

?>

</body>
</html>

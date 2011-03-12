<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("$srcdir/options.inc.php");

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

if ($viewmode) {
  $id = $_REQUEST['id'];
  $result = sqlQuery("SELECT * FROM form_encounter WHERE id = '$id'");
  $encounter = $result['encounter'];
  if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
    echo "<body>\n<html>\n";
    echo "<p>" . xl('You are not authorized to see this encounter.') . "</p>\n";
    echo "</body>\n</html>\n";
    exit();
  }
}

// Sort comparison for sensitivities by their order attribute.
function sensitivity_compare($a, $b) {
  return ($a[2] < $b[2]) ? -1 : 1;
}

// get issues
$ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
  "pid = $pid AND enddate IS NULL " .
  "ORDER BY type, begdate");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Patient Encounter','e'); ?></title>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.4.3.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/overlib_mini.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>

<!-- pop up calendar -->
<style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>

<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 // Process click on issue title.
 function newissue() {
  dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 800, 600);
  return false;
 }

 // callback from add_edit_issue.php:
 function refreshIssue(issue, title) {
  var s = document.forms[0]['issues[]'];
  s.options[s.options.length] = new Option(title, issue, true, true);
 }

 function saveClicked() {
  var f = document.forms[0];

<?php if (!$GLOBALS['athletic_team']) { ?>
  var category = document.forms[0].pc_catid.value;
  if ( category == '_blank' ) {
   alert("<?php echo xl('You must select a visit category'); ?>");
   return;
  }
<?php } ?>

<?php if (false /* $GLOBALS['ippf_specific'] */) { // ippf decided not to do this ?>
  if (f['issues[]'].selectedIndex < 0) {
   if (!confirm('There is no issue selected. If this visit relates to ' +
    'contraception or abortion, click Cancel now and then select or ' +
    'create the appropriate issue. Otherwise you can click OK.'))
   {
    return;
   }
  }
<?php } ?>
  top.restoreSession();
  f.submit();
 }

$(document).ready(function(){
  enable_big_modals();
});

</script>
</head>

<?php if ($viewmode) { ?>
<body class="body_top">
<?php } else { ?>
<body class="body_top" onload="javascript:document.new_encounter.reason.focus();">
<?php } ?>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form method='post' action="<?php echo $rootdir ?>/forms/newpatient/save.php" name='new_encounter'
 <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?>>

<div style = 'float:left'>
<?php if ($viewmode) { ?>
<input type=hidden name='mode' value='update'>
<input type=hidden name='id' value='<?php echo $_GET["id"] ?>'>
<span class=title><?php xl('Patient Encounter Form','e'); ?></span>
<?php } else { ?>
<input type='hidden' name='mode' value='new'>
<span class='title'><?php xl('New Encounter Form','e'); ?></span>
<?php } ?>
</div>

<div>
    <div style = 'float:left; margin-left:8px;margin-top:-3px'>
      <a href="javascript:saveClicked();" class="css_button link_submit"><span><?php xl('Save','e'); ?></span></a>
      <?php if ($viewmode || !isset($_GET["autoloaded"]) || $_GET["autoloaded"] != "1") { ?>
    </div>

    <div style = 'float:left; margin-top:-3px'>
  <?php if ($GLOBALS['concurrent_layout']) { ?>
      <a href="<?php echo "$rootdir/patient_file/encounter/encounter_top.php"; ?>"
        class="css_button link_submit" onclick="top.restoreSession()"><span><?php xl('Cancel','e'); ?></span></a>
  <?php } else { ?>
      <a href="<?php echo "$rootdir/patient_file/encounter/patient_encounter.php"; ?>"
        class="css_button link_submit" target='Main' onclick="top.restoreSession()">
      <span><?php xl('Cancel','e'); ?>]</span></a>
  <?php } // end not concurrent layout ?>
  <?php } // end not autoloading ?>
    </div>
 </div>

<br> <br>

<table width='96%'>

 <tr>
  <td width='33%' nowrap class='bold'><?php xl('Consultation Brief Description','e'); ?>:</td>
  <td width='34%' rowspan='2' align='center' valign='center' class='text'>
   <table>

    <tr<?php if ($GLOBALS['athletic_team']) echo " style='visibility:hidden;'"; ?>>
     <td class='bold' nowrap><?php xl('Visit Category:','e'); ?></td>
     <td class='text'>
      <select name='pc_catid' id='pc_catid'>
	<option value='_blank'>-- Select One --</option>
<?php
 $cres = sqlStatement("SELECT pc_catid, pc_catname " .
  "FROM openemr_postcalendar_categories ORDER BY pc_catname");
 while ($crow = sqlFetchArray($cres)) {
  $catid = $crow['pc_catid'];
  if ($catid < 9 && $catid != 5) continue;
  echo "       <option value='$catid'";
  if ($viewmode && $crow['pc_catid'] == $result['pc_catid']) echo " selected";
  echo ">" . xl_appt_category($crow['pc_catname']) . "</option>\n";
 }
?>
      </select>
     </td>
    </tr>

    <tr>
     <td class='bold' nowrap><?php xl('Facility:','e'); ?></td>
     <td class='text'>
      <select name='facility_id'>
<?php

if ($viewmode) {
  $def_facility = $result['facility_id'];
} else {
  $dres = sqlStatement("select facility_id from users where username = '" . $_SESSION['authUser'] . "'");
  $drow = sqlFetchArray($dres);
  $def_facility = $drow['facility_id'];
}
$fres = sqlStatement("select * from facility where service_location != 0 order by name");
if ($fres) {
  $fresult = array();
  for ($iter = 0; $frow = sqlFetchArray($fres); $iter++)
    $fresult[$iter] = $frow;
  foreach($fresult as $iter) {
?>
       <option value="<?php echo $iter['id']; ?>" <?php if ($def_facility == $iter['id']) echo "selected";?>><?php echo $iter['name']; ?></option>
<?php
  }
 }
?>
      </select>
     </td>
    </tr>

    <tr>
<?php
 $sensitivities = acl_get_sensitivities();
 if ($sensitivities && count($sensitivities)) {
  usort($sensitivities, "sensitivity_compare");
?>
     <td class='bold' nowrap><?php xl('Sensitivity:','e'); ?></td>
     <td class='text'>
      <select name='form_sensitivity'>
<?php
  foreach ($sensitivities as $value) {
   // Omit sensitivities to which this user does not have access.
   if (acl_check('sensitivities', $value[1])) {
    echo "       <option value='" . $value[1] . "'";
    if ($viewmode && $result['sensitivity'] == $value[1]) echo " selected";
    echo ">" . xl($value[3]) . "</option>\n";
   }
  }
  echo "       <option value=''";
  if ($viewmode && !$result['sensitivity']) echo " selected";
  echo ">" . xl('None'). "</option>\n";
?>
      </select>
     </td>
<?php
 } else {
?>
     <td colspan='2'><!-- sensitivities not used --></td>
<?php
 }
?>
    </tr>

    <tr<?php if (!$GLOBALS['gbl_visit_referral_source']) echo " style='visibility:hidden;'"; ?>>
     <td class='bold' nowrap><?php xl('Referral Source','e'); ?>:</td>
     <td class='text'>
<?php
  echo generate_select_list('form_referral_source', 'refsource', $viewmode ? $result['referral_source'] : '', '');
?>
     </td>
    </tr>

    <tr>
     <td class='bold' nowrap><?php xl('Date of Service:','e'); ?></td>
     <td class='text' nowrap>
      <input type='text' size='10' name='form_date' id='form_date' <?php echo $disabled ?>
       value='<?php echo $viewmode ? substr($result['date'], 0, 10) : date('Y-m-d'); ?>'
       title='<?php xl('yyyy-mm-dd Date of service','e'); ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
        id='img_form_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand'
        title='<?php xl('Click here to choose a date','e'); ?>'>
     </td>
    </tr>

    <tr<?php if ($GLOBALS['ippf_specific'] || $GLOBALS['athletic_team']) echo " style='visibility:hidden;'"; ?>>
     <td class='bold' nowrap><?php xl('Onset/hosp. date:','e'); ?></td>
     <td class='text' nowrap>
      <input type='text' size='10' name='form_onset_date' id='form_onset_date'
       value='<?php echo $viewmode ? substr($result['onset_date'], 0, 10) : date('Y-m-d'); ?>'
       title='<?php xl('yyyy-mm-dd Date of onset or hospitalization','e'); ?>'
       onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
        <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
        id='img_form_onset_date' border='0' alt='[?]' style='cursor:pointer;cursor:hand'
        title='<?php xl('Click here to choose a date','e'); ?>'>
     </td>
    </tr>

    <tr>
     <td class='text' colspan='2' style='padding-top:1em'>
<?php if ($GLOBALS['athletic_team']) { ?>
      <p><i>Click [Add Issue] to add a new issue if:<br />
      New injury likely to miss &gt; 1 day<br />
      New significant illness/medical<br />
      New allergy - only if nil exist</i></p>
<?php } ?>
     </td>
    </tr>

   </table>

  </td>

  <td class='bold' width='33%' nowrap>
    <div style='float:left'>
   <?php xl('Issues (Injuries/Medical/Allergy)','e'); ?>
    </div>
    <div style='float:left;margin-left:8px;margin-top:-3px'>
<?php if ($GLOBALS['athletic_team']) { // they want the old-style popup window ?>
      <a href="#" class="css_button_small link_submit"
       onclick="return newissue()"><span><?php echo htmlspecialchars(xl('Add')); ?></span></a>
<?php } else { ?>
      <a href="../../patient_file/summary/add_edit_issue.php" class="css_button_small link_submit iframe"
       onclick="top.restoreSession()"><span><?php echo htmlspecialchars(xl('Add')); ?></span></a>
<?php } ?>
    </div>
  </td>
 </tr>

 <tr>
  <td class='text' valign='top'>
   <textarea name='reason' cols='40' rows='12' wrap='virtual' style='width:96%'
    ><?php echo $viewmode ? htmlspecialchars($result['reason']) : $GLOBALS['default_chief_complaint']; ?></textarea>
  </td>
  <td class='text' valign='top'>
   <select multiple name='issues[]' size='8' style='width:100%'
    title='<?php xl('Hold down [Ctrl] for multiple selections or to unselect','e'); ?>'>
<?php
while ($irow = sqlFetchArray($ires)) {
  $list_id = $irow['id'];
  $tcode = $irow['type'];
  if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];

  if ($viewmode) {
    echo "    <option value='$list_id'";
    $perow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
      "pid = '$pid' AND encounter = '$encounter' AND list_id = '$list_id'");
    if ($perow['count']) echo " selected";
    echo ">$tcode: " . $irow['begdate'] . " " .
      htmlspecialchars(substr($irow['title'], 0, 40)) . "</option>\n";
  }
  else {
    echo "    <option value='$list_id'>$tcode: ";
    echo $irow['begdate'] . " " . htmlspecialchars(substr($irow['title'], 0, 40)) . "</option>\n";
  }
}
?>
   </select>

   <p><i><?php xl('To link this encounter/consult to an existing issue, click the '
   . 'desired issue above to highlight it and then click [Save]. '
   . 'Hold down [Ctrl] button to select multiple issues.','e'); ?></i></p>

  </td>
 </tr>

</table>

</form>

</body>

<script language="javascript">
/* required for popup calendar */
Calendar.setup({inputField:"form_date", ifFormat:"%Y-%m-%d", button:"img_form_date"});
Calendar.setup({inputField:"form_onset_date", ifFormat:"%Y-%m-%d", button:"img_form_onset_date"});
<?php
if (!$viewmode) {
  $erow = sqlQuery("SELECT count(*) AS count " .
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = '$pid' AND fe.date = '" . date('Y-m-d 00:00:00') . "' AND " .
    "f.formdir = 'newpatient' AND f.form_id = fe.id AND f.deleted = 0");
  if ($erow['count'] > 0) {
    echo "alert('" . xl('Warning: A visit was already created for this patient today!') . "');\n";
  }
}
?>
</script>

</html>

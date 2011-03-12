<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"

"http://www.w3.org/TR/html4/loose.dtd">

<?php

include_once("../../globals.php");

include_once("$srcdir/api.inc");

?>



<html>

<head>
<?php html_header_show();?>

<?php include("../../acog_printable_v.css"); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>



<?php

   $fres = sqlStatement("select * from form_plist where id=$id");

   $repdata = sqlFetchArray($fres);

   $fres=sqlStatement("select * from patient_data where pid=".$_SESSION["pid"]);

   if ($fres){

     $patient = sqlFetchArray($fres);

   }   

?>

<body class="body_top">

<table width="70%"  border="0" cellspacing="0" cellpadding="4">

  <tr>

    <td width="120" align="left" valign="bottom" class="srvCaption">Patient name:</td>

    <td align="left" valign="bottom" class="fibody5"><?php echo $patient{'fname'}.' '.$patient{'mname'}.' '.$patient{'lname'}; ?></td>

  </tr>

  <tr>

    <td width="120" align="left" valign="bottom" class="srvCaption">Birth date: </td>

    <td align="left" valign="bottom" class="fibody5"><?php  echo $patient{'DOB'};  ?></td>

  </tr>

  <tr>

    <td width="120" align="left" valign="bottom" class="srvCaption">ID No:</td>

    <td align="left" valign="bottom" class="fibody5"><?php echo $patient{'id'};  ?></td>

  </tr>

  <tr>

    <td width="120" align="left" valign="bottom" class="srvCaption">Date</td>

    <td align="left" valign="bottom" class="fibody5"><?php echo date('Y-m-d'); ?></td>

  </tr>

</table>

<div class="srvChapter">Problem list </div>

<div style="border: solid 2px black; background-color:#FFFFFF;">

  <table width="100%"  border="0" cellspacing="0" cellpadding="2">

    <tr>

      <td width="50%" class="ficaption3" id="bordR">High risk:</td>

      <td width="50%" class="ficaption3">Family history:</td>

    </tr>

    <tr>

      <td class="fibody5" id="bordR"><?php echo $repdata{'pl_high_risk'} ?>&nbsp;</td>

      <td class="fibody5"><?php echo $repdata{'pl_family_history'} ?>&nbsp;</td>

    </tr>

    <tr>

      <td class="ficaption3" id="bordR">Drug/Latex/Transfusion/Allergic reactions: </td>

      <td class="ficaption3">Current medications:</td>

    </tr>

    <tr>

      <td class="fibody5" id="bordR"><?php echo $repdata{'pl_reactions'} ?>&nbsp;</td>

      <td class="fibody5"><?php echo $repdata{'pl_medications'} ?>&nbsp;</td>

    </tr>

  </table>

</div>

<p>&nbsp;</p>

<div style="border: solid 2px black; background-color:#FFFFFF;">

  <table width="100%"  border="0" cellspacing="0" cellpadding="2">

    <tr>

      <td width="20" align="left" valign="bottom" class="ficaption2" id="bordR">No</td>

      <td width="120" align="center" valign="bottom" class="ficaption2" id="bordR">Entry date </td>

      <td align="center" valign="bottom" class="ficaption2" id="bordR">Problem/Resolution</td>

      <td width="120" align="center" valign="bottom" class="ficaption2" id="bordR">Onset age and date </td>

      <td width="120" align="center" valign="bottom" class="ficaption2">Resolution date </td>

    </tr>

<?php

$pli = 1;



while ($pli < 26){

list($pl_ed, $pl_problem, $pl_onset, $pl_rd) = explode('|~', $repdata["pl_problem_${pli}"]);

print <<<EOL

    <tr>

      <td align="left" valign="bottom" class="fibody5" id="bordR">${pli}.</td>

      <td align="left" valign="bottom" class="fibody5" id="bordR">${pl_ed}&nbsp;</td>

      <td align="left" valign="bottom" class="fibody5" id="bordR">${pl_problem}&nbsp;</td>

      <td align="left" valign="bottom" class="fibody5" id="bordR">${pl_onset}&nbsp;</td>

      <td align="left" valign="bottom" class="fibody5">${pl_rd}&nbsp;</td>

    </tr>

EOL;

$pli++;

}

?>	

  </table>

</div>

<p>&nbsp;</p>

</body>

</html>

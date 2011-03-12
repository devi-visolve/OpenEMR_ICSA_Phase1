<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.


// WHEN CONVERT THIS TO NEW SECURITY MODEL, NEED TO REMOVE FOLLOWING
//   AT APPROXIMATELY LINE 377:
//     $_REQUEST = stripslashes_deep($_REQUEST);
// http://www.openmedsoftware.org/wiki/Active_Projects#PLAN


require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("../../custom/code_types.inc.php");
require_once("$srcdir/patient.inc");
include_once("$srcdir/../interface/reports/report.inc.php");//Criteria Section common php page
require_once("$srcdir/billrep.inc");
require_once(dirname(__FILE__) . "/../../library/classes/OFX.class.php");
require_once(dirname(__FILE__) . "/../../library/classes/X12Partner.class.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/options.inc.php");
require_once("adjustment_reason_codes.php");

$EXPORT_INC = "$webserver_root/custom/BillingExport.php";

$alertmsg = '';

if ($_POST['mode'] == 'export') {
  $sql = ReturnOFXSql();
  $db = get_db();
  $results = $db->Execute($sql);
  $billings = array();
  if ($results->RecordCount() == 0) {
    echo xl("No Bills Found to Include in OFX Export<br>");
  }
  else {
    while(!$results->EOF) {
      $billings[] = $results->fields;
      $results->MoveNext();
    }
    $ofx = new OFX($billings);
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Disposition: attachment; filename=openemr_ofx.ofx");
    header("Content-Type: text/xml");
    echo $ofx->get_OFX();
    exit;
  }

}

// This is obsolete.
if ($_POST['mode'] == 'process') {
  if (exec("ps x | grep 'process_bills[.]php'")) {
    $alertmsg = xl('Request ignored - claims processing is already running!');
  }
  else {
    exec("cd $webserver_root/library/freeb;" .
      "php -q process_bills.php bill > process_bills.log 2>&1 &");
    $alertmsg = xl('Batch processing initiated; this may take a while.');
  }
}

//global variables:
if (!isset($_POST["mode"])) {
  $from_date = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d');
  $to_date   = isset($_POST['to_date'  ]) ? $_POST['to_date'  ] : '';
  $code_type = isset($_POST['code_type']) ? $_POST['code_type'] : 'all';
  $unbilled  = isset($_POST['unbilled' ]) ? $_POST['unbilled' ] : 'on';
  $my_authorized = $_POST["authorized"];
} else {
  $from_date     = $_POST["from_date"];
  $to_date       = $_POST["to_date"];
  $code_type     = $_POST["code_type"];
  $unbilled      = $_POST["unbilled"];
  $my_authorized = $_POST["authorized"];
}

// This tells us if only encounters that appear to be missing a "25" modifier
// are to be reported.
$missing_mods_only = !empty($_POST['missing_mods_only']);

/*
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : date('Y-m-d');
$to_date   = empty($_POST['to_date'  ]) ? $from_date : $_POST['to_date'];
$code_type = isset($_POST['code_type']) ? $_POST['code_type'] : 'all';
$unbilled  = isset($_POST['unbilled' ]) ? $_POST['unbilled' ] : 'on';
$my_authorized = $_POST["authorized"];
*/

$left_margin = isset($_POST["left_margin"]) ? $_POST["left_margin"] : 24;
$top_margin  = isset($_POST["top_margin"] ) ? $_POST["top_margin" ] : 20;

$ofrom_date  = $from_date;
$oto_date    = $to_date;
$ocode_type  = $code_type;
$ounbilled   = $unbilled;
$oauthorized = $my_authorized;
?>

<html>
<head>
<?php if (function_exists(html_header_show)) html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
<style>
.subbtn { margin-top:3px; margin-bottom:3px; margin-left:2px; margin-right:2px }
</style>
<script>

function select_all() {
  for($i=0;$i < document.update_form.length;$i++) {
    $name = document.update_form[$i].name;
    if ($name.substring(0,7) == "claims[" && $name.substring($name.length -6) == "[bill]") {
      document.update_form[$i].checked = true;
    }
  }
  set_button_states();
}

function set_button_states() {
  var f = document.update_form;
  var count0 = 0; // selected and not billed or queued
  var count1 = 0; // selected and queued
  var count2 = 0; // selected and billed
  for($i = 0; $i < f.length; ++$i) {
    $name = f[$i].name;
    if ($name.substring(0, 7) == "claims[" && $name.substring($name.length -6) == "[bill]" && f[$i].checked == true) {
      if      (f[$i].value == '0') ++count0;
      else if (f[$i].value == '1' || f[$i].value == '5') ++count1;
      else ++count2;
    }
  }

  var can_generate = (count0 > 0 || count1 > 0 || count2 > 0);
  var can_mark     = (count1 > 0 || count0 > 0 || count2 > 0);
  var can_bill     = (count0 == 0 && count1 == 0 && count2 > 0);

<?php if (file_exists($EXPORT_INC)) { ?>
  f.bn_external.disabled        = !can_generate;
<?php } else { ?>
  // f.bn_hcfa_print.disabled      = !can_generate;
  // f.bn_hcfa.disabled            = !can_generate;
  // f.bn_ub92_print.disabled      = !can_generate;
  // f.bn_ub92.disabled            = !can_generate;
  f.bn_x12.disabled             = !can_generate;
<?php if ($GLOBALS['support_encounter_claims']) { ?>
  f.bn_x12_encounter.disabled   = !can_generate;
<?php } ?>
  f.bn_process_hcfa.disabled    = !can_generate;
  f.bn_hcfa_txt_file.disabled   = !can_generate;
  // f.bn_electronic_file.disabled = !can_bill;
  f.bn_reopen.disabled          = !can_bill;
<?php } ?>
  f.bn_mark.disabled            = !can_mark;
}

// Process a click to go to an encounter.
function toencounter(pid, pubpid, pname, enc, datestr, dobstr) {
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
 var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
 parent.left_nav.setPatient(pname,pid,pubpid,'',dobstr);
 parent.left_nav.setEncounter(datestr, enc, othername);
 parent.left_nav.setRadio(othername, 'enc');
 parent.frames[othername].location.href =
  '../patient_file/encounter/encounter_top.php?set_encounter='
  + enc + '&pid=' + pid;
<?php } else { ?>
 location.href = '../patient_file/encounter/patient_encounter.php?set_encounter='
  + enc + '&pid=' + pid;
<?php } ?>
}
// Process a click to go to an patient.
function topatient(pid, pubpid, pname, enc, datestr, dobstr) {
 top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
 var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
 parent.left_nav.setPatient(pname,pid,pubpid,'',dobstr);
 parent.frames[othername].location.href =
  '../patient_file/summary/demographics_full.php?pid=' + pid;
<?php } else { ?>
 location.href = '../patient_file/summary/demographics_full.php?pid=' + pid;
<?php } ?>
}
</script>
<script language="javascript" type="text/javascript">
EncounterDateArray=new Array;
CalendarCategoryArray=new Array;
EncounterIdArray=new Array;
function SubmitTheScreen()
 {//Action on Update List link
  if(!ProcessBeforeSubmitting())
   return false;
  top.restoreSession();
  document.the_form.mode.value='change';
  document.the_form.target='_self';
  document.the_form.action='billing_report.php';
  document.the_form.submit();
  return true;
 }
function SubmitTheScreenPrint()
 {//Action on View Printable Report link
  if(!ProcessBeforeSubmitting())
   return false;
  top.restoreSession();
  document.the_form.target='new';
  document.the_form.action='print_billing_report.php';
  document.the_form.submit();
  return true;
 }
function SubmitTheScreenExportOFX()
 {//Action on Export OFX link
  if(!ProcessBeforeSubmitting())
   return false;
  top.restoreSession();
  document.the_form.mode.value='export';
  document.the_form.target='_self';
  document.the_form.action='billing_report.php';
  document.the_form.submit();
  return true;
 }
function TestExpandCollapse()
 {//Checks whether the Expand All, Collapse All labels need to be placed.If any result set is there these will be placed.
    var set=-1;
    for(i=1;i<=document.getElementById("divnos").value;i++)
    {
        var ele = document.getElementById("divid_"+i);
        if(ele)
        {
        set=1;
        break;
        }
    }
    if(set==-1)
         {
         if(document.getElementById("ExpandAll"))
          {
             document.getElementById("ExpandAll").innerHTML='';
             document.getElementById("CollapseAll").innerHTML='';
          }
         }
 }
function expandcollapse(atr){
    if(atr == "expand") {//Called in the Expand All, Collapse All links(All items will be expanded or collapsed)
        for(i=1;i<=document.getElementById("divnos").value;i++){
            var mydivid="divid_"+i;var myspanid="spanid_"+i;
                var ele = document.getElementById(mydivid);    var text = document.getElementById(myspanid);
                if(ele)
                 {
                    ele.style.display = "inline";text.innerHTML = "<?php echo htmlspecialchars(xl('Collapse'), ENT_QUOTES); ?>";
                 }
        }
      }
    else {
        for(i=1;i<=document.getElementById("divnos").value;i++){
            var mydivid="divid_"+i;var myspanid="spanid_"+i;
                var ele = document.getElementById(mydivid);    var text = document.getElementById(myspanid);
                if(ele)
                 {
                    ele.style.display = "none";    text.innerHTML = "<?php echo htmlspecialchars(xl('Expand'), ENT_QUOTES); ?>";
                 }
        }
    }

}
function divtoggle(spanid, divid) {//Called in the Expand, Collapse links(This is for a single item)
    var ele = document.getElementById(divid);
    if(ele)
     {
        var text = document.getElementById(spanid);
        if(ele.style.display == "inline") {
            ele.style.display = "none";
            text.innerHTML = "<?php echo htmlspecialchars(xl('Expand'), ENT_QUOTES); ?>";
        }
        else {
            ele.style.display = "inline";
            text.innerHTML = "<?php echo htmlspecialchars(xl('Collapse'), ENT_QUOTES); ?>";
        }
     }
}
function MarkAsCleared(Type)
 {
  CheckBoxBillingCount=0;
  for (var CheckBoxBillingIndex =0; ; CheckBoxBillingIndex++)
   {
    CheckBoxBillingObject=document.getElementById('CheckBoxBilling'+CheckBoxBillingIndex);
    if(!CheckBoxBillingObject)
     break;
    if(CheckBoxBillingObject.checked)
     {
       ++CheckBoxBillingCount;
     }
   }
   if(Type==1)
    {
     Message='<?php echo htmlspecialchars( xl('After saving your batch, click [View Log] to check for errors.'), ENT_QUOTES); ?>';
    }
   if(Type==2)
    {
     Message='<?php echo htmlspecialchars( xl('After saving the PDF, click [View Log] to check for errors.'), ENT_QUOTES); ?>';
    }
   if(Type==3)
    {
     Message='<?php echo htmlspecialchars( xl('After saving the TEXT file(s), click [View Log] to check for errors.'), ENT_QUOTES); ?>';
    }
  if(confirm(Message + "\n\n\n<?php echo htmlspecialchars( xl('Total'), ENT_QUOTES); ?>" + ' ' + CheckBoxBillingCount + ' ' +  "<?php echo htmlspecialchars( xl('Selected'), ENT_QUOTES); ?>\n" + 
  "<?php echo htmlspecialchars( xl('Would You Like them to be Marked as Cleared.'), ENT_QUOTES); ?>"))
   {
    document.getElementById('HiddenMarkAsCleared').value='yes';
  }
  else
   {
    document.getElementById('HiddenMarkAsCleared').value='';
   }
 }
</script>
<?php include_once("$srcdir/../interface/reports/report.script.php"); ?><!-- Criteria Section common javascript page-->
<!-- ================================================== -->
<!-- =============Included for Insurance ajax criteria==== -->
<!-- ================================================== -->
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/ajax/payment_ajax_jav.inc.php"); ?>
<script type="text/javascript" src="../../library/js/common.js"></script>
<style>
#ajax_div_insurance {
    position: absolute;
    z-index:10;
    background-color: #FBFDD0;
    border: 1px solid #ccc;
    padding: 10px;
}
</style>
<script language="javascript" type="text/javascript">
document.onclick=TakeActionOnHide;
</script>
<!-- ================================================== -->
<!-- =============Included for Insurance ajax criteria==== -->
<!-- ================================================== -->
</head>
<body class="body_top" onLoad="TestExpandCollapse()">

<p style='margin-top:5px;margin-bottom:5px;margin-left:5px'>

<?php if ($GLOBALS['concurrent_layout']) { ?>
<font class='title'><?php xl('Billing Manager','e') ?></font>
<?php } else if ($userauthorized) { ?>
<a href="../main/main.php" target='Main' onclick='top.restoreSession()'><font class=title><?php xl('Billing Manager','e') ?></font><font class=more> <?php echo $tback; ?></font></a>
<?php } else { ?>
<a href="../main/onotes/office_comments.php" target='Main' onclick='top.restoreSession()'><font class=title><?php xl('Billing Manager','e') ?></font><font class=more><?php echo $tback; ?></font></a>
<?php } ?>

</p>

<form name='the_form' method='post' action='billing_report.php' onsubmit='return top.restoreSession()' style="display:inline">

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language='JavaScript'>
 var mypcc = '1';
</script>

<input type='hidden' name='mode' value='change'>
<!-- ============================================================================================================================================= -->
                                                        <!-- Criteria section Starts -->
<!-- ============================================================================================================================================= -->
<?php
//The following are the search criteria per page.All the following variable which ends with 'Master' need to be filled properly.
//Each item is seperated by a comma(,).
//$ThisPageSearchCriteriaDisplayMaster ==>It is the display on screen for the set of criteria.
//$ThisPageSearchCriteriaKeyMaster ==>Corresponding database fields in the same order.
//$ThisPageSearchCriteriaDataTypeMaster ==>Corresponding data type in the same order.
$_REQUEST = stripslashes_deep($_REQUEST);//To deal with magic quotes on.
$ThisPageSearchCriteriaDisplayRadioMaster=array();
$ThisPageSearchCriteriaRadioKeyMaster=array();
$ThisPageSearchCriteriaQueryDropDownMaster=array();
$ThisPageSearchCriteriaQueryDropDownMasterDefault=array();
$ThisPageSearchCriteriaQueryDropDownMasterDefaultKey=array();
$ThisPageSearchCriteriaIncludeMaster=array();

$ThisPageSearchCriteriaDisplayMaster="Date of Service,Date of Entry,Date of Billing,Claim Type,Patient Name,".
                                     "Patient Id,Insurance Company,Encounter,Whether Insured,Charge Coded,Billing Status,".
                                     "Authorization Status,Last Level Billed,X12 Partner";
$ThisPageSearchCriteriaKeyMaster="form_encounter.date,billing.date,claims.process_time,claims.target,patient_data.fname,".
                                 "form_encounter.pid,claims.payer_id,form_encounter.encounter,insurance_data.provider,billing.id,billing.billed,".
                                 "billing.authorized,form_encounter.last_level_billed,billing.x12_partner_id";
$ThisPageSearchCriteriaDataTypeMaster="datetime,datetime,datetime,radio,text_like,".
                                      "text,include,text,radio,radio,radio,".
                                      "radio_like,radio,query_drop_down";
//The below section is needed if there is any 'radio' or 'radio_like' type in the $ThisPageSearchCriteriaDataTypeMaster
//$ThisPageSearchCriteriaDisplayRadioMaster,$ThisPageSearchCriteriaRadioKeyMaster ==>For each radio data type this pair comes.
//The key value 'all' indicates that no action need to be taken based on this.For that the key must be 'all'.Display value can be any thing.
$ThisPageSearchCriteriaDisplayRadioMaster[1]="All,eClaims,Paper";//Display Value
$ThisPageSearchCriteriaRadioKeyMaster[1]="all,standard,hcfa";//Key
$ThisPageSearchCriteriaDisplayRadioMaster[2]="All,Insured,Non-Insured";//Display Value
$ThisPageSearchCriteriaRadioKeyMaster[2]="all,1,0";//Key
$ThisPageSearchCriteriaDisplayRadioMaster[3]="All,Coded,Not Coded";//Display Value
$ThisPageSearchCriteriaRadioKeyMaster[3]="all,not null,null";//Key
$ThisPageSearchCriteriaDisplayRadioMaster[4]="All,Unbilled,Billed,Denied";//Display Value
$ThisPageSearchCriteriaRadioKeyMaster[4]="all,0,1,7";//Key
$ThisPageSearchCriteriaDisplayRadioMaster[5]="All,Authorized,Unauthorized";
$ThisPageSearchCriteriaRadioKeyMaster[5]="%,1,0";
$ThisPageSearchCriteriaDisplayRadioMaster[6]="All,None,Ins 1,Ins 2 or Ins 3";
$ThisPageSearchCriteriaRadioKeyMaster[6]="all,0,1,2";
//The below section is needed if there is any 'query_drop_down' type in the $ThisPageSearchCriteriaDataTypeMaster
$ThisPageSearchCriteriaQueryDropDownMaster[1]="SELECT name,id FROM x12_partners;";
$ThisPageSearchCriteriaQueryDropDownMasterDefault[1]="All";//Only one item will be here
$ThisPageSearchCriteriaQueryDropDownMasterDefaultKey[1]="all";//Only one item will be here
//The below section is needed if there is any 'include' type in the $ThisPageSearchCriteriaDataTypeMaster
//Function name is added here.Corresponding include files need to be included in the respective pages as done in this page.
//It is labled(Included for Insurance ajax criteria)(Line:-279-299).
$ThisPageSearchCriteriaIncludeMaster[1]="InsuranceCompanyDisplay";//This is php function defined in the file 'report.inc.php'

if(!isset($_REQUEST['mode']))//default case
 {
  $_REQUEST['final_this_page_criteria'][0]="(form_encounter.date between '".date("Y-m-d 00:00:00")."' and '".date("Y-m-d 23:59:59")."')";
  $_REQUEST['final_this_page_criteria'][1]="billing.billed = '0'";
  
  $_REQUEST['final_this_page_criteria_text'][0]=htmlspecialchars(xl("Date of Service = Today"), ENT_QUOTES);
  $_REQUEST['final_this_page_criteria_text'][1]=htmlspecialchars(xl("Billing Status = Unbilled"), ENT_QUOTES);
  
  $_REQUEST['date_master_criteria_form_encounter_date']="today";
  $_REQUEST['master_from_date_form_encounter_date']=date("Y-m-d");
  $_REQUEST['master_to_date_form_encounter_date']=date("Y-m-d");
  
  $_REQUEST['radio_billing_billed']=0;
 
 }
?>
<table width='100%' border="0" cellspacing="0" cellpadding="0">
 <tr>
      <td width="25%">&nbsp;</td>
      <td width="50%">
            <?php include_once("$srcdir/../interface/reports/criteria.tab.php"); ?>      
      </td>
      <td width="25%">
<?php
// ============================================================================================================================================= -->
                                                        // Criteria section Ends -->
// ============================================================================================================================================= -->
?>
      
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="15%">&nbsp;</td>
            <td width="85%"><span class='text'><a onClick="javascript:return SubmitTheScreen();" href="#" class=link_submit>[<?php echo htmlspecialchars(xl('Update List'), ENT_QUOTES) ?>]</a>
   or
   <a onClick="javascript:return SubmitTheScreenExportOFX();" href="#"  class='link_submit'><?php echo htmlspecialchars(xl('[Export OFX]'), ENT_QUOTES) ?></a></span>               </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><a onClick="javascript:return SubmitTheScreenPrint();" href="#" 
    class='link_submit'  ><?php echo htmlspecialchars(xl('[View Printable Report]'), ENT_QUOTES) ?></a></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>
            <?php
              $acct_config = $GLOBALS['oer_config']['ws_accounting'];
              if($acct_config['enabled']) {
                if($acct_config['enabled'] !== 2) {
                  print '<span class=text><a href="javascript:void window.open(\'' . $acct_config['url_path'] . '\')">' . htmlspecialchars(xl("[SQL-Ledger]"), ENT_QUOTES) . '</a> &nbsp; </span>';
                }
                if (acl_check('acct', 'rep')) {
                  print '<span class=text><a href="javascript:void window.open(\'sl_receipts_report.php\')" onclick="top.restoreSession()">' . htmlspecialchars(xl('[Reports]'), ENT_QUOTES) . '</a> &nbsp; </span>';
                }
                if (acl_check('acct', 'eob')) {
                  print '<span class=text><a href="javascript:void window.open(\'sl_eob_search.php\')" onclick="top.restoreSession()">' . htmlspecialchars(xl('[EOBs]'), ENT_QUOTES) . '</a></span>';
                }
              }
            ?>
            </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td>
            <?php if (! file_exists($EXPORT_INC)) { ?>
               <!--
               <a href="javascript:top.restoreSession();document.the_form.mode.value='process';document.the_form.submit()" class="link_submit"
                title="Process all queued bills to create electronic data (and print if requested)"><?php echo htmlspecialchars(xl('[Start Batch Processing]'), ENT_QUOTES) ?></a>
               &nbsp;
               -->
               <a href='../../library/freeb/process_bills.php' target='_blank' class='link_submit'
                title='<?php htmlspecialchars(xl('See messages from the last set of generated claims'), ENT_QUOTES); ?>'><?php echo htmlspecialchars(xl('[View Log]'), ENT_QUOTES) ?></a>
            <?php } ?>
            </td>
          </tr>
          <tr>
            <td>&nbsp;</td>
            <td><a href="javascript:select_all()" class="link_submit"><?php  echo htmlspecialchars(xl('[Select All]','e'), ENT_QUOTES) ?></a></td>
          </tr>
      </table>

      
      </td>
 </tr>
</table>
<table width='100%' border="0" cellspacing="0" cellpadding="0" >
    <tr>
        <td>
            <hr color="#000000">
        </td>
    </tr>
</table>
</form>
<form name='update_form' method='post' action='billing_process.php' onsubmit='return top.restoreSession()' style="display:inline">
<center>
<span class='text' style="display:inline">
<?php if (file_exists($EXPORT_INC)) { ?>
<input type="submit" class="subbtn" name="bn_external" value="Export Billing" title="<?php xl('Export to external billing system','e') ?>">
<input type="submit" class="subbtn" name="bn_mark" value="Mark as Cleared" title="<?php xl('Mark as billed but skip billing','e') ?>">
<?php } else { ?>
<!--
<input type="submit" class="subbtn" name="bn_hcfa_print" value="Queue HCFA &amp; Print" title="<?php xl('Queue for HCFA batch processing and printing','e') ?>">
<input type="submit" class="subbtn" name="bn_hcfa" value="Queue HCFA" title="<?php xl('Queue for HCFA batch processing','e')?>">
<input type="submit" class="subbtn" name="bn_ub92_print" value="Queue UB92 &amp; Print" title="<?php xl('Queue for UB-92 batch processing and printing','e')?>">
<input type="submit" class="subbtn" name="bn_ub92" value="Queue UB92" title="<?php xl('Queue for UB-92 batch processing','e')?>">
-->
<input type="submit" class="subbtn" name="bn_x12" value="<?php xl('Generate X12','e')?>"
 title="<?php xl('Generate and download X12 batch','e')?>"
 onclick="MarkAsCleared(1)">
<?php if ($GLOBALS['support_encounter_claims']) { ?>
<input type="submit" class="subbtn" name="bn_x12_encounter" value="<?php xl('Generate X12 Encounter','e')?>"
 title="<?php xl('Generate and download X12 encounter claim batch','e')?>"
 onclick="MarkAsCleared(1)">
<?php } ?>
<input type="submit" class="subbtn" style="width:175px;" name="bn_process_hcfa" value="<?php xl('Generate CMS 1500 PDF','e')?>"
 title="<?php xl('Generate and download CMS 1500 paper claims','e')?>"
 onclick="MarkAsCleared(2)">
<input type="submit" class="subbtn" style="width:175px;" name="bn_hcfa_txt_file" value="<?php xl('Generate CMS 1500 TEXT','e')?>"
 title="<?php xl('Making batch text files for uploading to Clearing House and will mark as billed', 'e')?>"
 onclick="MarkAsCleared(3)">
<input type="submit" class="subbtn" name="bn_mark" value="<?php xl('Mark as Cleared','e')?>" title="<?php xl('Post to accounting and mark as billed','e')?>">
<input type="submit" class="subbtn" name="bn_reopen" value="<?php xl('Re-Open','e')?>" title="<?php xl('Mark as not billed','e')?>">
<!--
<input type="submit" class="subbtn" name="bn_electronic_file" value="Make Electronic Batch &amp; Clear" title="<?php xl('Download billing file, post to accounting and mark as billed','e')?>">
-->
&nbsp;&nbsp;&nbsp;
<?php xl('CMS 1500 Margins','e'); ?>:
&nbsp;<?php xl('Left','e'); ?>:
<input type='text' size='2' name='left_margin'
 value='<?php echo $left_margin; ?>'
 title=<?php xl('HCFA left margin in points','e','\'','\''); ?> />
&nbsp;<?php xl('Top','e'); ?>:
<input type='text' size='2' name='top_margin'
 value='<?php echo $top_margin; ?>'
 title=<?php xl('HCFA top margin in points','e','\'','\''); ?> />
</span>
<?php } ?>

</center>
<input type='hidden' name='HiddenMarkAsCleared'  id='HiddenMarkAsCleared' value="" />
<input type='hidden' name='mode' value="bill" />
<input type='hidden' name='authorized' value="<?php echo $my_authorized; ?>" />
<input type='hidden' name='unbilled' value="<?php echo $unbilled; ?>" />
<input type='hidden' name='code_type' value="%" />
<input type='hidden' name='to_date' value="<?php echo $to_date; ?>" />
<input type='hidden' name='from_date' value="<?php echo $from_date; ?>" />

<?php
if ($my_authorized == "on" ) {
  $my_authorized = "1";
} else {
  $my_authorized = "%";
}
if ($unbilled == "on") {
  $unbilled = "0";
} else {
  $unbilled = "%";
}
$list = getBillsListBetween("%");
?>

<input type='hidden' name='bill_list' value="<?php echo $list; ?>" />

<!-- new form for uploading -->

<?php
if (!isset($_POST["mode"])) {
  if (!isset($_POST["from_date"])) {
    $from_date = date("Y-m-d");
  } else {
    $from_date = $_POST["from_date"];
  }
  if (empty($_POST["to_date"])) {
    $to_date = '';
  } else {
    $to_date = $_POST["to_date"];
  }
  if (!isset($_POST["code_type"])) {
    $code_type="all";
  } else {
    $code_type = $_POST["code_type"];
  }
  if (!isset($_POST["unbilled"])) {
    $unbilled = "on";
  } else {
    $unbilled = $_POST["unbilled"];
  }
  if (!isset($_POST["authorized"])) {
    $my_authorized = "on";
  } else {
    $my_authorized = $_POST["authorized"];
  }
} else {
  $from_date = $_POST["from_date"];
  $to_date = $_POST["to_date"];
  $code_type = $_POST["code_type"];
  $unbilled = $_POST["unbilled"];
  $my_authorized = $_POST["authorized"];
}

if ($my_authorized == "on" ) {
  $my_authorized = "1";
} else {
  $my_authorized = "%";
}

if ($unbilled == "on") {
  $unbilled = "0";
} else {
  $unbilled = "%";
}

if (isset($_POST["mode"]) && $_POST["mode"] == "bill") {
  billCodesList($list);
}
?>

<table border="0" cellspacing="0" cellpadding="0" width="100%">

<?php
if ($ret = getBillsBetween("%"))
{
if(is_array($ret))
 {
?>
<tr ><td colspan='8' align="right" ><table width="250" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100" id='ExpandAll'><a  onclick="expandcollapse('expand');" class='small'  href="JavaScript:void(0);"><?php echo '('.htmlspecialchars( xl('Expand All'), ENT_QUOTES).')' ?></a></td>
    <td width="100" id='CollapseAll'><a  onclick="expandcollapse('collapse');" class='small'  href="JavaScript:void(0);"><?php echo '('.htmlspecialchars( xl('Collapse All'), ENT_QUOTES).')' ?></a></td>
    <td width="50">&nbsp;</td>
  </tr>
</table>
</td></tr>
<?php
}
  $loop = 0;
  $oldcode = "";
  $last_encounter_id = "";
  $lhtml = "";
  $rhtml = "";
  $lcount = 0;
  $rcount = 0;
  $bgcolor = "";
  $skipping = FALSE;

  $mmo_empty_mod = false;
  $mmo_num_charges = 0;
  $divnos=0;
  
  foreach ($ret as $iter) {

    // We include encounters here that have never been billed.  However
    // if it had no selected billing items but does have non-selected
    // billing items, then it is not of interest.
    if (!$iter['id']) {
      $res = sqlQuery("SELECT count(*) AS count FROM billing WHERE " .
        "encounter = '" . $iter['enc_encounter'] . "' AND " .
        "pid='" . $iter['enc_pid'] . "' AND " .
        "activity = 1");
      if ($res['count'] > 0) continue;
    }

    $this_encounter_id = $iter['enc_pid'] . "-" . $iter['enc_encounter'];

    if ($last_encounter_id != $this_encounter_id) {

      // This dumps all HTML for the previous encounter.
      //
      if ($lhtml) {
        while ($rcount < $lcount) {
          $rhtml .= "<tr bgcolor='$bgcolor'><td colspan='7'></td></tr>";
          ++$rcount;
        }
        // This test handles the case where we are only listing encounters
        // that appear to have a missing "25" modifier.
        if (!$missing_mods_only || ($mmo_empty_mod && $mmo_num_charges > 1)) {
          if($DivPut=='yes')
           {
             $lhtml.='</div>';
            $DivPut='no';
           }
          echo "<tr bgcolor='$bgcolor'>\n<td rowspan='$rcount' valign='top'>\n$lhtml</td>$rhtml\n";
          echo "<tr bgcolor='$bgcolor'><td colspan='8' height='5'></td></tr>\n\n";
          ++$encount;
        }
      }

      $lhtml = "";
      $rhtml = "";
      $mmo_empty_mod = false;
      $mmo_num_charges = 0;

      // If there are ANY unauthorized items in this encounter and this is
      // the normal case of viewing only authorized billing, then skip the
      // entire encounter.
      //
      $skipping = FALSE;
      if ($my_authorized == '1') {
        $res = sqlQuery("select count(*) as count from billing where " .
          "encounter = '" . $iter['enc_encounter'] . "' and " .
          "pid='" . $iter['enc_pid'] . "' and " .
          "activity = 1 and authorized = 0");
        if ($res['count'] > 0) {
          $skipping = TRUE;
          $last_encounter_id = $this_encounter_id;
          continue;
        }
      }

      $name = getPatientData($iter['enc_pid'], "fname, mname, lname, pubpid, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");

      # Check if patient has primary insurance and a subscriber exists for it.
      # If not we will highlight their name in red.
      # TBD: more checking here.
      #
      $res = sqlQuery("select count(*) as count from insurance_data where " .
        "pid = " . $iter['enc_pid'] . " and " .
        "type='primary' and " .
        "subscriber_lname is not null and " .
        "subscriber_lname != '' limit 1");
      $namecolor = ($res['count'] > 0) ? "black" : "#ff7777";

      $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
      echo "<tr bgcolor='$bgcolor'><td colspan='8' height='5'></td></tr>\n";
      $lcount = 1;
      $rcount = 0;
      $oldcode = "";

      $ptname = $name['fname'] . " " . $name['lname'];
      $raw_encounter_date = date("Y-m-d", strtotime($iter['enc_date']));
            
            //  Add Encounter Date to display with "To Encounter" button 2/17/09  JCH
      $lhtml .= "&nbsp;<span class=bold><font color='$namecolor'>$ptname" .
        "</font></span><span class=small>&nbsp;(" . $iter['enc_pid'] . "-" .
        $iter['enc_encounter'] . ")</span>";

         //Encounter details are stored to javacript as array.
        $result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
            " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = '".$iter['enc_pid']."' order by fe.date desc");
           if(sqlNumRows($result4)>0)
            ?>
            <script language='JavaScript'>
            Count=0;
            EncounterDateArray[<?php echo $iter['enc_pid']; ?>]=new Array;
            CalendarCategoryArray[<?php echo $iter['enc_pid']; ?>]=new Array;
            EncounterIdArray[<?php echo $iter['enc_pid']; ?>]=new Array;
            <?php
            while($rowresult4 = sqlFetchArray($result4))
             {
            ?>
                EncounterIdArray[<?php echo $iter['enc_pid']; ?>][Count]='<?php echo htmlspecialchars($rowresult4['encounter'], ENT_QUOTES); ?>';
                EncounterDateArray[<?php echo $iter['enc_pid']; ?>][Count]='<?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date']))), ENT_QUOTES); ?>';
                CalendarCategoryArray[<?php echo $iter['enc_pid']; ?>][Count]='<?php echo htmlspecialchars( xl_appt_category($rowresult4['pc_catname']), ENT_QUOTES); ?>';
                Count++;
         <?php
             }
         ?>
        </script>
        <?php        
                
            //  Not sure why the next section seems to do nothing except post "To Encounter" button 2/17/09  JCH
      $lhtml .= "&nbsp;&nbsp;&nbsp;<a class=\"link_submit\" " .
        "href=\"javascript:window.toencounter(" . $iter['enc_pid'] .
        ",'" . addslashes($name['pubpid']) .
        "','" . addslashes($ptname) . "'," . $iter['enc_encounter'] .
        ",'" . oeFormatShortDate($raw_encounter_date) . "',' " . 
        xl('DOB') . ": " . oeFormatShortDate($name['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAge($name['DOB_YMD']) . "');
                 top.window.parent.left_nav.setPatientEncounter(EncounterIdArray[" . $iter['enc_pid'] . "],EncounterDateArray[" . $iter['enc_pid'] . 
                 "], CalendarCategoryArray[" . $iter['enc_pid'] . "])\">[" .
        xl('To Enctr') . " " . oeFormatShortDate($raw_encounter_date) . "]</a>";
        
            //  Changed "To xxx" buttons to allow room for encounter date display 2/17/09  JCH
      $lhtml .= "&nbsp;&nbsp;&nbsp;<a class=\"link_submit\" " .
        "href=\"javascript:window.topatient(" . $iter['enc_pid'] .
        ",'" . addslashes($name['pubpid']) .
        "','" . addslashes($ptname) . "'," . $iter['enc_encounter'] .
        ",'" . oeFormatShortDate($raw_encounter_date) . "',' " . 
        xl('DOB') . ": " . oeFormatShortDate($name['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAge($name['DOB_YMD']) . "');
                 top.window.parent.left_nav.setPatientEncounter(EncounterIdArray[" . $iter['enc_pid'] . "],EncounterDateArray[" . $iter['enc_pid'] . 
                 "], CalendarCategoryArray[" . $iter['enc_pid'] . "])\">[" . xl('To Dems') . "]</a>";
        $divnos=$divnos+1;
      $lhtml .= "&nbsp;&nbsp;&nbsp;<a  onclick='divtoggle(\"spanid_$divnos\",\"divid_$divnos\");' class='small' id='aid_$divnos' href=\"JavaScript:void(0);".
        "\">(<span id=spanid_$divnos class=\"indicator\">" . htmlspecialchars( xl('Expand'), ENT_QUOTES) . "</span>)</a>";

      if ($iter['id']) {

        $lcount += 2;
        $lhtml .= "<br />\n";
        $lhtml .= "&nbsp;<span class=text>Bill: ";
        $lhtml .= "<select name='claims[" . $this_encounter_id . "][payer]' style='background-color:$bgcolor'>";

        $query = "SELECT id.provider AS id, id.type, id.date, " .
          "ic.x12_default_partner_id AS ic_x12id, ic.name AS provider " .
          "FROM insurance_data AS id, insurance_companies AS ic WHERE " .
          "ic.id = id.provider AND " .
          "id.pid = '" . mysql_escape_string($iter['enc_pid']) . "' AND " .
          "id.date <= '$raw_encounter_date' " .
          "ORDER BY id.type ASC, id.date DESC";

        $result = sqlStatement($query);
        $count = 0;
        $default_x12_partner = $iter['ic_x12id'];
        $prevtype = '';

        while ($row = mysql_fetch_array($result)) {
          if (strcmp($row['type'], $prevtype) == 0) continue;
          $prevtype = $row['type'];
          if (strlen($row['provider']) > 0) {
            // This preserves any existing insurance company selection, which is
            // important when EOB posting has re-queued for secondary billing.
            $lhtml .= "<option value=\"" . strtoupper(substr($row['type'],0,1)) . $row['id'] . "\"";
            if (($count == 0 && !$iter['payer_id']) || $row['id'] == $iter['payer_id']) {
              $lhtml .= " selected";
              if (!is_numeric($default_x12_partner)) $default_x12_partner = $row['ic_x12id'];
            }
            $lhtml .= ">" . $row['type'] . ": " . $row['provider'] . "</option>";
          }
          $count++;
        }

        $lhtml .= "<option value='-1'>Unassigned</option>\n";
        $lhtml .= "</select>&nbsp;&nbsp;\n";
        $lhtml .= "<select name='claims[" . $this_encounter_id . "][partner]' style='background-color:$bgcolor'>";
        $x = new X12Partner();
        $partners = $x->_utility_array($x->x12_partner_factory());
        foreach ($partners as $xid => $xname) {
          $lhtml .= '<option label="' . $xname . '" value="' . $xid .'"';
          if ($xid == $default_x12_partner) {
            $lhtml .= "selected";
          }
          $lhtml .= '>' . $xname . '</option>';
        }
        $lhtml .= "</select>";
        $DivPut='yes';
        $lhtml .= "<br>\n&nbsp;<div   id='divid_$divnos' style='display:none'>" . oeFormatShortDate(substr($iter['date'], 0, 10))
          . substr($iter['date'], 10, 6) . " " . xl("Encounter was coded");

        $query = "SELECT * FROM claims WHERE " .
          "patient_id = '" . $iter['enc_pid'] . "' AND " .
          "encounter_id = '" . $iter['enc_encounter'] . "' " .
          "ORDER BY version";
        $cres = sqlStatement($query);

        $lastcrow = false;

        while ($crow = sqlFetchArray($cres)) {
          $query = "SELECT id.type, ic.name " .
            "FROM insurance_data AS id, insurance_companies AS ic WHERE " .
            "id.pid = '" . $iter['enc_pid'] . "' AND " .
            "id.provider = '" . $crow['payer_id'] . "' AND " .
            "id.date <= '$raw_encounter_date' AND " .
            "ic.id = id.provider " .
            "ORDER BY id.type ASC, id.date DESC";

          $irow= sqlQuery($query);

          if ($crow['bill_process']) {
            $lhtml .= "<br>\n&nbsp;" .
              oeFormatShortDate(substr($crow['bill_time'], 0, 10)) .
              substr($crow['bill_time'], 10, 6) . " " .
              xl("Queued for") . " {$irow['type']} {$crow['target']} " .
              xl("billing to ") . $irow['name'];
            ++$lcount;
          }
          else if ($crow['status'] < 6) {
              if ($crow['status'] > 1) {
                $lhtml .= "<br>\n&nbsp;" .
                  oeFormatShortDate(substr($crow['bill_time'], 0, 10)) .
                  substr($crow['bill_time'], 10, 6) . " " .
                  htmlspecialchars( xl("Marked as cleared"), ENT_QUOTES);
                ++$lcount;
              }
              else {
                $lhtml .= "<br>\n&nbsp;" .
                  oeFormatShortDate(substr($crow['bill_time'], 0, 10)) .
                  substr($crow['bill_time'], 10, 6) . " " .
                  htmlspecialchars( xl("Re-opened"), ENT_QUOTES);
                ++$lcount;
              }
          }
          else if ($crow['status'] == 6) {
            $lhtml .= "<br>\n&nbsp;" .
              oeFormatShortDate(substr($crow['bill_time'], 0, 10)) .
              substr($crow['bill_time'], 10, 6) . " " .
              htmlspecialchars( xl("This claim has been forwarded to next level."), ENT_QUOTES);
            ++$lcount;
          }
          else if ($crow['status'] == 7) {
            $lhtml .= "<br>\n&nbsp;" .
              oeFormatShortDate(substr($crow['bill_time'], 0, 10)) .
              substr($crow['bill_time'], 10, 6) . " " .
              htmlspecialchars( xl("This claim has been denied.Reason:-"), ENT_QUOTES);
              if($crow['process_file'])
               {
                $code_array=split(',',$crow['process_file']);
                foreach($code_array as $code_key => $code_value)
                 {
                    $lhtml .= "<br>\n&nbsp;&nbsp;&nbsp;";
                    $reason_array=split('_',$code_value);
                    if(!isset($adjustment_reasons[$reason_array[3]]))
                     {
                        $lhtml .=htmlspecialchars( xl("For code"), ENT_QUOTES).' ['.$reason_array[0].'] '.htmlspecialchars( xl("and modifier"), ENT_QUOTES).' ['.$reason_array[1].'] '.htmlspecialchars( xl("the Denial code is"), ENT_QUOTES).' ['.$reason_array[2].' '.$reason_array[3].']';
                     }
                    else
                     {
                        $lhtml .=htmlspecialchars( xl("For code"), ENT_QUOTES).' ['.$reason_array[0].'] '.htmlspecialchars( xl("and modifier"), ENT_QUOTES).' ['.$reason_array[1].'] '.htmlspecialchars( xl("the Denial Group code is"), ENT_QUOTES).' ['.$reason_array[2].'] '.htmlspecialchars( xl("and the Reason is"), ENT_QUOTES).':- '.$adjustment_reasons[$reason_array[3]];
                     }
                 }
               }
              else
               {
                $lhtml .=htmlspecialchars( xl("Not Specified."), ENT_QUOTES);
               }
            ++$lcount;
          }

          if ($crow['process_time']) {
            $lhtml .= "<br>\n&nbsp;" .
              oeFormatShortDate(substr($crow['process_time'], 0, 10)) .
              substr($crow['process_time'], 10, 6) . " " .
              xl("Claim was generated to file ") .
              "<a href='get_claim_file.php?key=" . $crow['process_file'] .
              "' onclick='top.restoreSession()'>" .
              $crow['process_file'] . "</a>";
            ++$lcount;
          }

          $lastcrow = $crow;
        } // end while ($crow = sqlFetchArray($cres))

        if ($lastcrow && $lastcrow['status'] == 4) {
          $lhtml .= "<br>\n&nbsp;This claim has been closed.";
          ++$lcount;
        }

        if ($lastcrow && $lastcrow['status'] == 5) {
          $lhtml .= "<br>\n&nbsp;This claim has been canceled.";
          ++$lcount;
        }
      } // end if ($iter['id'])

    } // end if ($last_encounter_id != $this_encounter_id)

    if ($skipping) continue;

    // Collect info related to the missing modifiers test.
    if ($iter['fee'] > 0) {
      ++$mmo_num_charges;
      $tmp = substr($iter['code'], 0, 3);
      if (($tmp == '992' || $tmp == '993') && empty($iter['modifier']))
        $mmo_empty_mod = true;
    }

    ++$rcount;

    if ($rhtml) {
        $rhtml .= "<tr bgcolor='$bgcolor'>\n";
    }
    $rhtml .= "<td width='50'>";
    if ($iter['id'] && $oldcode != $iter['code_type']) {
        $rhtml .= "<span class=text>" . $iter['code_type'] . ": </span>";
    }

    $oldcode = $iter['code_type'];
    $rhtml .= "</td>\n";
    $justify = "";

    if ($iter['id'] && $code_types[$iter['code_type']]['just']) {
      $js = split(":",$iter['justify']);
      $counter = 0;
      foreach ($js as $j) {
        if(!empty($j)) {
          if ($counter == 0) {
            $justify .= " (<b>$j</b>)";
          }
          else {
            $justify .= " ($j)";
          }
          $counter++;
        }
      }
    }

    $rhtml .= "<td><span class='text'>" .
      ($iter['code_type'] == 'COPAY' ? oeFormatMoney($iter['code']) : $iter['code']);
    if ($iter['modifier']) $rhtml .= ":" . $iter['modifier'];
    $rhtml .= "</span><span style='font-size:8pt;'>$justify</span></td>\n";

    $rhtml .= '<td align="right"><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
    if ($iter['id'] && $iter['fee'] > 0) {
      $rhtml .= oeFormatMoney($iter['fee']);
    }
    $rhtml .= "</span></td>\n";
    $rhtml .= '<td><span style="font-size:8pt;">&nbsp;&nbsp;&nbsp;';
    if ($iter['id']) $rhtml .= getProviderName(empty($iter['provider_id']) ? $iter['enc_provider_id'] : $iter['provider_id']);
    $rhtml .= "</span></td>\n";
    $rhtml .= '<td width=100>&nbsp;&nbsp;&nbsp;<span style="font-size:8pt;">';
    if ($iter['id']) $rhtml .= oeFormatSDFT(strtotime($iter{"date"}));
    $rhtml .= "</span></td>\n";
    if ($iter['id'] && $iter['authorized'] != 1) {
      $rhtml .= "<td><span class=alert>".xl("Note: This code was not entered by an authorized user. Only authorized codes may be uploaded to the Open Medical Billing Network for processing. If you wish to upload these codes, please select an authorized user here.")."</span></td>\n";
    }
    else {
      $rhtml .= "<td></td>\n";
    }
    if ($iter['id'] && $last_encounter_id != $this_encounter_id) {
      $tmpbpr = $iter['bill_process'];
      if ($tmpbpr == '0' && $iter['billed']) $tmpbpr = '2';
      $rhtml .= "<td><input type='checkbox' value='$tmpbpr' name='claims[" . $this_encounter_id . "][bill]' onclick='set_button_states()' id='CheckBoxBilling" . $CheckBoxBilling*1 . "'>&nbsp;</td>\n";
      $CheckBoxBilling++;
    }
    else {
      $rhtml .= "<td></td>\n";
    }
    $rhtml .= "</tr>\n";
    $last_encounter_id = $this_encounter_id;
    
  } // end foreach

  if ($lhtml) {
    while ($rcount < $lcount) {
      $rhtml .= "<tr bgcolor='$bgcolor'><td colspan='7'></td></tr>";
      ++$rcount;
    }
    if (!$missing_mods_only || ($mmo_empty_mod && $mmo_num_charges > 1)) {
      if($DivPut=='yes')
       {
        $lhtml.='</div>';
        $DivPut='no';
       }
      echo "<tr bgcolor='$bgcolor'>\n<td rowspan='$rcount' valign='top'>\n$lhtml</td>$rhtml\n";
      echo "<tr bgcolor='$bgcolor'><td colspan='8' height='5'></td></tr>\n";
    }
  }

}

?>

</table>
</form>

<script>
set_button_states();
<?php
if ($alertmsg) {
  echo "alert('$alertmsg');\n";
}
?>
</script>
<input type="hidden" name="divnos"  id="divnos" value="<?php echo $divnos ?>"/>
<input type='hidden' name='ajax_mode' id='ajax_mode' value='' />
</body>
</html>

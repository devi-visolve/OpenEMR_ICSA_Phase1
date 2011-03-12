<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/sql-ledger.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/formdata.inc.php");

 $accounting_enabled = $GLOBALS['oer_config']['ws_accounting']['enabled'];
 $INTEGRATED_AR = $accounting_enabled === 2;

 //maximum number of encounter entries to display on this page:
 $N = 12;

 // Get relevant ACL info.
 $auth_notes_a  = acl_check('encounters', 'notes_a');
 $auth_notes    = acl_check('encounters', 'notes');
 $auth_coding_a = acl_check('encounters', 'coding_a');
 $auth_coding   = acl_check('encounters', 'coding');
 $auth_relaxed  = acl_check('encounters', 'relaxed');
 $auth_med      = acl_check('patients'  , 'med');
 $auth_demo     = acl_check('patients'  , 'demo');

 $tmp = getPatientData($pid, "squad");
 if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  $auth_notes_a = $auth_notes = $auth_coding_a = $auth_coding = $auth_med = $auth_demo = $auth_relaxed = 0;

 if (!($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
  echo "<body>\n<html>\n";
  echo "<p>(".htmlspecialchars( xl('Encounters not authorized'), ENT_NOQUOTES).")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

// Perhaps the view choice should be saved as a session variable.
//
$tmp = sqlQuery("select authorized from users " .
  "where id = ?", array($_SESSION['authUserID']) );
$billing_view = ($tmp['authorized'] || $GLOBALS['athletic_team']) ? 0 : 1;
if (isset($_GET['billing']))
  $billing_view = empty($_GET['billing']) ? 0 : 1;

// This is called to generate a line of output for a patient document.
//
function showDocument(&$drow) {
  global $ISSUE_TYPES, $auth_med;

  $docdate = $drow['docdate'];

  echo "<tr class='text docrow' id='".htmlspecialchars( $drow['id'], ENT_QUOTES)."' title='". htmlspecialchars( xl('View document'), ENT_QUOTES) . "'>\n";

  // show date
  echo "<td>" . htmlspecialchars( oeFormatShortDate($docdate), ENT_NOQUOTES) . "</td>\n";

  // show associated issue, if any
  echo "<td>";
  if ($auth_med) {
    $irow = sqlQuery("SELECT type, title, begdate " .
      "FROM lists WHERE " .
      "id = ? " .
      "LIMIT 1", array($drow['list_id']) );
    if ($irow) {
      $tcode = $irow['type'];
      if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];
      echo htmlspecialchars("$tcode: " . $irow['title'], ENT_NOQUOTES);
    }
  } else {
    echo "(" . htmlspecialchars( xl('No access'), ENT_NOQUOTES) . ")";
  }
  echo "</td>\n";

  // show document name and category
  echo "<td colspan='3'>".
    htmlspecialchars( xl('Document') . ": " . basename($drow['url']) . ' (' . xl_document_category($drow['name']) . ')', ENT_NOQUOTES) .
    "</td>\n";

  // skip billing and insurance columns
  if (!$GLOBALS['athletic_team']) {
    echo "<td colspan=5>&nbsp;</td>\n";
  }

  echo "</tr>\n";
}
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<script language="JavaScript">

//function toencounter(enc, datestr) {
function toencounter(rawdata) {
    var parts = rawdata.split("~");
    var enc = parts[0];
    var datestr = parts[1];

    top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
    parent.left_nav.setEncounter(datestr, enc, window.name);
    parent.left_nav.setRadio(window.name, 'enc');
    parent.left_nav.loadFrame('enc2', window.name, 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);
<?php } else { ?>
    top.Title.location.href = '../encounter/encounter_title.php?set_encounter='   + enc;
    top.Main.location.href  = '../encounter/patient_encounter.php?set_encounter=' + enc;
<?php } ?>
}

function todocument(docid) {
  h = '<?php echo $GLOBALS['webroot'] ?>/controller.php?document&view&patient_id=<?php echo $pid ?>&doc_id=' + docid;
  top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
  parent.left_nav.setRadio(window.name, 'doc');
  location.href = h;
<?php } else { ?>
  top.Main.location.href = h;
<?php } ?>
}

 // Helper function to set the contents of a div.
function setDivContent(id, content) {
    $("#"+id).html(content);
}

 // Called when clicking on a billing note.
function editNote(feid) {
  top.restoreSession(); // this is probably not needed
  var c = "<iframe src='edit_billnote.php?feid=" + feid +
    "' style='width:100%;height:88pt;'></iframe>";
  setDivContent('note_' + feid, c);
}

 // Called when the billing note editor closes.
 function closeNote(feid, fenote) {
    var c = "<div id='"+ feid +"' title='<?php echo htmlspecialchars( xl('Click to edit'), ENT_QUOTES); ?>' class='text billing_note_text'>" +
            fenote + "</div>";
    setDivContent('note_' + feid, c);
 }

</script>

</head>

<body class="body_bottom">
<div id="patient_pastenc"> <!-- large outer DIV -->

<?php if ($GLOBALS['concurrent_layout']) { ?>
<!-- <a href='encounters_full.php'> -->
<?php } else { ?>
<!-- <a href='encounters_full.php' target='Main'> -->
<?php } ?>
<font class='title'><?php echo htmlspecialchars( xl('Past Encounters and Documents'), ENT_NOQUOTES); ?></font>
&nbsp;&nbsp;

<?php if ($billing_view) { ?>
<a href='encounters.php?billing=0' onclick='top.restoreSession()' style='font-size:8pt'>(<?php echo htmlspecialchars( xl('To Clinical View'), ENT_NOQUOTES); ?>)</a>
<?php } else { ?>
<a href='encounters.php?billing=1' onclick='top.restoreSession()' style='font-size:8pt'>(<?php echo htmlspecialchars( xl('To Billing View'), ENT_NOQUOTES); ?>)</a>
<?php } ?>

<br>

<table>
 <tr class='text'>
  <th><?php echo htmlspecialchars( xl('Date'), ENT_NOQUOTES); ?></th>

<?php if ($billing_view) { ?>
  <th class='billing_note'><?php echo htmlspecialchars( xl('Billing Note'), ENT_NOQUOTES); ?></th>
<?php } else { ?>
  <th><?php echo htmlspecialchars( xl('Issue'), ENT_NOQUOTES);       ?></th>
  <th><?php echo htmlspecialchars( xl('Reason/Form'), ENT_NOQUOTES); ?></th>
  <th><?php echo htmlspecialchars( xl('Provider'), ENT_NOQUOTES);    ?></th>
<?php } ?>

<?php if ($billing_view && $accounting_enabled) { ?>
  <th><?php echo xl('Code','e'); ?></th>
  <th class='right'><?php echo htmlspecialchars( xl('Chg'), ENT_NOQUOTES); ?></th>
  <th class='right'><?php echo htmlspecialchars( xl('Paid'), ENT_NOQUOTES); ?></th>
  <th class='right'><?php echo htmlspecialchars( xl('Adj'), ENT_NOQUOTES); ?></th>
  <th class='right'><?php echo htmlspecialchars( xl('Bal'), ENT_NOQUOTES); ?></th>
<?php } else { ?>
  <th colspan='5'><?php echo htmlspecialchars( (($GLOBALS['phone_country_code'] == '1') ? xl('Billing') : xl('Coding')), ENT_NOQUOTES); ?></th>
<?php } ?>

<?php if (!$GLOBALS['athletic_team'] && !$GLOBALS['ippf_specific']) { ?>
  <th>&nbsp;<?php echo htmlspecialchars( (($GLOBALS['weight_loss_clinic']) ? xl('Payment') : xl('Insurance')), ENT_NOQUOTES); ?></th>
<?php } ?>

 </tr>

<?php
$drow = false;
if (! $billing_view) {
    // Query the documents for this patient.
    $dres = sqlStatement("SELECT d.id, d.type, d.url, d.docdate, d.list_id, c.name " .
                    "FROM documents AS d, categories_to_documents AS cd, categories AS c WHERE " .
                    "d.foreign_id = ? AND cd.document_id = d.id AND c.id = cd.category_id " .
                    "ORDER BY d.docdate DESC, d.id DESC", array($pid) );
    $drow = sqlFetchArray($dres);
}

$count = 0;
if ($result = getEncounters($pid)) {

    if ($billing_view && $accounting_enabled && !$INTEGRATED_AR) SLConnect();

    foreach ($result as $iter ) {
        /*************************************************************
        // $count++; // Forget about limiting the number of encounters
        if ($count > $N) {
            //we have more encounters to print, but we've reached our display maximum
            print "<tr><td colspan='4' align='center'>" .
                "<a target='Main' href='encounters_full.php' class='alert' onclick='top.restoreSession()'>" .
                xl('Some encounters were not displayed. Click here to view all.') .
                "</a></td></tr>\n";
            break;
        }
        *************************************************************/

        // $href = "javascript:window.toencounter(" . $iter['encounter'] . ")";
        $reason_string = "";
        $auth_sensitivity = true;

        $raw_encounter_date = '';

        if ($result4 = sqlQuery("SELECT fe.*, u.fname, u.mname, u.lname " .
          "FROM form_encounter AS fe " .
          "LEFT JOIN users AS u ON u.id = fe.provider_id " .
          "WHERE fe.encounter = ? " .
          "AND fe.pid = ?", array($iter["encounter"],$pid) ))
        {
            $raw_encounter_date = date("Y-m-d", strtotime($result4{"date"}));
            $encounter_date = date("D F jS", strtotime($result4{"date"}));

            // if ($auth_notes_a || ($auth_notes && $iter['user'] == $_SESSION['authUser']))
            $reason_string .= htmlspecialchars( $result4{"reason"}, ENT_NOQUOTES) . "<br>\n";
            // else
            //   $reason_string = "(No access)";

            if ($result4['sensitivity']) {
                $auth_sensitivity = acl_check('sensitivities', $result4['sensitivity']);
                if (!$auth_sensitivity) {
                    $reason_string = "(".htmlspecialchars( xl("No access"), ENT_NOQUOTES).")";
                }
            }
        }

        $erow = sqlQuery("SELECT user FROM forms WHERE encounter = ? " .
        "AND formdir = 'newpatient' LIMIT 1", array($iter['encounter']) );

        // This generates document lines as appropriate for the date order.
        while ($drow && $raw_encounter_date && $drow['docdate'] > $raw_encounter_date) {
            showDocument($drow);
            $drow = sqlFetchArray($dres);
        }

        // Fetch all forms for this encounter, if the user is authorized to see
        // this encounter's notes and this is the clinical view.
        $encarr = array();
        $encounter_rows = 1;
        if (!$billing_view && $auth_sensitivity &&
            ($auth_notes_a || ($auth_notes && $iter['user'] == $_SESSION['authUser'])))
        {
            $encarr = getFormByEncounter($pid, $iter['encounter'], "formdir, user, form_name, form_id, deleted");
            $encounter_rows = count($encarr);
        }

        $rawdata = $iter['encounter'] . "~" . oeFormatShortDate($raw_encounter_date);
        echo "<tr class='encrow text' id='".htmlspecialchars( $rawdata, ENT_QUOTES) .
	  "' title='" . htmlspecialchars( xl('View encounter','','',' ') . "$pid.{$iter['encounter']}", ENT_QUOTES) . "'>\n";

        // show encounter date
        echo "<td valign='top'>" . htmlspecialchars( oeFormatShortDate($raw_encounter_date), ENT_NOQUOTES) . "</td>\n";

        if ($billing_view) {

            // Show billing note that you can click on to edit.
            $feid = $result4['id'] ? htmlspecialchars( $result4['id'], ENT_QUOTES) : 0; // form_encounter id
            echo "<td valign='top'>";
            echo "<div id='note_$feid'>";
            //echo "<div onclick='editNote($feid)' title='Click to edit' class='text billing_note_text'>";
            echo "<div id='$feid' title='". htmlspecialchars( xl('Click to edit'), ENT_QUOTES) . "' class='text billing_note_text'>";
            echo $result4['billing_note'] ? nl2br(htmlspecialchars( $result4['billing_note'], ENT_NOQUOTES)) : htmlspecialchars( xl('Add','','[',']'), ENT_NOQUOTES);
            echo "</div>";
            echo "</div>";
            echo "</td>\n";

        //  *************** end billing view *********************
        }
        else {

            // show issues for this encounter
            echo "<td>";
            if ($auth_med && $auth_sensitivity) {
                $ires = sqlStatement("SELECT lists.type, lists.title, lists.begdate " .
                                    "FROM issue_encounter, lists WHERE " .
                                    "issue_encounter.pid = ? AND " .
                                    "issue_encounter.encounter = ? AND " .
                                    "lists.id = issue_encounter.list_id " .
                                    "ORDER BY lists.type, lists.begdate", array($pid,$iter['encounter']) );
                for ($i = 0; $irow = sqlFetchArray($ires); ++$i) {
                    if ($i > 0) echo "<br>";
                    $tcode = $irow['type'];
                    if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];
                        echo htmlspecialchars( "$tcode: " . $irow['title'], ENT_NOQUOTES);
                }
            } 
            else {
                echo "(" . htmlspecialchars( xl('No access'), ENT_NOQUOTES) . ")";
            }
            echo "</td>\n";

            // show encounter reason/title
            echo "<td>".$reason_string;
            echo "<div style='padding-left:10px;'>";

            // Now show a line for each encounter form, if the user is authorized to
            // see this encounter's notes.

            foreach ($encarr as $enc) {
                if ($enc['formdir'] == 'newpatient') continue;
            
                // skip forms whose 'deleted' flag is set to 1 --JRM--
                if ($enc['deleted'] == 1) continue;
    
                // Skip forms that we are not authorized to see. --JRM--
                // pardon the wonky logic
                $formdir = $enc['formdir'];
                if (($auth_notes_a) ||
                    ($auth_notes && $enc['user'] == $_SESSION['authUser']) ||
                    ($auth_relaxed && ($formdir == 'sports_fitness' || $formdir == 'podiatry'))) ;
                else continue;
    
                // build the potentially HUGE tooltip used by ttshow
                $title = xl('View encounter');
                //
                // Normally skip the tooltip because of poor database performance.
                // However athletic teams want it.
                //
                if ($GLOBALS['athletic_team']) {
                  if ($enc['formdir'] != 'physical_exam' &&
                    $enc['formdir'] != 'procedure_order' &&
                    substr($enc['formdir'],0,3) != 'LBF')
                  {
                    $frow = sqlQuery("select * from form_" . add_escape_custom($enc['formdir']) .
                                    " where id = ?", array($enc['form_id']) );
                    foreach ($frow as $fkey => $fvalue) {
                        if (! preg_match('/[A-Za-z]/', $fvalue)) continue;
                        if ($title) $title .= "; ";
                        $title .= strtoupper($fkey) . ': ' . $fvalue;
                    }
                    $title = htmlspecialchars(strtr($title, "\t\n\r", "   "), ENT_QUOTES);
                  }
                } // end athletic team

                echo "<span class='form_tt' title=\"$title\">";
                echo htmlspecialchars( xl_form_title($enc['form_name']), ENT_NOQUOTES);
                echo "</span><br>";

            } // end encounter Forms loop
    
            echo "</div>";
            echo "</td>\n";

            // show user (Provider) for the encounter
            $provname = '&nbsp;';
            if (!empty($result4['lname']) || !empty($result4['fname'])) {
              $provname = htmlspecialchars( $result4['lname'], ENT_NOQUOTES);
              if (!empty($result4['fname']) || !empty($result4['mname']))
                $provname .= htmlspecialchars( ', ' . $result4['fname'] . ' ' . $result4['mname'], ENT_NOQUOTES);
            }
            echo "<td>$provname</td>\n";

        } // end not billing view

        //this is where we print out the text of the billing that occurred on this encounter
        $thisauth = $auth_coding_a;
        if (!$thisauth && $auth_coding) {
            if ($erow['user'] == $_SESSION['authUser'])
                $thisauth = $auth_coding;
        }
        $coded = "";
        $arid = 0;
        if ($thisauth && $auth_sensitivity) {
            $binfo = array('', '', '', '', '');
            if ($subresult2 = getBillingByEncounter($pid, $iter['encounter'], "code_type, code, modifier, code_text, fee"))
            {
                // Get A/R info, if available, for this encounter.
                $arinvoice = array();
                $arlinkbeg = "";
                $arlinkend = "";
                if ($billing_view && $accounting_enabled) {
                    if ($INTEGRATED_AR) {
                        $tmp = sqlQuery("SELECT id FROM form_encounter WHERE " .
                                    "pid = ? AND encounter = ?", array($pid,$iter['encounter']) );
                        $arid = 0 + $tmp['id'];
                        if ($arid) $arinvoice = ar_get_invoice_summary($pid, $iter['encounter'], true);
                    }
                    else {
                        $arid = SLQueryValue("SELECT id FROM ar WHERE invnumber = " .
                                        "'$pid.{$iter['encounter']}'");
                        if ($arid) $arinvoice = get_invoice_summary($arid, true);
                    }
                    if ($arid) {
                        $arlinkbeg = "<a href='../../billing/sl_eob_invoice.php?id=" .
			            htmlspecialchars( $arid, ENT_QUOTES)."'" .
                                    " target='_blank' class='text' style='color:#00cc00'>";
                        $arlinkend = "</a>";
                    }
                }

                // Throw in product sales.
                $query = "SELECT s.drug_id, s.fee, d.name " .
                  "FROM drug_sales AS s " .
                  "LEFT JOIN drugs AS d ON d.drug_id = s.drug_id " .
                  "WHERE s.pid = ? AND s.encounter = ? " .
                  "ORDER BY s.sale_id";
                $sres = sqlStatement($query, array($pid,$iter['encounter']) );
                while ($srow = sqlFetchArray($sres)) {
                  $subresult2[] = array('code_type' => 'PROD',
                    'code' => 'PROD:' . $srow['drug_id'], 'modifier' => '',
                    'code_text' => $srow['name'], 'fee' => $srow['fee']);
                }

                // This creates 5 columns of billing information:
                // billing code, charges, payments, adjustments, balance.
                foreach ($subresult2 as $iter2) {
                    // Next 2 lines were to skip diagnoses, but that seems unpopular.
                    // if ($iter2['code_type'] != 'COPAY' &&
                    //   !$code_types[$iter2['code_type']]['fee']) continue;
                    $title = htmlspecialchars( ($iter2['code_text']), ENT_QUOTES);
                    $codekey = $iter2['code'];
                    if ($iter2['code_type'] == 'COPAY') $codekey = 'CO-PAY';
                    if ($iter2['modifier']) $codekey .= ':' . $iter2['modifier'];
                    if ($binfo[0]) $binfo[0] .= '<br>';
                    // $binfo[0] .= "<span class='form_tt' title='".$title."'>".
                    $binfo[0] .= "<span title='$title'>".
                                //onmouseover='ttshow(this,\"$title\")' onmouseout='tthide()'>" .
                                $arlinkbeg . htmlspecialchars( ($codekey == 'CO-PAY' ? xl($codekey) : $codekey), ENT_NOQUOTES) .
                                $arlinkend . "</span>";
                    if ($billing_view && $accounting_enabled) {
                        if ($binfo[1]) {
                            for ($i = 1; $i < 5; ++$i) $binfo[$i] .= '<br>';
                        }
                        if (empty($arinvoice[$codekey])) {
                            // If no invoice, show the fee.
                            if ($arlinkbeg) $binfo[1] .= '&nbsp;';
                            else $binfo[1] .= htmlspecialchars( oeFormatMoney($iter2['fee']), ENT_NOQUOTES);

                            for ($i = 2; $i < 5; ++$i) $binfo[$i] .= '&nbsp;';
                        }
                        else {
                            $binfo[1] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['chg'] + $arinvoice[$codekey]['adj']), ENT_NOQUOTES);
                            $binfo[2] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['chg'] - $arinvoice[$codekey]['bal']), ENT_NOQUOTES);
                            $binfo[3] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['adj']), ENT_NOQUOTES);
                            $binfo[4] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['bal']), ENT_NOQUOTES);
                            unset($arinvoice[$codekey]);
                        }
                    }
                } // end foreach

                // Pick up any remaining unmatched invoice items from the accounting
                // system.  Display them in red, as they should be unusual.
                if ($accounting_enabled && !empty($arinvoice)) {
                    foreach ($arinvoice as $codekey => $val) {
                        if ($binfo[0]) {
                            for ($i = 0; $i < 5; ++$i) $binfo[$i] .= '<br>';
                        }
                        for ($i = 0; $i < 5; ++$i) $binfo[$i] .= "<font color='red'>";
                        $binfo[0] .= htmlspecialchars( $codekey, ENT_NOQUOTES);
                        $binfo[1] .= htmlspecialchars( oeFormatMoney($val['chg'] + $val['adj']), ENT_NOQUOTES);
                        $binfo[2] .= htmlspecialchars( oeFormatMoney($val['chg'] - $val['bal']), ENT_NOQUOTES);
                        $binfo[3] .= htmlspecialchars( oeFormatMoney($val['adj']), ENT_NOQUOTES);
                        $binfo[4] .= htmlspecialchars( oeFormatMoney($val['bal']), ENT_NOQUOTES);
                        for ($i = 0; $i < 5; ++$i) $binfo[$i] .= "</font>";
                    }
                }
            } // end if there is billing

            echo "<td class='text'>".$binfo[0]."</td>\n";
            for ($i = 1; $i < 5; ++$i) {
                echo "<td class='text right'>". $binfo[$i]."</td>\n";
            }
        } // end if authorized

        else {
            echo "<td class='text' valign='top' colspan='5' rowspan='$encounter_rows'>(".htmlspecialchars( xl("No access"), ENT_NOQUOTES).")</td>\n";
        }

        // show insurance
        if (!$GLOBALS['athletic_team'] && !$GLOBALS['ippf_specific']) {
            $insured = oeFormatShortDate($raw_encounter_date);
            if ($auth_demo) {
                $responsible = -1;
                if ($arid) {
                    if ($INTEGRATED_AR) {
                        $responsible = ar_responsible_party($pid, $iter['encounter']);
                    } else {
                        $responsible = responsible_party($arid);
                    }
                }
                $subresult5 = getInsuranceDataByDate($pid, $raw_encounter_date, "primary");
                if ($subresult5 && $subresult5{"provider_name"}) {
                    $style = $responsible == 1 ? " style='color:red'" : "";
                    $insured = "<span class='text'$style>&nbsp;" . htmlspecialchars( xl('Primary'), ENT_NOQUOTES) . ": " .
                    htmlspecialchars( $subresult5{"provider_name"}, ENT_NOQUOTES) . "</span><br>\n";
                }
                $subresult6 = getInsuranceDataByDate($pid, $raw_encounter_date, "secondary");
                if ($subresult6 && $subresult6{"provider_name"}) {
                    $style = $responsible == 2 ? " style='color:red'" : "";
                    $insured .= "<span class='text'$style>&nbsp;" . htmlspecialchars( xl('Secondary'), ENT_NOQUOTES) . ": " .
                    htmlspecialchars( $subresult6{"provider_name"}, ENT_NOQUOTES) . "</span><br>\n";
                }
                $subresult7 = getInsuranceDataByDate($pid, $raw_encounter_date, "tertiary");
                if ($subresult6 && $subresult7{"provider_name"}) {
                    $style = $responsible == 3 ? " style='color:red'" : "";
                    $insured .= "<span class='text'$style>&nbsp;" . htmlspecialchars( xl('Tertiary'), ENT_NOQUOTES) . ": " .
                    htmlspecialchars( $subresult7{"provider_name"}, ENT_NOQUOTES) . "</span><br>\n";
                }
                if ($responsible == 0) {
                    $insured .= "<span class='text' style='color:red'>&nbsp;" . htmlspecialchars( xl('Patient'), ENT_NOQUOTES) .
                                "</span><br>\n";
                }
            }
            else {
                $insured = " (".htmlspecialchars( xl("No access"), ENT_NOQUOTES).")";
            }
      
            echo "<td>".$insured."</td>\n";
        }

        echo "</tr>\n";


    } // end foreach $result

    if ($billing_view && $accounting_enabled && !$INTEGRATED_AR) SLClose();

} // end if

// Dump remaining document lines if count not exceeded.
while ($drow && $count <= $N) {
    showDocument($drow);
    $drow = sqlFetchArray($dres);
}
?>

</table>

</div> <!-- end 'pastenc' large outer DIV -->
</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".encrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".encrow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".encrow").click(function() { toencounter(this.id); }); 
    
    $(".docrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".docrow").mouseout(function() { $(this).toggleClass("highlight"); });
    $(".docrow").click(function() { todocument(this.id); }); 

    $(".billing_note_text").mouseover(function() { $(this).toggleClass("billing_note_text_highlight"); });
    $(".billing_note_text").mouseout(function() { $(this).toggleClass("billing_note_text_highlight"); });
    $(".billing_note_text").click(function(evt) { evt.stopPropagation(); editNote(this.id); });

    // set up the tooltip function
    //tooltip();
});

/* COMMENTED out July 2009 -- JRM
this.tooltip = function(){  
    // CONFIG
    xOffset = 10;
    yOffset = 20;       
    // these 2 variable determine popup's distance from the cursor
    // you might want to adjust to get the right result     
    // END CONFIG

    // display the floating tooltip paragraph
    $(".form_tt").hover(function(e){                                             
        this.t = this.title;
        this.title = "";                                      
        $("#patient_pastenc").append("<p class='tooltip text'>"+ this.t +"</p>");
        $(".tooltip")
            .css("top",(e.pageY - xOffset) + "px")
            .css("left",(e.pageX + yOffset) + "px")
            .fadeIn("fast");        
    },

    // destroy the tooltip paragraph
    function(){
        this.title = this.t;        
        $(".tooltip").remove();
    }); 

    // mouse moves on tooltip paragraph
    $(".form_tt").mousemove(function(e){
        $(".tooltip")
            .css("top",(e.pageY - xOffset) + "px")
            .css("left",(e.pageX + yOffset) + "px");
    }); 
};
*/

</script>

</html>

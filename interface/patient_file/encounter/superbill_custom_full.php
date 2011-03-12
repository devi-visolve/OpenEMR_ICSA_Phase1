<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");

// Translation for form fields.
function ffescape($field) {
  if (!get_magic_quotes_gpc()) $field = addslashes($field);
  return trim($field);
}

// Format dollars for display.
//
function bucks($amount) {
  if ($amount) {
    $amount = oeFormatMoney($amount);
    return $amount;
  }
  return '';
}

$alertmsg = '';
$pagesize = 100;
$mode = $_POST['mode'];
$code_id = 0;
$related_code = '';
$active = 1;
$reportable = 0;

if (isset($mode)) {
  $code_id    = $_POST['code_id'] + 0;
  $code       = $_POST['code'];
  $code_type  = $_POST['code_type'];
  $code_text  = $_POST['code_text'];
  $modifier   = $_POST['modifier'];
  $superbill  = $_POST['form_superbill'];
  $related_code = $_POST['related_code'];
  $cyp_factor = $_POST['cyp_factor'] + 0;
  $active     = empty($_POST['active']) ? 0 : 1;
  $reportable = empty($_POST['reportable']) ? 0 : 1;

  $taxrates = "";
  if (!empty($_POST['taxrate'])) {
    foreach ($_POST['taxrate'] as $key => $value) {
      $taxrates .= "$key:";
    }
  }

  if ($mode == "delete") {
    sqlStatement("DELETE FROM codes WHERE id = '$code_id'");
    $code_id = 0;
  }
  else if ($mode == "add") { // this covers both adding and modifying
    $crow = sqlQuery("SELECT COUNT(*) AS count FROM codes WHERE " .
      "code_type = '"    . ffescape($code_type)    . "' AND " .
      "code = '"         . ffescape($code)         . "' AND " .
      "modifier = '"     . ffescape($modifier)     . "' AND " .
      "id != '$code_id'");
    if ($crow['count']) {
      $alertmsg = xl('Cannot add/update this entry because a duplicate already exists!');
    }
    else {
      $sql =
        "code = '"         . ffescape($code)         . "', " .
        "code_type = '"    . ffescape($code_type)    . "', " .
        "code_text = '"    . ffescape($code_text)    . "', " .
        "modifier = '"     . ffescape($modifier)     . "', " .
        "superbill = '"    . ffescape($superbill)    . "', " .
        "related_code = '" . ffescape($related_code) . "', " .
        "cyp_factor = '"   . ffescape($cyp_factor)   . "', " .
        "taxrates = '"     . ffescape($taxrates)     . "', " .
        "active = $active" . ", " .
        "reportable = $reportable";
      if ($code_id) {
        $query = "UPDATE codes SET $sql WHERE id = '$code_id'";
        sqlStatement($query);
        sqlStatement("DELETE FROM prices WHERE pr_id = '$code_id' AND " .
          "pr_selector = ''");
      }
      else {
        $code_id = sqlInsert("INSERT INTO codes SET $sql");
      }
      if (!$alertmsg) {
        foreach ($_POST['fee'] as $key => $value) {
          $value = $value + 0;
          if ($value) {
            sqlStatement("INSERT INTO prices ( " .
              "pr_id, pr_selector, pr_level, pr_price ) VALUES ( " .
              "'$code_id', '', '$key', '$value' )");
          }
        }
        $code = $code_type = $code_text = $modifier = $superbill = "";
        $code_id = 0;
        $related_code = '';
        $cyp_factor = 0;
        $taxrates = '';
        $active = 1;
        $reportable = 0;
      }
    }
  }
  else if ($mode == "edit") { // someone clicked [Edit]
    $sql = "SELECT * FROM codes WHERE id = '$code_id'";
    $results = sqlQ($sql);
    while ($row = mysql_fetch_assoc($results)) {
      $code         = $row['code'];
      $code_text    = $row['code_text'];
      $code_type    = $row['code_type'];
      $modifier     = $row['modifier'];
      // $units        = $row['units'];
      $superbill    = $row['superbill'];
      $related_code = $row['related_code'];
      $cyp_factor   = $row['cyp_factor'];
      $taxrates     = $row['taxrates'];
      $active       = 0 + $row['active'];
      $reportable   = 0 + $row['reportable'];
    }
  }
}

$related_desc = '';
if (!empty($related_code)) {
  $related_desc = $related_code;
}

$fstart = $_REQUEST['fstart'] + 0;
$filter = $_REQUEST['filter'] + 0;
$search = $_REQUEST['search'];

$where = "1 = 1";
if ($filter) {
  $where .= " AND code_type = '$filter'";
}
if (!empty($search)) {
  $where .= " AND code LIKE '" . ffescape($search) . "%'";
}

$crow = sqlQuery("SELECT count(*) AS count FROM codes WHERE $where");
$count = $crow['count'];
if ($fstart >= $count) $fstart -= $pagesize;
if ($fstart < 0) $fstart = 0;
$fend = $fstart + $pagesize;
if ($fend > $count) $fend = $count;
?>

<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>

<script language="JavaScript">

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0];
 var s = f.related_code.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 f.related_code.value = s;
 f.related_desc.value = s;
}

// This invokes the find-code popup.
function sel_related() {
 var f = document.forms[0];
 var i = f.code_type.selectedIndex;
 var codetype = '';
 if (i >= 0) {
  var myid = f.code_type.options[i].value;
<?php
foreach ($code_types as $key => $value) {
  $codeid = $value['id'];
  $coderel = $value['rel'];
  if (!$coderel) continue;
  echo "  if (myid == $codeid) codetype = '$coderel';";
}
?>
 }
 if (!codetype) {
  alert('<?php xl('This code type does not accept relations.','e') ?>');
  return;
 }
 dlgopen('find_code_popup.php', '_blank', 500, 400);
}

// Some validation for saving a new code entry.
function validEntry(f) {
 if (!f.code.value) {
  alert('<?php xl('No code was specified!','e') ?>');
  return false;
 }
<?php if ($GLOBALS['ippf_specific']) { ?>
 if (f.code_type.value == 12 && !f.related_code.value) {
  alert('<?php echo xl('A related IPPF code is required!'); ?>');
  return false;
 }
<?php } ?>
 return true;
}

function submitAdd() {
 var f = document.forms[0];
 if (!validEntry(f)) return;
 f.mode.value = 'add';
 f.code_id.value = '';
 f.submit();
}

function submitUpdate() {
 var f = document.forms[0];
 if (! parseInt(f.code_id.value)) {
  alert('<?php xl('Cannot update because you are not editing an existing entry!','e') ?>');
  return;
 }
 if (!validEntry(f)) return;
 f.mode.value = 'add';
 f.submit();
}

function submitList(offset) {
 var f = document.forms[0];
 var i = parseInt(f.fstart.value) + offset;
 if (i < 0) i = 0;
 f.fstart.value = i;
 f.submit();
}

function submitEdit(id) {
 var f = document.forms[0];
 f.mode.value = 'edit';
 f.code_id.value = id;
 f.submit();
}

function submitDelete(id) {
 var f = document.forms[0];
 f.mode.value = 'delete';
 f.code_id.value = id;
 f.submit();
}

function getCTMask() {
 var ctid = document.forms[0].code_type.value;
<?php
foreach ($code_types as $key => $value) {
  $ctid   = $value['id'];
  $ctmask = addslashes($value['mask']);
  echo " if (ctid == '$ctid') return '$ctmask';\n";
}
?>
 return '';
}

function codeTypeChanged(code) {
 var f = document.forms[0];
 if (f.code_type.value == 2) 
   f.reportable.disabled = false;
 else {
   f.reportable.checked = false;
   f.reportable.disabled = true;
 }
}

</script>

</head>
<body class="body_top" onLoad="codeTypeChanged();" >

<?php if ($GLOBALS['concurrent_layout']) {
} else { ?>
<a href='patient_encounter.php?codefrom=superbill' target='Main'>
<span class='title'><?php xl('Superbill Codes','e'); ?></span>
<font class='more'><?php echo $tback;?></font></a>
<?php } ?>

<form method='post' action='superbill_custom_full.php' name='theform'>

<input type='hidden' name='mode' value=''>

<br>

<center>
<table border='0' cellpadding='0' cellspacing='0'>

 <tr>
  <td colspan="3"> <?php xl('Not all fields are required for all codes or code types.','e'); ?><br><br></td>
 </tr>

 <tr>
  <td><?php xl('Type','e'); ?>:</td>
  <td width="5"></td>
  <td>
   <select name="code_type" onChange="codeTypeChanged();">
<?php foreach ($code_types as $key => $value) { ?>
    <option value="<?php  echo $value['id'] ?>"<?php if ($GLOBALS['code_type'] == $value['id']) echo " selected" ?>><?php echo $key ?></option>
<?php } ?>
   </select>
   &nbsp;&nbsp;
   <?php xl('Code','e'); ?>:
   <input type='text' size='6' name='code' value='<?php echo $code ?>'
    onkeyup='maskkeyup(this,getCTMask())'
    onblur='maskblur(this,getCTMask())'
   />
<?php if (modifiers_are_used()) { ?>
   &nbsp;&nbsp;<?php xl('Modifier','e'); ?>:
   <input type='text' size='3' name='modifier' value='<?php echo $modifier ?>'>
<?php } else { ?>
   <input type='hidden' name='modifier' value='<?php // echo $modifier; ?>'>
<?php } ?>

   &nbsp;&nbsp;
   <input type='checkbox' name='active' value='1'<?php if (!empty($active)) echo ' checked'; ?> />
   <?php xl('Active','e'); ?>
  </td>
 </tr>

 <tr>
  <td><?php xl('Description','e'); ?>:</td>
  <td></td>
  <td>
   <input type='text' size='50' name="code_text" value='<?php echo $code_text ?>'>
  </td>
 </tr>

 <tr>
  <td><?php xl('Category','e'); ?>:</td>
  <td></td>
  <td>
<?php
generate_form_field(array('data_type'=>1,'field_id'=>'superbill','list_id'=>'superbill'), $superbill);
?>
   &nbsp;&nbsp;
   <input type='checkbox' name='reportable' value='1'<?php if (!empty($reportable)) echo ' checked'; ?> />
   <?php xl('Reportable','e'); ?>
  </td>
 </tr>

 <tr<?php if (empty($GLOBALS['ippf_specific'])) echo " style='display:none'"; ?>>
  <td><?php xl('CYP Factor','e'); ?>:</td>
  <td></td>
  <td>
   <input type='text' size='10' maxlength='20' name="cyp_factor" value='<?php echo $cyp_factor ?>'>
  </td>
 </tr>

 <tr<?php if (!related_codes_are_used()) echo " style='display:none'"; ?>>
  <td><?php xl('Relate To','e'); ?>:</td>
  <td></td>
  <td>
   <input type='text' size='50' name='related_desc'
    value='<?php echo $related_desc ?>' onclick="sel_related()"
    title='<?php xl('Click to select related code','e'); ?>' readonly />
   <input type='hidden' name='related_code' value='<?php echo $related_code ?>' />
  </td>
 </tr>

 <tr>
  <td><?php xl('Fees','e'); ?>:</td>
  <td></td>
  <td>
<?php
$pres = sqlStatement("SELECT lo.option_id, lo.title, p.pr_price " .
  "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
  "p.pr_id = '$code_id' AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
  "WHERE list_id = 'pricelevel' ORDER BY lo.seq");
for ($i = 0; $prow = sqlFetchArray($pres); ++$i) {
  if ($i) echo "&nbsp;&nbsp;";
  echo xl_list_label($prow['title']) . " ";
  echo "<input type='text' size='6' name='fee[" . $prow['option_id'] . "]' " .
    "value='" . $prow['pr_price'] . "' >\n";
}
?>
  </td>
 </tr>

<?php
$taxline = '';
$pres = sqlStatement("SELECT option_id, title FROM list_options " .
  "WHERE list_id = 'taxrate' ORDER BY seq");
while ($prow = sqlFetchArray($pres)) {
  if ($taxline) $taxline .= "&nbsp;&nbsp;";
  $taxline .= "<input type='checkbox' name='taxrate[" . $prow['option_id'] . "]' value='1'";
  if (strpos(":$taxrates", $prow['option_id']) !== false) $taxline .= " checked";
  $taxline .= " />\n";
  $taxline .= $prow['title'] . "\n";
}
if ($taxline) {
?>
 <tr>
  <td><?php xl('Taxes','e'); ?>:</td>
  <td></td>
  <td>
   <?php echo $taxline ?>
  </td>
 </tr>
<?php } ?>

 <tr>
  <td colspan="3" align="center">
   <input type="hidden" name="code_id" value="<?php echo $code_id ?>"><br>
   <a href='javascript:submitUpdate();' class='link'>[<?php xl('Update','e'); ?>]</a>
   &nbsp;&nbsp;
   <a href='javascript:submitAdd();' class='link'>[<?php xl('Add as New','e'); ?>]</a>
  </td>
 </tr>

</table>

<table border='0' cellpadding='5' cellspacing='0' width='96%'>
 <tr>

  <td class='text'>
   <select name='filter' onchange='submitList(0)'>
    <option value='0'>All</option>
<?php
foreach ($code_types as $key => $value) {
  echo "<option value='" . $value['id'] . "'";
  if ($value['id'] == $filter) echo " selected";
  echo ">$key</option>\n";
}
?>
   </select>
   &nbsp;&nbsp;&nbsp;&nbsp;

   <input type="text" name="search" size="5" value="<?php echo $search ?>">&nbsp;
   <input type="submit" name="go" value=<?php xl('Search','e','\'','\''); ?>>
   <input type='hidden' name='fstart' value='<?php echo $fstart ?>'>
  </td>

  <td class='text' align='right'>
<?php if ($fstart) { ?>
   <a href="javascript:submitList(-<?php echo $pagesize ?>)">
    &lt;&lt;
   </a>
   &nbsp;&nbsp;
<?php } ?>
   <?php echo ($fstart + 1) . " - $fend of $count" ?>
   &nbsp;&nbsp;
   <a href="javascript:submitList(<?php echo $pagesize ?>)">
    &gt;&gt;
   </a>
  </td>

 </tr>
</table>

</form>

<table border='0' cellpadding='5' cellspacing='0' width='96%'>
 <tr>
  <td><span class='bold'><?php xl('Code','e'); ?></span></td>
  <td><span class='bold'><?php xl('Mod','e'); ?></span></td>
  <td><span class='bold'><?php xl('Act','e'); ?></span></td>
  <td><span class='bold'><?php xl('Rep','e'); ?></span></td>
  <td><span class='bold'><?php xl('Type','e'); ?></span></td>
  <td><span class='bold'><?php xl('Description','e'); ?></span></td>
<?php if (related_codes_are_used()) { ?>
  <td><span class='bold'><?php xl('Related','e'); ?></span></td>
<?php } ?>
<?php
$pres = sqlStatement("SELECT title FROM list_options " .
  "WHERE list_id = 'pricelevel' ORDER BY seq");
while ($prow = sqlFetchArray($pres)) {
  echo "  <td class='bold' align='right' nowrap>" . xl_list_label($prow['title']) . "</td>\n";
}
?>
  <td></td>
  <td></td>
 </tr>
<?php

$res = sqlStatement("SELECT * FROM codes WHERE $where " .
  "ORDER BY code_type, code, code_text LIMIT $fstart, " . ($fend - $fstart));

for ($i = 0; $row = sqlFetchArray($res); $i++) $all[$i] = $row;

if (!empty($all)) {
  $count = 0;
  foreach($all as $iter) {
    $count++;

    $has_fees = false;
    foreach ($code_types as $key => $value) {
      if ($value['id'] == $iter['code_type']) {
        $has_fees = $value['fee'];
        break;
      }
    }

    echo " <tr>\n";
    echo "  <td class='text'>" . $iter["code"] . "</td>\n";
    echo "  <td class='text'>" . $iter["modifier"] . "</td>\n";
    echo "  <td class='text'>" . ($iter["active"] ? xl('Yes') : xl('No')) . "</td>\n";
    echo "  <td class='text'>" . ($iter["reportable"] ? xl('Yes') : xl('No')) . "</td>\n";
    echo "  <td class='text'>$key</td>\n";
    echo "  <td class='text'>" . $iter['code_text'] . "</td>\n";

    if (related_codes_are_used()) {
      // Show related codes.
      echo "  <td class='text'>";
      $arel = explode(';', $iter['related_code']);
      foreach ($arel as $tmp) {
        list($reltype, $relcode) = explode(':', $tmp);
        $reltype = $code_types[$reltype]['id'];
        $relrow = sqlQuery("SELECT code_text FROM codes WHERE " .
          "code_type = '$reltype' AND code = '$relcode' LIMIT 1");
        echo $relcode . ' ' . trim($relrow['code_text']) . '<br />';
      }
      echo "</td>\n";
    }

    $pres = sqlStatement("SELECT p.pr_price " .
      "FROM list_options AS lo LEFT OUTER JOIN prices AS p ON " .
      "p.pr_id = '" . $iter['id'] . "' AND p.pr_selector = '' AND p.pr_level = lo.option_id " .
      "WHERE list_id = 'pricelevel' ORDER BY lo.seq");
    while ($prow = sqlFetchArray($pres)) {
      echo "<td class='text' align='right'>" . bucks($prow['pr_price']) . "</td>\n";
    }

    echo "  <td align='right'><a class='link' href='javascript:submitDelete(" . $iter['id'] . ")'>[" . xl('Delete') . "]</a></td>\n";
    echo "  <td align='right'><a class='link' href='javascript:submitEdit("   . $iter['id'] . ")'>[" . xl('Edit') . "]</a></td>\n";
    echo " </tr>\n";

  }
}

?>

</table>

</center>

<script language="JavaScript">
<?php
 if ($alertmsg) {
  echo "alert('" . htmlentities($alertmsg) . "');\n";
 }
?>
</script>

</body>
</html>

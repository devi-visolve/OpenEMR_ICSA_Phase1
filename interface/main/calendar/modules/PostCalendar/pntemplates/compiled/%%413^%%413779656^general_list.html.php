<?php /* Smarty version 2.6.2, created on 2011-03-10 03:41:52
         compiled from /usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/prescription/general_list.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', '/usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/prescription/general_list.html', 88, false),)), $this); ?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];  ?>" type="text/css">
<script type="text/javascript" src="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/library/js/jquery-1.2.2.min.js"></script>

<?php echo '
<style type="text/css" title="mystyles" media="all">
.inactive {
  color:#777777;
}
</style>

<script language="javascript">

function changeLinkHref(id,addValue,value) {
    var myRegExp = new RegExp(":" + value + ":");
    if (addValue){ //add value to href
        document.getElementById(id).href += \':\' + value + \':\';
    }
    else { //remove value from href
       document.getElementById(id).href = document.getElementById(id).href.replace(myRegExp,\'\');
    }
}

function changeLinkHref_All(id,addValue,value) {
    var myRegExp = new RegExp(":" + value + ":");
    if (addValue){ //add value to href
        document.getElementById(id).href += \':\' + value + \':\';
    }
    else { //remove value from href
        document.getElementById(id).href = document.getElementById(id).href.replace(myRegExp,\'\');
		document.getElementById(id).target = \'\';
    }
}

function Check(chk) {
    var len=chk.length;
    if (len==undefined) {chk.checked=true;}
    else {
        for (pr = 0; pr < chk.length; pr++){
            chk[pr].checked=true;
            changeLinkHref_All(\'multiprint\',true,chk[pr].value);
            changeLinkHref_All(\'multiprintcss\',true, chk[pr].value);
            changeLinkHref_All(\'multiprintToFax\',true, chk[pr].value);
        }
    }
}

function Uncheck(chk) {
    var len=chk.length;
    if (len==undefined) {chk.checked=false;}
    else {
        for (pr = 0; pr < chk.length; pr++){
            chk[pr].checked=false;
            changeLinkHref_All(\'multiprint\',false,chk[pr].value);
            changeLinkHref_All(\'multiprintcss\',false, chk[pr].value);
            changeLinkHref_All(\'multiprintToFax\',false, chk[pr].value);
        }
    }
}

var CheckForChecks = function(chk) {
    // Checks for any checked boxes, if none are found than an alert is raised and the link is killed
    if (Checking(chk) == false) { return false; }
    return top.restoreSession();
};

function Checking(chk) {
    var len=chk.length;
	var foundone=false;
	 
    if (len==undefined) {
			if (chk.checked == true){
				foundone=true;
			}
	} 
	else {
		for (pr = 0; pr < chk.length; pr++){
			if (chk[pr].checked == true) {
				foundone=true;
			}
		}
	}	
	if (foundone) {
		return true;
	} else {
		alert("';  echo smarty_function_xl(array('t' => 'Please select at least one prescription!'), $this); echo '");
		return false;
	}
}

</script>

'; ?>

</head>
<body class="body_top">

<?php if ($this->_tpl_vars['prescriptions']): ?>
<span class="title"><b><?php echo smarty_function_xl(array('t' => 'List'), $this);?>
</b></span>

<div id="prescription_list">

<form name="presc">

<div id="print_links">
    <table width="100%">
        <tr>
            <td align="left">
                <table>
                    <tr>
                        <td>
                            <a id="multiprint" href="<?php echo $this->_tpl_vars['CONTROLLER']; ?>
prescription&multiprint&id=<?php echo $this->_tpl_vars['printm']; ?>
" onclick="top.restoreSession()" class="css_button"><span><?php echo smarty_function_xl(array('t' => 'Print'), $this);?>
 (<?php echo smarty_function_xl(array('t' => 'PDF'), $this);?>
)</span></a>
                        </td>
                        <td>
                            <a id="multiprintcss" href="<?php echo $this->_tpl_vars['CONTROLLER']; ?>
prescription&multiprintcss&id=<?php echo $this->_tpl_vars['printm']; ?>
" onclick="top.restoreSession()" class="css_button"><span><?php echo smarty_function_xl(array('t' => 'Print'), $this);?>
 (<?php echo smarty_function_xl(array('t' => 'HTML'), $this);?>
)</span></a>
                        </td>
                        <td style="border-style:none;">
                            <a id="multiprintToFax" href="<?php echo $this->_tpl_vars['CONTROLLER']; ?>
prescription&multiprintfax&id=<?php echo $this->_tpl_vars['printm']; ?>
" onclick="top.restoreSession()" class="css_button"><span><?php echo smarty_function_xl(array('t' => 'Print'), $this);?>
 (<?php echo smarty_function_xl(array('t' => 'Fax'), $this);?>
)</span></a>
                        </td>
                        <?php if ($this->_tpl_vars['CAMOS_FORM'] == true): ?>
                        <td>
                            <a id="four_panel_rx" href="<?php echo $this->_tpl_vars['WEBROOT']; ?>
/interface/forms/CAMOS/rx_print.php?sigline=plain" onclick="top.restoreSession()" class="css_button"><span><?php echo smarty_function_xl(array('t' => 'Print Four Panel'), $this);?>
</span></a>
                        </td>
                        <?php endif; ?>
                    </tr>
                </table>
            </td>
            <td align="right">
                <table>
                <tr>
                    <td>
                        <a href="#" class="small" onClick="Check(document.presc.check_list);"><span><?php echo smarty_function_xl(array('t' => 'Check All'), $this);?>
</span></a> |
                        <a href="#" class="small" onClick="Uncheck(document.presc.check_list);"><span><?php echo smarty_function_xl(array('t' => 'Clear All'), $this);?>
</span></a>
                    </td>
                </tr>
                </table>
            </td>
        </tr>
    </table>
</div>


<table width="100%" class="showborder_head" cellspacing="0px" cellpadding="2px">
    <tr>
		<th width="8px">&nbsp;</th>
        <th width="180px"><?php echo smarty_function_xl(array('t' => 'Drug'), $this);?>
</th>
        <th><?php echo smarty_function_xl(array('t' => 'Created'), $this);?>
<br /><?php echo smarty_function_xl(array('t' => 'Changed'), $this);?>
</th>
        <th><?php echo smarty_function_xl(array('t' => 'Dosage'), $this);?>
</th>
        <th><?php echo smarty_function_xl(array('t' => 'Qty'), $this);?>
.</th>
        <th><?php echo smarty_function_xl(array('t' => 'Unit'), $this);?>
</th>
        <th><?php echo smarty_function_xl(array('t' => 'Provider'), $this);?>
</th>
    </tr>

	<?php if (count($_from = (array)$this->_tpl_vars['prescriptions'])):
    foreach ($_from as $this->_tpl_vars['prescription']):
?>
  <tr id="<?php echo $this->_tpl_vars['prescription']->id; ?>
" class="showborder onescript <?php if ($this->_tpl_vars['prescription']->active <= 0): ?> inactive<?php endif; ?>" title="<?php echo smarty_function_xl(array('t' => 'Click to view/edit'), $this);?>
">
	 <td align="center">
      <input id="check_list" type="checkbox" value="<?php echo $this->_tpl_vars['prescription']->id; ?>
" onclick="changeLinkHref('multiprint',this.checked, this.value);changeLinkHref('multiprintcss',this.checked, this.value);changeLinkHref('multiprintToFax',this.checked, this.value)" title="<?php echo smarty_function_xl(array('t' => 'Select for printing'), $this);?>
">
    </td>
    <td class="editscript"  id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
        <a class='editscript css_button_small' id='<?php echo $this->_tpl_vars['prescription']->id; ?>
' href="controller.php?prescription&edit&id=<?php echo $this->_tpl_vars['prescription']->id; ?>
" style="margin-top:-2px"><span><?php echo smarty_function_xl(array('t' => 'Edit'), $this);?>
</span></a>
        <?php if ($this->_tpl_vars['prescription']->active > 0): ?><b><?php endif;  echo $this->_tpl_vars['prescription']->drug;  if ($this->_tpl_vars['prescription']->active > 0): ?></b><?php endif; ?>&nbsp;
    </td>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo $this->_tpl_vars['prescription']->date_added; ?>
<br />
      <?php echo $this->_tpl_vars['prescription']->date_modified; ?>
&nbsp;
    </td>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo $this->_tpl_vars['prescription']->get_dosage_display(); ?>
 &nbsp;
    </td>
    <td class="editscript" id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo $this->_tpl_vars['prescription']->quantity; ?>
 &nbsp;
    </td>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
       <?php echo $this->_tpl_vars['prescription']->get_size(); ?>
 <?php echo $this->_tpl_vars['prescription']->get_unit_display(); ?>
&nbsp;
    </td>
    <td id="<?php echo $this->_tpl_vars['prescription']->id; ?>
">
      <?php echo $this->_tpl_vars['prescription']->provider->get_name_display(); ?>
&nbsp;
    </td>
  </tr>
	<?php endforeach; unset($_from); endif; ?>
</table>

</form>
</div>

<?php else: ?>
<div class="text" style="margin-top:10px"><?php echo smarty_function_xl(array('t' => 'There are currently no prescriptions'), $this);?>
.</div>
<?php endif; ?>

</body>
<?php echo '
<script language=\'JavaScript\'>

$(document).ready(function(){
$("#multiprint").click(function() { return CheckForChecks(document.presc.check_list); });
$("#multiprintcss").click(function() { return CheckForChecks(document.presc.check_list); });
$("#multiprintToFax").click(function() { return CheckForChecks(document.presc.check_list); });
$(".editscript").click(function() { ShowScript(this); });
$(".onescript").mouseover(function() { $(this).children().toggleClass("highlight"); });
$(".onescript").mouseout(function() { $(this).children().toggleClass("highlight"); });
});

var ShowScript = function(eObj) {
    top.restoreSession();
    objID = eObj.id;
    document.location.href="';  echo $this->_tpl_vars['WEB_ROOT'];  echo '/controller.php?prescription&edit&id="+objID;
    return true;
};

</script>
'; ?>

</html>
<?php /* Smarty version 2.6.2, created on 2011-03-10 11:28:09
         compiled from /usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/documents/general_view.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', '/usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/documents/general_view.html', 43, false),array('modifier', 'escape', '/usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/documents/general_view.html', 107, false),)), $this); ?>
<head>
<style type="text/css">@import url(library/dynarch_calendar.css);</style>
<script type="text/javascript" src="library/dialog.js"></script>
<script type="text/javascript" src="library/textformat.js"></script>
<script type="text/javascript" src="library/dynarch_calendar.js"></script>
<?php  include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php");  ?>
<script type="text/javascript" src="library/dynarch_calendar_setup.js"></script>
<script language="JavaScript">
 var mypcc = '<?php  echo $GLOBALS['phone_country_code']  ?>';

 // Process click on Delete link.
 function deleteme(docid) <?php echo '{'; ?>

  dlgopen('interface/patient_file/deleter.php?document=' + docid, '_blank', 500, 450);
  return false;
 <?php echo '}'; ?>


 // Called by the deleter.php window on a successful delete.
 function imdeleted() <?php echo '{'; ?>

  top.restoreSession();
  window.location.href='<?php echo $this->_tpl_vars['REFRESH_ACTION']; ?>
';
 <?php echo '}'; ?>


 // Called to show patient notes related to this document in the "other" frame.
 function showpnotes(docid) <?php echo '{'; ?>

<?php  if ($GLOBALS['concurrent_layout']) {  ?>
  var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
  parent.left_nav.forceDual();
  parent.left_nav.setRadio(othername, 'pno');
  parent.left_nav.loadFrame('pno1', othername, 'patient_file/summary/pnotes.php?docid=' + docid);
<?php  }  ?>
  return false;
 <?php echo '}'; ?>



</script>

</head>


<table valign="top">
    <tr>
        <td>
            <a class="css_button" href="<?php echo $this->_tpl_vars['web_path']; ?>
" onclick="top.restoreSession()"><span><?php echo smarty_function_xl(array('t' => 'Download'), $this);?>
</span></a>
            <a class="css_button" href='' onclick='return showpnotes(<?php echo $this->_tpl_vars['file']->get_id(); ?>
)'><span><?php echo smarty_function_xl(array('t' => 'Show Notes'), $this);?>
</span></a>
            <?php echo $this->_tpl_vars['delete_string']; ?>

        </td>
    </tr>
    <tr>
		<td valign="top">
			<div class="text">
                <form method="post" name="document_validate" action="<?php echo $this->_tpl_vars['VALIDATE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => 'Sha-1 Hash'), $this);?>
:</b>&nbsp;
                        <i><?php echo $this->_tpl_vars['file']->get_hash(); ?>
</i>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['document_validate'].submit();">(<span><?php echo smarty_function_xl(array('t' => 'validate'), $this);?>
)</span></a>
                    </div>
                </div>
                </form>
               </div>
            <div class="text">
                <form method="post" name="document_update" action="<?php echo $this->_tpl_vars['UPDATE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => 'Update'), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['document_update'].submit();">(<span><?php echo smarty_function_xl(array('t' => 'submit'), $this);?>
)</span></a>
                    </div>
                </div>
                <div>
                    <?php echo smarty_function_xl(array('t' => 'Rename'), $this);?>
:
                    <input type='text' size='20' name='docname' id='docname' value='<?php echo $this->_tpl_vars['file']->get_url_web(); ?>
'/>
              	</div>
                <div>
                    <?php echo smarty_function_xl(array('t' => 'Date'), $this);?>
:
                    <input type='text' size='10' name='docdate' id='docdate'
                     value='<?php echo $this->_tpl_vars['DOCDATE']; ?>
' title='<?php echo smarty_function_xl(array('t' => 'yyyy-mm-dd document date'), $this);?>
'
                     onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
                    <img src='interface/pic/show_calendar.gif' id='img_docdate' align='absbottom'
                     width='24' height='22' border='0' alt='[?]' style='cursor:pointer'
                     title='<?php echo smarty_function_xl(array('t' => 'Click here to choose a date'), $this);?>
' />
                    <select name="issue_id"><?php echo $this->_tpl_vars['ISSUES_LIST']; ?>
</select>
                </div>
                </form>
            </div>

            <br/>

            <div class="text">
                <form method="post" name="document_move" action="<?php echo $this->_tpl_vars['MOVE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => 'Move'), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['document_move'].submit();">(<span><?php echo smarty_function_xl(array('t' => 'submit'), $this);?>
)</span></a>
                    </div>
                </div>

                <div>
                        <select name="new_category_id"><?php echo $this->_tpl_vars['tree_html_listbox']; ?>
</select>&nbsp;
                        <?php echo smarty_function_xl(array('t' => 'Move to Patient'), $this);?>
 # <input type="text" name="new_patient_id" size="4" />
                        <a href="javascript:<?php echo '{}'; ?>
"
                         onclick="top.restoreSession();var URL='controller.php?patient_finder&find&form_id=<?php echo ((is_array($_tmp="document_move['new_patient_id']")) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
&form_name=<?php echo ((is_array($_tmp="document_move['new_patient_name']")) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
'; window.open(URL, 'document_move', 'toolbar=0,scrollbars=1,location=0,statusbar=1,menubar=0,resizable=1,width=450,height=400,left=425,top=250');">
                        <img src="images/stock_search-16.png" border="0" /></a>
                        <input type="hidden" name="new_patient_name" value="" />
                </div>
                </form>
            </div>

            <br/>

            <form name="notes" method="post" action="<?php echo $this->_tpl_vars['NOTE_ACTION']; ?>
" onsubmit="return top.restoreSession()">
            <div class="text">
                <div>
                    <div style="float:left">
                        <b><?php echo smarty_function_xl(array('t' => 'Notes'), $this);?>
</b>&nbsp;
                    </div>
                    <div style="float:none">
                        <a href="javascript:;" onclick="document.forms['notes'].submit();">(<span><?php echo smarty_function_xl(array('t' => 'add'), $this);?>
</span>)</a>
                    </div>
                <div>
                    <textarea cols="53" rows="4" wrap="virtual" name="note"></textarea><br>
                    <input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
                    <input type="hidden" name="foreign_id" value="<?php echo $this->_tpl_vars['file']->get_id(); ?>
" />

                    <?php if ($this->_tpl_vars['notes']): ?>
                    <div style="margin-top:7px">
                        <?php if (isset($this->_foreach['note_loop'])) unset($this->_foreach['note_loop']);
$this->_foreach['note_loop']['name'] = 'note_loop';
$this->_foreach['note_loop']['total'] = count($_from = (array)$this->_tpl_vars['notes']);
$this->_foreach['note_loop']['show'] = $this->_foreach['note_loop']['total'] > 0;
if ($this->_foreach['note_loop']['show']):
$this->_foreach['note_loop']['iteration'] = 0;
    foreach ($_from as $this->_tpl_vars['note']):
        $this->_foreach['note_loop']['iteration']++;
        $this->_foreach['note_loop']['first'] = ($this->_foreach['note_loop']['iteration'] == 1);
        $this->_foreach['note_loop']['last']  = ($this->_foreach['note_loop']['iteration'] == $this->_foreach['note_loop']['total']);
?>
                        <div>
                        <?php echo smarty_function_xl(array('t' => 'Note'), $this);?>
 #<?php echo $this->_tpl_vars['note']->get_id(); ?>

                        <?php echo smarty_function_xl(array('t' => 'Date:'), $this);?>
 <?php echo $this->_tpl_vars['note']->get_date(); ?>

                        <?php echo $this->_tpl_vars['note']->get_note(); ?>

                        </div>
                        <?php endforeach; unset($_from); endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            </form>

		</td>
	</tr>
	<tr>
		<td>
            <div class="text"><b><?php echo smarty_function_xl(array('t' => 'Content'), $this);?>
</b></div>
            <?php if ($this->_tpl_vars['file']->get_mimetype() == "image/tiff"): ?>
			<embed frameborder="0" type="<?php echo $this->_tpl_vars['file']->get_mimetype(); ?>
" src="<?php echo $this->_tpl_vars['web_path']; ?>
as_file=false"></embed>
			<?php elseif ($this->_tpl_vars['file']->get_mimetype() == "image/png" || $this->_tpl_vars['file']->get_mimetype() == "image/jpg" || $this->_tpl_vars['file']->get_mimetype() == "image/jpeg" || $this->_tpl_vars['file']->get_mimetype() == "image/gif" || $this->_tpl_vars['file']->get_mimetype() == "application/pdf"): ?>
			<iframe frameborder="0" type="<?php echo $this->_tpl_vars['file']->get_mimetype(); ?>
" src="<?php echo $this->_tpl_vars['web_path']; ?>
as_file=false"></iframe>
			<?php else: ?>
			<iframe frameborder="0" type="<?php echo $this->_tpl_vars['file']->get_mimetype(); ?>
" src="<?php echo $this->_tpl_vars['web_path']; ?>
as_file=true"></iframe>
			<?php endif; ?>
		</td>
	</tr>
</table>
<script language='JavaScript'>
 Calendar.setup(<?php echo '{'; ?>
inputField:"docdate", ifFormat:"%Y-%m-%d", button:"img_docdate"<?php echo '}'; ?>
);
</script>
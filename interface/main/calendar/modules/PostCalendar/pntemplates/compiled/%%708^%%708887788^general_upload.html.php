<?php /* Smarty version 2.6.2, created on 2011-03-10 11:27:35
         compiled from /usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/documents/general_upload.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', '/usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/documents/general_upload.html', 5, false),)), $this); ?>
<form method=post enctype="multipart/form-data" action="<?php echo $this->_tpl_vars['FORM_ACTION']; ?>
" onsubmit="return top.restoreSession()">
<input type="hidden" name="MAX_FILE_SIZE" value="64000000" />

<div class="text">
    <?php echo smarty_function_xl(array('t' => "NOTE: Uploading files with duplicate names will cause the files to be automatically renamed (for example, file.jpg will become file.1.jpg). Filenames are considered unique per patient, not per category."), $this);?>

    <br/>
    <br/>
</div>
<div class="text bold">
    <?php echo smarty_function_xl(array('t' => 'Upload Document'), $this);?>
 <?php if ($this->_tpl_vars['category_name']): ?> <?php echo smarty_function_xl(array('t' => 'to category'), $this);?>
 '<?php echo $this->_tpl_vars['category_name']; ?>
'<?php endif; ?>
</div>
<div class="text">
    <p><span><?php echo smarty_function_xl(array('t' => 'Source File Path'), $this);?>
:</span> <input type="file" name="file" id="source-name"/></p>
    <p><span title="<?php echo smarty_function_xl(array('t' => 'Leave Blank To Keep Original Filename'), $this);?>
"><?php echo smarty_function_xl(array('t' => 'Destination Name'), $this);?>
:</span> <input type="text" name="destination" title="<?php echo smarty_function_xl(array('t' => 'Leave Blank To Keep Original Filename'), $this);?>
" id="destination-name" /></p>
    <p><input type="submit" value="<?php echo smarty_function_xl(array('t' => 'Upload'), $this);?>
" /></p>
</div>
<?php if (! empty ( $this->_tpl_vars['file'] )): ?>
<div class="text bold">
    <br/>
    <?php echo smarty_function_xl(array('t' => 'Upload Report'), $this);?>

</div>
<div class="text">
    <?php if ($this->_tpl_vars['error']): ?><i><?php echo $this->_tpl_vars['error']; ?>
</i><br/><?php endif; ?>
    <?php echo smarty_function_xl(array('t' => 'ID'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_id(); ?>
<br>
    <?php echo smarty_function_xl(array('t' => 'Patient'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_foreign_id(); ?>
<br>
    <?php echo smarty_function_xl(array('t' => 'URL'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_url(); ?>
<br>
    <?php echo smarty_function_xl(array('t' => 'Size'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_size(); ?>
<br>
    <?php echo smarty_function_xl(array('t' => 'Date'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_date(); ?>
<br>
    <?php echo smarty_function_xl(array('t' => 'Hash'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_hash(); ?>
<br>
    <?php echo smarty_function_xl(array('t' => 'MimeType'), $this);?>
: <?php echo $this->_tpl_vars['file']->get_mimetype(); ?>
<br>
    <?php echo smarty_function_xl(array('t' => 'Revision'), $this);?>
: <?php echo $this->_tpl_vars['file']->revision; ?>
<br>
</div>
<?php endif; ?>

<input type="hidden" name="patient_id" value="<?php echo $this->_tpl_vars['patient_id']; ?>
" />
<input type="hidden" name="category_id" value="<?php echo $this->_tpl_vars['category_id']; ?>
" />
<input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
</form>
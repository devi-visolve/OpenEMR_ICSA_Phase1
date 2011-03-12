<?php /* Smarty version 2.6.2, created on 2011-03-10 03:42:32
         compiled from /usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/prescription/general_send.html */ ?>
<?php require_once(SMARTY_DIR . 'core' . DIRECTORY_SEPARATOR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', '/usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/prescription/general_send.html', 8, false),array('function', 'html_options', '/usr/local/apache2.2.11/htdocs/vicareplus/demo/ICSA/templates/prescription/general_send.html', 44, false),)), $this); ?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['CSS_HEADER']; ?>
" type="text/css">
</head>
<body class="body_top">

<span class="title"><b><?php echo smarty_function_xl(array('t' => 'Send'), $this);?>
</b></span>
<div style="margin-top:10px;">
    <?php if ($this->_tpl_vars['process_result']): ?>
        <?php echo $this->_tpl_vars['process_result']; ?>

        <br/>
    <?php endif; ?>

    <div style="float:left">
        <form name="genform1" method="post" action="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
send&id=<?php echo $this->_tpl_vars['prescription']->id; ?>
" target="_new" onsubmit="return top.restoreSession()">
        <input type="submit" name="submit" value="<?php echo smarty_function_xl(array('t' => 'Print'), $this);?>
 (<?php echo smarty_function_xl(array('t' => 'PDF'), $this);?>
)" style="width:100;font-size:9pt;"/>
        <input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
        </form>
    </div>

    <div style="float:left">
        <form name="send_prescription" method="post" action="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
send&id=<?php echo $this->_tpl_vars['prescription']->id; ?>
" target="_new" onsubmit="return top.restoreSession()">
        <input type="submit" name="submit" value="<?php echo smarty_function_xl(array('t' => 'Print'), $this);?>
 (<?php echo smarty_function_xl(array('t' => 'HTML'), $this);?>
)" style="width:100;font-size:9pt;"/>
        <input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
        </form>
    </div>

    <div style="float:none">
        <form name="send_prescription" method="post" action="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
send&id=<?php echo $this->_tpl_vars['prescription']->id; ?>
&print_to_fax=true" target="_new" onsubmit="return top.restoreSession()">
        <input type="submit" name="submit" value="<?php echo smarty_function_xl(array('t' => 'Print To Fax'), $this);?>
" style="width:100;font-size:9pt;"/>
        <input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
        </form>
    </div>

    <div>
        <form name="send_prescription" method="post" action="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
send&id=<?php echo $this->_tpl_vars['prescription']->id; ?>
" onsubmit="return top.restoreSession()">
        <input type="submit" name="submit" value="<?php echo smarty_function_xl(array('t' => 'Email'), $this);?>
" style="width:100;font-size:9pt;" /><input type="text" name="email_to"  size="25" value="<?php echo $this->_tpl_vars['prescription']->pharmacy->get_email(); ?>
">
        <br/>
        <input type="submit" name="submit" value="<?php echo smarty_function_xl(array('t' => 'Fax'), $this);?>
" style="width:100;font-size:9pt;"/><input type="text" name="fax_to"  size="25" value="<?php echo $this->_tpl_vars['prescription']->pharmacy->get_fax(); ?>
" >
        <input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
        </form>
        <form name="send_prescription" method="post" action="<?php echo $this->_tpl_vars['TOP_ACTION']; ?>
send&id=<?php echo $this->_tpl_vars['prescription']->id; ?>
" target="_new" onsubmit="return top.restoreSession()">
        <input type="submit" name="submit" value="<?php echo smarty_function_xl(array('t' => 'Auto Send'), $this);?>
" style="width:100;font-size:9pt;" /> <?php echo smarty_function_html_options(array('name' => 'pharmacy_id','options' => $this->_tpl_vars['prescription']->pharmacy->utility_pharmacy_array(),'selected' => $this->_tpl_vars['prescription']->pharmacy->id), $this);?>

        <input type="hidden" name="process" value="<?php echo $this->_tpl_vars['PROCESS']; ?>
" />
        </form>
    </div>
</div>

</body>
</html>
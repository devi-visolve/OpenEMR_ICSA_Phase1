<?php /* Smarty version 2.3.1, created on 2011-03-10 01:21:16
         compiled from default/views/header.html */ ?>
<html>
<head>
<!-- Get the style sheet for the theme defined in globals.php -->
<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'] ?>" type="text/css">

<?php if ($this->_tpl_vars['cal_ui'] == 3): ?>
<!-- this style sheet is used for the ajax_* style calendars -->
<link rel="stylesheet" href="<?php echo $GLOBALS['webroot'].'/interface/themes/ajax_calendar.css'; ?>" type="text/css">
<!-- the javascript used for the ajax_* style calendars -->
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php  echo $GLOBALS['webroot']  ?>/library/js/jquery-1.2.2.min.js"></script>
<?php endif; ?>

</head>
<?php 
/* in an attempt to not 'rock the boat' too much the concurrent_layout
 * color scheme remains unchanged
 */
if ($GLOBALS['concurrent_layout']) {
    echo "<body style='background-color:".$GLOBALS['style']['BGCOLOR2']."'>";
}
else {
    echo "<body class='body_top'>";
}
 ?>
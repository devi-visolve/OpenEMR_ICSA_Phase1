<!-- Form created by Andres paglayan -->
<?php
include_once("../../globals.php");
include_once("C_WellChildCare.class.php");
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">

<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_well_child_care", $_GET["id"]);
?>

<form method=post action="<?php echo $rootdir?>/forms/well_child_care/save.php?mode=update&id=<?php echo $_GET["id"];?>" name="my_form">

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>
<br></br>
<!-- Form goes here -->

<?php
	
	include_once("C_WellChildCare.class.php");
	$form=new C_WellChildCare($pid);
	$a=$form->put_form($obj);

?>

<!-- Form ends here -->
<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[Save]</a>
<br>
<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save Changes]</a>

</form>
<?php
formFooter();
?>

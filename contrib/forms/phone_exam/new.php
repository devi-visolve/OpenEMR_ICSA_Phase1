<?php

// file new.php

include_once("../../globals.php");

include_once("../../../library/api.inc");

formHeader("Phone Exam");

?>

<html><head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>

<body class="body_top">



<br>

<form method='post' action="<?php echo $rootdir;?>/forms/phone_exam/save.php?mode=new" name='phone_exam_form' enctype="multipart/form-data">

<span class=title>Phone Exam</span>

<br>



<span class=text>Notes:</span><br>

<textarea name="notes" wrap="virtual" cols="45" rows="10"></textarea><br>



<!--REM note our nifty jscript submit -->

<input type="hidden" name="action" value="submit">

<a href="javascript:top.restoreSession();document.phone_exam_form.submit();" class="link_submit">[Save]</a>

<br>



<a href="<?php echo $GLOBALS['form_exit_url']; ?>" class="link" onclick="top.restoreSession()">[Don't Save]</a>

</form>



<?php

formFooter();

?>


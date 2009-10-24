<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<title>Forgotten Password Request</title>
	</head>
	<body>
		<b>Forgotten Password Request</b>
		<p>You (<?php echo $identity; ?>) have requested a new password.</p>
		<p>Please click the link below and enter this code into the box.</p>
		<p><b><?php echo $forgotten_password_code; ?></b></p>
		<p><A HREF="<?= $this->config->site_url(); ?>auth/resetpass">Click here to reset password</A></p>
	</body>
</html>

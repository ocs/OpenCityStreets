<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    	<head>
        	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" type="text/css" href="<?= site_url('css') ?>/template.css"/>

<!-- <script language="javascript" src="<?= site_url() ?>js/jquery-1.3.2.min.js" ></script>
<script language="javascript" src="<?= site_url() ?>js/jquery-ui-1.7.2.custom.min.js" ></script>
-->
		<title><?= $this->config->item('gamename','ocs'); ?> client v<?= $this->config->item('version','ocs'); ?></title>
	</head>
	<body>

		<div id='header'>
			<center>
				<img src='<?= site_url('images') ?>/ocs_logo_big.jpg'>
			</center>
		</div>

		<div id='content'>
			<?= $content ?>
		</div>

		<div id='footer'>
			<P>&nbsp;<P>
			<B><I><?= $this->config->item('motd','ocs'); ?> </I></B>
			
				

		</div>


	</body>
</html>

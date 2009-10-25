<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
        <head>
                <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
                <title>New account registration</title>
        </head>
<body>

<B>Welcome to <?= $this->config->item('gamename','ocs'); ?></B>
<P>
Someone, hopefully you, has registed for a new player account with the following details:<P>

Username: <?= $username; ?>
<br>
email: <?= $email; ?>
<P>
In order to start playing you'll first need to confirm that you received this email.
<P>
Either <A HREF="<?= $this->config->site_url(); ?>auth/activatebyclick/<?= $activation ?>">click on this link</A>, or return to the game web site, choose Activate Account when asked to log in, and enter your username and this code:
<P>
<b><?= $activation; ?></b>
<P>
Thank you for your interest in <?= $this->config->item('gamename','ocs'); ?>, hope to see you in the game soon!

</body>
</html>


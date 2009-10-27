<P>
<B>You are logged in.</B>
<P>&nbsp;<P>
Your profile<P>&nbsp;<P>

Session ID: <?=   $this->session->userdata('session_id'); ?><br>
User ID: <?=   $this->session->userdata('user_id'); ?><br>
Username: <?= $user_profile->username; ?><br>
Email: <?= $user_profile->email; ?><br>
Language: <?= $user_profile->language; ?><br>

</p>
<P>
<A href="<?= site_url('auth/delete') ?>">Delete this account</a>
</P>
<P>
<A href="<?= site_url('auth/logout') ?>">Log out</a>
</P>


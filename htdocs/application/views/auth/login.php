<h1>Login to <?= $this->config->item('gamename','ocs') ?></h1>

<?php echo $this->session->flashdata('message'); ?>

<?php echo validation_errors(); ?>


<?php echo form_open('auth/login/attempt'); ?>

<table>
    <tbody>
	
        <tr>
            	<td>Username:</td>
            	<td>
			<input type="text" id="username" name="username" />
		</td>
        </tr>
        <tr>
            	<td>Password:</td>
            	<td>
			<input type="password" id="password" name="password" />
		</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">
		<?php echo form_submit('submit', 'Login'); ?>
	    </td>
        </tr>

    </tfoot>
</table>

<?php echo form_close(''); ?>

<P>&nbsp;</P><P>

<A href="<?= site_url('auth/register') ?>">Create Account</a>
&nbsp;&nbsp;&nbsp;
<A href="<?= site_url('auth/activate') ?>">Activate Account</a>
&nbsp;&nbsp;&nbsp;
<A href="<?= site_url('auth/lostpass') ?>">Lost Password</a>

<P>&nbsp;</P><P>


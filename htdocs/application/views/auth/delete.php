<h1>Delete your <?= $this->config->item('gamename','ocs') ?> account?</h1>

<?php echo $this->session->flashdata('message'); ?>

<?php echo validation_errors(); ?>

<?php echo form_open('auth/delete'); ?>

<table>
    <tbody>
	
        <tr>
            	<td colspan =2>Enter your password to delete "<?= $this->session->userdata('username');?>" (This cannot be undone!)</td>
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
		<?php echo form_submit('submit', 'Delete my account'); ?>
	    </td>
        </tr>

    </tfoot>
</table>

<?php echo form_close(''); ?>

<P>&nbsp;</P><P>


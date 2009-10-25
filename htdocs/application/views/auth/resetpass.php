<h1>Reset Password</h1>

<p>Please enter the verification code that was emailed to you, and a new password.</p>


<?php echo $this->session->flashdata('message'); ?>

<?php echo validation_errors(); ?>


<?php echo form_open('auth/resetpass'); ?>


<table>

    <tbody>

        <tr>

            <td>Verification Code</td>

            <td><?php echo form_input('code', set_value('code')); ?></td>

        </tr>

        <tr>

            <td>New password</td>

            <td><?php echo form_password('password', set_value('password')); ?></td>

        </tr>

        <tr>

            <td>Confirm password</td>

            <td><?php echo form_password('password2', set_value('password2')); ?></td>

        </tr>

    </tbody>

    <tfoot>

        <tr>

            <td colspan="2"><?php echo form_submit('submit', 'Reset Password'); ?></td>

        </tr>

    </tfoot>

</table>



<?php echo form_close(''); ?>

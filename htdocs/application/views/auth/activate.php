<h1>Account Activation</h1>



<p>Please enter your activation code from the registration email.</p>
<P>&nbsp;


<?php echo $this->session->flashdata('message'); ?>

<?php echo validation_errors(); ?>



<?php echo form_open('auth/activate'); ?>



<table>

    <tbody>

        <tr>

            <td>Verification Code</td>

            <td><?php echo form_input('code', set_value('code')); ?></td>

        </tr>

    </tbody>

    <tfoot>

        <tr>

            <td colspan="2"><?php echo form_submit('submit', 'Activate'); ?></td>

        </tr>

    </tfoot>

</table>



<?php echo form_close(''); ?>

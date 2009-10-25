<h1>Register</h1>


<?php echo $this->session->flashdata('message'); ?>


<?php echo validation_errors(); ?>



<?php echo form_open('auth/register'); ?>

<table>

    <tbody>

        <tr>

            <td>Username</td>

            <td><?php echo form_input('username', set_value('username')); ?></td>

        </tr>

        <tr>

            <td>Email Address</td>

            <td><?php echo form_input('email'); ?></td>

        </tr>

        <tr>

            <td>Password</td>

            <td><?php echo form_password('password'); ?></td>

        </tr>

        <tr>

            <td>Confirm</td>

            <td><?php echo form_password('password2'); ?></td>

        </tr>

        <tr>

            	<td>Language</td>

            	<td>

			

		</td>
        </tr>



      </tbody>
   </table>

 <?= $recaptcha ?>
<P>&nbsp;</P><P>

<?php echo form_submit('submit', 'Register'); ?>


<?php echo form_close(''); ?>

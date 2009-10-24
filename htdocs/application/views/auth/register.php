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

            <td><?php echo form_input('email', set_value('email')); ?></td>

        </tr>

        <tr>

            <td>Password</td>

            <td><?php echo form_password('password'); ?></td>

        </tr>
      </tbody>
   </table>

 <?= $recaptcha ?>
<P>&nbsp;</P><P>

<?php echo form_submit('submit', 'Register'); ?>


<?php echo form_close(''); ?>

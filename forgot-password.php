<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
}

?>

<div class="auth-container">
    <div class="auth-inner-container">
        <div class="brand-name">
            <span>FASHION PALETTE</span>
        </div>
        <div class="brand-icon">
            <i class="fa fa-palette"></i>
        </div>
        <form class="auth-form" action="../../index.php" method="POST">
            <div class="form-title text-content">Forgot Password</div>
            <div class="form-link text-content my-2">
                <a class="form-link" onclick="location='reset-password'">Already have a reset code?</a>
            </div>
            <?php if (isset($form_data['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <?php echo $form_data['error']; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            <div class="form-group py-1">
                <div class="input-wrapper">
                    <input id="email" name="email" type="email" class="form-control with-icon"
                           placeholder="Email" autocomplete="username"
                           value="<?php echo get_form_val('email'); ?>">
                    <i class="input-icon fa fa-user" aria-hidden="true"></i>
                </div>
            </div>
            <button type="submit" class="btn btn-custom-primary">Send Reset Code</button>
            <div class="form-link text-content my-3">
                <a class="form-link" onclick="location='login'">Back to Sign in</a>
            </div>
        </form>
    </div>
</div>

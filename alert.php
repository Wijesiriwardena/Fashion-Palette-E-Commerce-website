<?php if (isset($form_data["message"])): ?>
    <div class="alert alert-success alert-dismissible text-center">
        <?php echo $form_data["message"]; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif;
if (isset($form_data["warn"])): ?>
    <div class="alert alert-warning alert-dismissible text-center">
        <?php echo $form_data["warn"]; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif;
if (isset($form_data["error"])): ?>
    <div class="alert alert-danger alert-dismissible text-center">
        <?php echo $form_data["error"]; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif;

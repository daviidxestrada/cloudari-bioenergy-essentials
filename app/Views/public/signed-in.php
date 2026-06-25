<?php
if (!defined('ABSPATH')) {
    exit;
}
include CLOUDARI_BIOENERGY_ESSENTIALS_VIEW_DIR . 'public/partials/login-styles.php';
?>
<div class="cloudari-login-box">
    <h2>Members Area</h2>
    <p>You are signed in.</p>
    <p><a class="cloudari-login-button" href="<?php echo esc_url($logout_url); ?>">Sign out</a></p>
</div>

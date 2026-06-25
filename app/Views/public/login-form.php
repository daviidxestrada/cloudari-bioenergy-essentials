<?php
if (!defined('ABSPATH')) {
    exit;
}
include CLOUDARI_BIOENERGY_ESSENTIALS_VIEW_DIR . 'public/partials/login-styles.php';
?>
<div class="cloudari-login-box">
    <h2>Members Area</h2>
    <?php echo wp_kses_post($message); ?>
    <form method="post" action="<?php echo esc_url($action_url); ?>">
        <?php wp_nonce_field('cloudari_bioenergy_login'); ?>
        <input type="hidden" name="cloudari_bioenergy_login" value="1">
        <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>">
        <label for="cloudari-member-username">Username</label>
        <input id="cloudari-member-username" name="member_username" type="text" autocomplete="username" required>
        <label for="cloudari-member-password">Password</label>
        <input id="cloudari-member-password" name="member_password" type="password" autocomplete="current-password" required>
        <button type="submit">Log in</button>
    </form>
</div>

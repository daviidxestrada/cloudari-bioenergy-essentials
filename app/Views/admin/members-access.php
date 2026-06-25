<?php
if (!defined('ABSPATH')) {
    exit;
}

$labels = isset($labels) && is_array($labels) ? $labels : array();
?>
<div class="wrap cloudari-bioenergy-access">
    <h1><?php echo esc_html($labels['page_title']); ?></h1>
    <?php echo wp_kses_post($notice); ?>
    <p><?php echo esc_html($labels['intro']); ?></p>

    <style>
        .cloudari-bioenergy-access .cloudari-panel {
            max-width: 980px;
            margin-top: 20px;
            padding: 18px 20px;
            background: #fff;
            border: 1px solid #dcdcde;
        }
        .cloudari-bioenergy-access .cloudari-panel h2 {
            margin-top: 0;
        }
        .cloudari-bioenergy-access .cloudari-user-table input[type="text"],
        .cloudari-bioenergy-access .cloudari-user-table input[type="password"] {
            width: 100%;
            max-width: 260px;
        }
        .cloudari-bioenergy-access .cloudari-add-grid {
            display: grid;
            grid-template-columns: minmax(160px, 280px) minmax(160px, 280px) auto;
            gap: 12px;
            align-items: end;
            max-width: 760px;
        }
        .cloudari-bioenergy-access .cloudari-field label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }
        .cloudari-bioenergy-access .cloudari-field input {
            width: 100%;
        }
        @media (max-width: 782px) {
            .cloudari-bioenergy-access .cloudari-add-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="cloudari-panel">
        <h2><?php echo esc_html($labels['users_title']); ?></h2>
        <table class="widefat striped cloudari-user-table">
            <thead>
                <tr>
                    <th><?php echo esc_html($labels['username']); ?></th>
                    <th><?php echo esc_html($labels['new_password']); ?></th>
                    <th><?php echo esc_html($labels['created']); ?></th>
                    <th><?php echo esc_html($labels['actions']); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$members) : ?>
                <tr><td colspan="4"><?php echo esc_html($labels['no_members']); ?></td></tr>
            <?php else : ?>
                <?php foreach ($members as $username => $member) : ?>
                    <?php $form_id = 'cloudari-member-edit-' . sanitize_html_class($username); ?>
                    <tr>
                        <td>
                            <form method="post" id="<?php echo esc_attr($form_id); ?>">
                                <?php echo wp_nonce_field('cloudari_bioenergy_members', '_wpnonce', true, false); ?>
                                <input type="hidden" name="cloudari_bioenergy_action" value="update_member">
                                <input type="hidden" name="current_username" value="<?php echo esc_attr($username); ?>">
                                <input type="text" name="member_username" value="<?php echo esc_attr($username); ?>" autocomplete="off">
                            </form>
                        </td>
                        <td>
                            <input form="<?php echo esc_attr($form_id); ?>" type="password" name="member_password" autocomplete="new-password" placeholder="<?php echo esc_attr($labels['leave_blank']); ?>">
                        </td>
                        <td><?php echo !empty($member['created_at']) ? esc_html(gmdate('Y-m-d H:i', (int) $member['created_at'])) : '-'; ?></td>
                        <td>
                            <button form="<?php echo esc_attr($form_id); ?>" class="button button-primary" type="submit"><?php echo esc_html($labels['save']); ?></button>
                            <form method="post" style="display:inline-block;margin-left:6px">
                                <?php echo wp_nonce_field('cloudari_bioenergy_members', '_wpnonce', true, false); ?>
                                <input type="hidden" name="cloudari_bioenergy_action" value="delete_member">
                                <input type="hidden" name="current_username" value="<?php echo esc_attr($username); ?>">
                                <button class="button button-link-delete" type="submit"><?php echo esc_html($labels['delete']); ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="cloudari-panel">
        <h2><?php echo esc_html($labels['add_title']); ?></h2>
        <form method="post">
            <?php wp_nonce_field('cloudari_bioenergy_members'); ?>
            <input type="hidden" name="cloudari_bioenergy_action" value="add_member">
            <div class="cloudari-add-grid">
                <div class="cloudari-field">
                    <label for="cloudari-member-username"><?php echo esc_html($labels['username']); ?></label>
                    <input name="member_username" id="cloudari-member-username" type="text" autocomplete="off" required>
                </div>
                <div class="cloudari-field">
                    <label for="cloudari-member-password"><?php echo esc_html($labels['password']); ?></label>
                    <input name="member_password" id="cloudari-member-password" type="password" autocomplete="new-password" required>
                </div>
                <div>
                    <button class="button button-primary" type="submit"><?php echo esc_html($labels['add_user']); ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

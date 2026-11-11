<?php
/* KO Functions administrator-only account email changes */
defined('ABSPATH') || exit;

function ko_cf_can_change_account_email() {
    $actor = wp_get_current_user();
    return $actor->ID > 0 && (
        in_array('administrator', (array) $actor->roles, true)
        || (is_multisite() && is_super_admin($actor->ID))
    );
}

function ko_cf_email_change_is_blocked($user_id, $email) {
    if (!$user_id || ko_cf_can_change_account_email()) {
        return false;
    }
    $existing = get_userdata((int) $user_id);
    return $existing && (string) $email !== (string) $existing->user_email;
}

function ko_cf_email_change_message() {
    return 'Only an administrator can change account email addresses. Contact your site administrator for help.';
}

// Reject profile edits before WordPress saves them.
add_action('user_profile_update_errors', function ($errors, $update, $user) {
    if ($update && isset($user->user_email) && ko_cf_email_change_is_blocked($user->ID, $user->user_email)) {
        $errors->add('ko_email_change_restricted', ko_cf_email_change_message(), array('form-field' => 'email'));
    }
}, PHP_INT_MAX, 3);

// Reject REST updates, including /users/me and application-password requests.
add_filter('rest_pre_insert_user', function ($prepared_user, $request) {
    if (is_wp_error($prepared_user)) {
        return $prepared_user;
    }
    $user_id = (int) $request->get_param('id');
    if (isset($prepared_user->user_email) && ko_cf_email_change_is_blocked($user_id, $prepared_user->user_email)) {
        return new WP_Error('ko_email_change_restricted', ko_cf_email_change_message(), array('status' => 403));
    }
    return $prepared_user;
}, PHP_INT_MAX, 2);

// Final persistence guard for wp_update_user()/wp_insert_user(), including
// old confirmation links and updates made without an authenticated actor.
// This filter must return data, not WP_Error: preserve the stored address.
add_filter('wp_pre_insert_user_data', function ($data, $update, $user_id) {
    if ($update && !ko_cf_can_change_account_email()) {
        $existing = get_userdata((int) $user_id);
        if ($existing) {
            $data['user_email'] = $existing->user_email;
        }
    }
    return $data;
}, PHP_INT_MAX, 3);

// wp_update_user decides whether to notify before the persistence filter runs.
add_filter('send_email_change_email', function ($send, $user, $userdata) {
    if (isset($userdata['user_email']) && ko_cf_email_change_is_blocked($user['ID'], wp_unslash($userdata['user_email']))) {
        return false;
    }
    return $send;
}, PHP_INT_MAX, 3);

add_action('admin_init', function () {
    if (ko_cf_can_change_account_email()) {
        return;
    }
    // Core otherwise sends a confirmation email before profile validation.
    // Keep the attempted value available so validation returns a clear error.
    remove_action('personal_options_update', 'send_confirmation_on_profile_email');
});

// Retain the profile-screen cue; server-side hooks above enforce the rule.
add_action('admin_footer', function () {
    global $pagenow;
    if (!in_array($pagenow, array('profile.php', 'user-edit.php'), true) || ko_cf_can_change_account_email()) {
        return;
    }
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var email = document.getElementById('email');
        if (!email) return;
        email.readOnly = true;
        email.setAttribute('aria-describedby', ((email.getAttribute('aria-describedby') || '') + ' ko-email-restriction-help').trim());
        var help = document.createElement('p');
        help.id = 'ko-email-restriction-help';
        help.className = 'description';
        help.textContent = <?php echo wp_json_encode(ko_cf_email_change_message()); ?>;
        email.parentNode.appendChild(help);
    });
    </script>
    <?php
});
/* END KO Functions administrator-only account email changes */

<?php
/* KO Functions email restriction local hook tests */
define('ABSPATH', __DIR__);
$hooks = array();
$actor = (object) array('ID' => 2, 'roles' => array('editor'));
$users = array(2 => (object) array('user_email' => 'editor@example.test'), 3 => (object) array('user_email' => "o'neill@example.test"));
$multisite = false;
$super_admin = false;
function add_filter($tag, $callback, $priority = 10, $args = 1) { $GLOBALS['hooks'][$tag][$priority][] = array($callback, $args); }
function add_action($tag, $callback, $priority = 10, $args = 1) { add_filter($tag, $callback, $priority, $args); }
function remove_action($tag, $callback, $priority = 10) {
    foreach ($GLOBALS['hooks'][$tag][$priority] ?? array() as $i => $entry) {
        if ($entry[0] === $callback) unset($GLOBALS['hooks'][$tag][$priority][$i]);
    }
}
function apply_filters($tag, $value, ...$args) {
    $priorities = $GLOBALS['hooks'][$tag] ?? array(); ksort($priorities);
    foreach ($priorities as $callbacks) foreach ($callbacks as [$callback, $count]) $value = $callback(...array_slice(array_merge(array($value), $args), 0, $count));
    return $value;
}
function do_action($tag, ...$args) {
    $priorities = $GLOBALS['hooks'][$tag] ?? array(); ksort($priorities);
    foreach ($priorities as $callbacks) foreach ($callbacks as [$callback, $count]) $callback(...array_slice($args, 0, $count));
}
function wp_get_current_user() { return $GLOBALS['actor']; }
function get_userdata($id) { return $GLOBALS['users'][$id] ?? false; }
function is_multisite() { return $GLOBALS['multisite']; }
function is_super_admin($id) { return $GLOBALS['super_admin']; }
function wp_unslash($value) { return stripslashes($value); }
function is_wp_error($value) { return $value instanceof WP_Error; }
class WP_Error {
    public $code; public $data; public $messages = array();
    function __construct($code = '', $message = '', $data = null) { if ($code) $this->add($code, $message, $data); }
    function add($code, $message, $data = null) { $this->code = $code; $this->data = $data; $this->messages[] = $message; }
}
class Request { private $id; function __construct($id) { $this->id = $id; } function get_param($name) { return $name === 'id' ? $this->id : null; } }
function send_confirmation_on_profile_email() { $GLOBALS['confirmations']++; }
require __DIR__ . '/../includes/email-restriction.php';
$checks = 0;
function check($condition, $label) { $GLOBALS['checks']++; if (!$condition) throw new RuntimeException('FAIL: ' . $label); }
$changed = array('user_email' => 'new@example.test', 'display_name' => 'New name');
foreach (array('subscriber', 'contributor', 'author', 'editor', 'custom_report_viewer') as $role) {
    $actor->roles = array($role);
    $saved = apply_filters('wp_pre_insert_user_data', $changed, true, 2);
    check($saved['user_email'] === 'editor@example.test', "$role email cannot change");
    check($saved['display_name'] === 'New name', "$role unrelated fields preserved");
    $error = new WP_Error(); do_action('user_profile_update_errors', $error, true, (object) array('ID' => 2, 'user_email' => 'new@example.test'));
    check($error->code === 'ko_email_change_restricted', "$role profile error");
    $rest = apply_filters('rest_pre_insert_user', (object) array('user_email' => 'new@example.test'), new Request(2));
    check(is_wp_error($rest) && $rest->data['status'] === 403, "$role REST denied");
}
check(apply_filters('wp_pre_insert_user_data', $changed, true, 3)['user_email'] === "o'neill@example.test", 'other target and apostrophe preserved');
check(apply_filters('wp_pre_insert_user_data', $changed, false, null) === $changed, 'new account creation unaffected');
$same = (object) array('user_email' => 'editor@example.test');
check(apply_filters('rest_pre_insert_user', $same, new Request(2)) === $same, 'unchanged REST email allowed');
$other = (object) array('display_name' => 'Updated');
check(apply_filters('rest_pre_insert_user', $other, new Request(2)) === $other, 'REST non-email update allowed');
$existing_error = new WP_Error('other', 'Other validation failed');
check(apply_filters('rest_pre_insert_user', $existing_error, new Request(2)) === $existing_error, 'prior error preserved');
check(apply_filters('send_email_change_email', true, array('ID' => 2), $changed) === false, 'blocked change notification suppressed');
$confirmations = 0; add_action('personal_options_update', 'send_confirmation_on_profile_email'); do_action('admin_init'); do_action('personal_options_update', 2);
check($confirmations === 0, 'non-admin confirmation not sent');
$actor->ID = 0; $actor->roles = array();
check(apply_filters('wp_pre_insert_user_data', $changed, true, 2)['user_email'] === 'editor@example.test', 'no-actor background email change blocked');
$actor->ID = 1; $actor->roles = array('administrator');
check(apply_filters('wp_pre_insert_user_data', $changed, true, 2) === $changed, 'administrator can change another account email');
$prepared = (object) $changed;
check(apply_filters('rest_pre_insert_user', $prepared, new Request(2)) === $prepared, 'administrator REST update allowed');
$error = new WP_Error(); do_action('user_profile_update_errors', $error, true, (object) array('ID' => 2, 'user_email' => 'new@example.test'));
check(!$error->messages, 'administrator profile update allowed');
check(apply_filters('send_email_change_email', true, array('ID' => 2), $changed) === true, 'administrator notification preserved');
check(apply_filters('send_email_change_email', false, array('ID' => 2), $changed) === false, 'earlier notification suppression preserved');
add_action('personal_options_update', 'send_confirmation_on_profile_email'); do_action('admin_init'); do_action('personal_options_update', 1);
check($confirmations === 1, 'administrator confirmation flow preserved');
$actor->roles = array(); $multisite = true; $super_admin = true;
check(apply_filters('wp_pre_insert_user_data', $changed, true, 2) === $changed, 'network super administrator permitted');
echo "PASS: $checks local hook assertions\n";
/* END KO Functions email restriction local hook tests */

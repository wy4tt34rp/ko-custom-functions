# KO: Custom Functions (Divi 5)

## 2.9.4 — Administrator-only account email changes

Adds server-side email-change restrictions to the existing 2.9.3 plugin without replacing its other customizations.

Only the acting administrator (or multisite super administrator) can change an existing account email. Non-administrator profile updates return a validation error; REST updates return HTTP 403. A final WordPress persistence filter preserves the stored address for lower-level updates through `wp_update_user()` or `wp_insert_user()`. Those lower-level calls can still save unrelated fields and return success.

Blocked changes do not start the core profile confirmation flow or send misleading email-change notifications. Administrator behavior, new account creation and unrelated fields remain unchanged. Background tasks without an administrator identity cannot change existing emails. Direct SQL writes or another plugin deliberately overriding the hooks are outside this protection.

Deploy `ko-custom-functions.php` and `includes/email-restriction.php` together. Do not activate a second copy of this plugin. Back up the current installation and compare site-specific customizations before replacement.

Validation: `php tests/email-restriction.php` passes 35 local hook-level assertions using synthetic identities and WordPress API stubs. Both runtime PHP files pass syntax checks. Full WordPress integration and live account-change behavior have not been tested. No live deployment has been performed.

WordPress hook contracts:
- https://developer.wordpress.org/reference/hooks/wp_pre_insert_user_data/
- https://developer.wordpress.org/reference/functions/wp_update_user/
- https://developer.wordpress.org/reference/functions/send_confirmation_on_profile_email/
- https://developer.wordpress.org/reference/classes/wp_rest_users_controller/update_current_item/

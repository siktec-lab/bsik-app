<?php

/****************************************************************************/
/* ROLES endpoints:   
 * users.get_role_privileges    => [role, form] get specific role defined privileges with or without form.
 * users.edit_role_privileges   => [role, priv] save priv edits of a role.
 * users.create_new_role        => [role_name, role_desc, role_color, priv] creates a new role.
 * users.save_role_data         => [role, role_name, role_desc, role_color] save role data edits.
 * users.delete_role            => [role] delete a specific role - limited to one.
 ****************************************************************************/
require_once "includes".DS."roles-api.php";

/****************************************************************************/
/* USERS endpoints:   
 * users.create_user_account    => [form data ...] creates a new validated user accounts.
 ****************************************************************************/
require_once "includes".DS."accounts-api.php";

/****************************************************************************/
/* EXTERNAL endpoints: frontend-exposed apis endpoints.
 * users.login    => [form data ...] creates a new validated user accounts.
 ****************************************************************************/
require_once "includes".DS."external-api.php";
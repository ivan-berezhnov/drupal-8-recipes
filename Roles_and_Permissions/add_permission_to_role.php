<?php

// Show list of roles
$roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();

// Get full list of permission and sort by module.
$permissions = \Drupal::service('user.permissions')->getPermissions();
$permissions_by_provider = [];
foreach ($permissions as $key => $permission) {
  $permissions_by_provider[$permission['provider']][] = $key;
}

// Get full list of permission and add to admin role.
use Drupal\user\Entity\Role;

$permissions = \Drupal::service('user.permissions')->getPermissions();
$role_object = Role::load('site_administrator');
foreach ($permissions as $key => $permission) {
  $role_object->grantPermission($key);
}
$role_object->save();

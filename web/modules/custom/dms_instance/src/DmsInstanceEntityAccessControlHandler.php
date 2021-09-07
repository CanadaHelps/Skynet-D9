<?php

namespace Drupal\dms_instance;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the DMS Instance entity.
 *
 * @see \Drupal\dms_instance\Entity\DmsInstanceEntity.
 */
class DmsInstanceEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\dms_instance\Entity\DmsInstanceEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished dms instance entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published dms instance entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit dms instance entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete dms instance entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add dms instance entities');
  }


}

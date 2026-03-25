<?php

declare(strict_types=1);

namespace Drupal\appointment\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;


/*
 * Access controller for the Appointment entity.
 *
 * @see \Drupal\Core\Entity\EntityAccessControlHandler
 */
class AppointmentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) : AccessResult {
    switch ($operation) {
      case 'view':
        if($account->hasPermission('view appointment entities')) {
          return AccessResult::allowed();
        }
        else{
          return AccessResult::forbidden();
        }
      case 'update':
        if ($account->hasPermission('edit appointment entities')) {
          return AccessResult::allowed();
        }
        else{
          return AccessResult::forbidden();
        }

      case 'delete':
        if($account->hasPermission('delete appointment entities')) {
          return AccessResult::allowed();
        }
        else{
          return AccessResult::forbidden();
        }

      case 'edit_booking':
        if ($entity->get('status')->value === 'canceled' || $entity->get('status')->value === 'Canceled') {
          return AccessResult::forbidden('Canceled appointments cannot be edited.')
            ->addCacheableDependency($entity);
        }

        if ($account->hasPermission('edit appointment entities')) {
          return AccessResult::allowed();
        }
        else{
          return AccessResult::forbidden();
        }
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  public function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) : AccessResult
  {
    if($account->hasPermission('add appointment entities')) {
      return AccessResult::allowed();
    }else{
      return AccessResult::forbidden();
    }
  }
}

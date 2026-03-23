<?php

declare(strict_types=1);

namespace Drupal\appointment\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;


/*
 * Access controller for the Agency entity.
 *
 * @see \Drupal\Core\Entity\EntityAccessControlHandler
 */
class AgencyAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) : AccessResult {
    switch ($operation) {
      case 'view':
        if($account->hasPermission('view agency entities') || $account->hasPermission('view appointment entities')) {
          return AccessResult::allowed();
        }
        else{
          return AccessResult::forbidden();
        }
      case 'update':
        if($account->hasPermission('edit agency entities')) {
          return AccessResult::allowed();
        }
        else{
          return AccessResult::forbidden();
        }
      case 'delete':
        if($account->hasPermission('delete agency entities')) {
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
    if($account->hasPermission('add agency entities')) {
      return AccessResult::allowed();
    }else{
      return AccessResult::forbidden();
    }
  }
}

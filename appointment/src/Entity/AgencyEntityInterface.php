<?php

declare(strict_types=1);

namespace Drupal\appointment\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Agency entity entities.
 *
 * @ingroup agency
 */
interface AgencyEntityInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}

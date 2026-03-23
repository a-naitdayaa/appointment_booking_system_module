<?php

declare(strict_types=1);

namespace Drupal\appointment\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;
/**
 * Provides an interface for defining Appointment entity entities.
 *
 * @ingroup appointment
 */
interface AppointmentEntityInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}

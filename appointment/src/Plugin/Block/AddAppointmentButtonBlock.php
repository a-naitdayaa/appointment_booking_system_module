<?php

namespace Drupal\appointment\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides an "Add Appointment" button block.
 */
#[Block(
  id: "add_appointment_button",
  admin_label:new TranslatableMarkup("Add Appointment Button"),
  category: new TranslatableMarkup("Appointment")
)]

class AddAppointmentButtonBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#markup' => '<a href="/appointment/booking-an-appointment" class="button booking-btn booking-btn--primary">Add Appointment</a>',
      '#allowed_tags' => ['a'],
    ];
  }


}



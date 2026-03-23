<?php

namespace Drupal\appointment\Controller;

use Drupal\Core\Controller\ControllerBase;

class AppointmentSettingController extends ControllerBase {

  public function settingsPage(): array {
    return [
      '#type'   => 'markup',
      '#markup' => $this->t('Manage appointment entity settings, fields and display.'),
    ];
  }
}

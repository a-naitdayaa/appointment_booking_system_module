<?php

namespace Drupal\appointment\Controller;

use Drupal\Core\Controller\ControllerBase;

class AgencySettingController extends ControllerBase {

  public function settingsPage(): array {
    return [
      '#type'   => 'markup',
      '#markup' => $this->t('Manage agency entity settings, fields and display.'),
    ];
  }
}

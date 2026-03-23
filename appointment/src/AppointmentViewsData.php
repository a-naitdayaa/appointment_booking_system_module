<?php

declare(strict_types=1);

namespace Drupal\appointment;

use Drupal\views\EntityViewsData;

/**
 * Provides Views integration for Appointment entities.
 */
class AppointmentViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData(): array {
    $data = parent::getViewsData();

    // Add a proper entity reference filter for Agency.
    $data['appointment']['agency'] = [
      'title' => t('Agency'),
      'help' => t('Filter appointments by agency.'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'entity_reference',
        'entity_type' => 'agency_entity',
        'label' => t('Agency'),
      ],
      'argument' => [
        'id' => 'entity_reference',
      ],
    ];

    // Add a proper entity reference filter for Adviser.
    $data['appointment']['adviser'] = [
      'title' => t('Adviser'),
      'help' => t('Filter appointments by adviser.'),
      'field' => [
        'id' => 'standard',
      ],
      'filter' => [
        'id' => 'entity_reference',
        'entity_type' => 'user',
        'label' => t('Adviser'),
      ],
      'argument' => [
        'id' => 'entity_reference',
      ],
    ];

    return $data;
  }

}

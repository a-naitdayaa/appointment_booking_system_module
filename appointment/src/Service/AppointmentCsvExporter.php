<?php

namespace Drupal\appointment\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class AppointmentCsvExporter {

  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /*
   * Export appointments as CSV
   *
   */
  public function exportCsv(array $filters = []): string {
    $storage = $this->entityTypeManager->getStorage('appointment_entity');

    $query = $storage->getQuery()->accessCheck(TRUE);
    foreach ($filters as $field => $value) {
      $query->condition($field, $value);
    }

    $entity_ids = $query->execute();
    $appointments = $storage->loadMultiple($entity_ids);

    // CSV output buffer.
    $buffer = fopen('php://temp', 'r+');

    // Headers: match table columns.
    $headers = [
      'Title',
      'Date',
      'Agency',
      'Adviser',
      'Specialization',
      'Customer Name',
      'Customer Email',
      'Customer Phone',
      'Status',
      'Notes',
    ];

    fputcsv($buffer, $headers);

    foreach ($appointments as $appointment) {
      $customer_info = $appointment->get('customer_info')->first();

      fputcsv($buffer, [
        $appointment->get('title')->value ?? '',
        $appointment->get('date')->value ?? '',
        $appointment->get('agency')->entity ? $appointment->get('agency')->entity->label() : '',
        $appointment->get('adviser')->entity ? $appointment->get('adviser')->entity->getDisplayName() : '',
        $appointment->get('specialization')->entity?->label() ?? '',
        $customer_info?->getName() ?? '',
        $customer_info?->getEmail() ?? '',
        $customer_info?->getPhone() ?? '',
        $appointment->get('status')->value ?? '',
        $appointment->get('notes')->value ?? ''
      ],
        ',', // separator
        '"', // enclosure
        '\\'); // escape
    }

    rewind($buffer);
    $csv = stream_get_contents($buffer);
    fclose($buffer);


    return $csv;
  }

}

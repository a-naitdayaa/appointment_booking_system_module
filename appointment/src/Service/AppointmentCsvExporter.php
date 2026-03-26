<?php

namespace Drupal\appointment\Service;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\File\FileUrlGenerator;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

class AppointmentCsvExporter {
  use StringTranslationTrait;

  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   *
   * Starting & Defining the batch
   *
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   */
  public function startBatchExport(): void {
    $query = $this->entityTypeManager->getStorage('appointment_entity')
                                        ->getQuery()
                                        ->accessCheck(TRUE);

    $ids = array_values($query->execute());

    $chunk_size = 10;

    \Drupal::logger('debug')->notice('Processing chunk size: @c', ['@c' => count($ids)]);

    $batch = new BatchBuilder();

    $batch->setTitle($this->t('Exporting appointments to CSV'))
      ->setFinishCallback([self::class, 'exportFinished'])
      ->setInitMessage($this->t('Starting export...'))
      ->setProgressMessage($this->t('Processing chunk @current of @total...'))
      ->setErrorMessage($this->t('An error occurred during export.'));

      $batch->addOperation([self::class, 'exportCsv'], [$ids]);


    batch_set($batch->toArray());   //registers the batch definition
  }

  /*
   * Batch Process method
   */
  public static function exportCsv(array $ids, array &$context): void {
    $storage = \Drupal::entityTypeManager()
      ->getStorage('appointment_entity');

    if(!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;

      $context['sandbox']['file'] = 'public://appointments.csv';

      $handle = fopen($context['sandbox']['file'], 'w');

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

      fputcsv($handle, $headers);

      fclose($handle);

      $context['sandbox']['progress'] = 0;
      $context['sandbox']['failed'] = 0;
    }

    $handle = fopen($context['sandbox']['file'], 'a');

    $appointments = $storage->loadMultiple($ids);

    foreach ($appointments as $appointment) {

      if(!isset($appointment)) {
        $context['sandbox']['failed']++;
        continue;
      }

      $customer_info = $appointment->get('customer_info')->first();

      fputcsv($handle, [
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

      $context['sandbox']['progress']++;
    }

    fclose($handle);

    $context['message'] = t('Processed @count appointments', [
      '@count' => $context['sandbox']['progress'],
    ]);

  }



  public static function exportFinished($success, $results, $operations) {
    if ($success) {
      $url = \Drupal::service('file_url_generator')
        ->generateAbsoluteString('public://appointments.csv');

      \Drupal::messenger()->addStatus(t(
        'Export completed. <a href=":url">Download CSV</a>',
        [':url' => $url]
      ));
    }
    else {
      \Drupal::messenger()->addError('Export failed.');
    }
  }

}

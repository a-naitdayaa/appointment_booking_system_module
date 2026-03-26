<?php

namespace Drupal\appointment\Controller;

use Drupal\appointment\Service\AppointmentCsvExporter;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Controller for exporting appointments as CSV.
 */
class AppointmentExportController extends ControllerBase {

  private AppointmentCsvExporter $csvExporter;

  public function __construct(AppointmentCsvExporter $csvExporter) {
    $this->csvExporter = $csvExporter;
  }

  public static function create(ContainerInterface $container): static
  {
    return new static(
      $container->get('appointment.csv_exporter')
    );
  }

  public function exportCsv() : RedirectResponse
  {
    $this->csvExporter->startBatchExport();

    return batch_process('admin/appointments');  // redirect url when finished
  }

}

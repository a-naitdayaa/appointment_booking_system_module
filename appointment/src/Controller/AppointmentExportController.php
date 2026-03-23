<?php

namespace Drupal\appointment\Controller;

use Drupal\appointment\Service\AppointmentCsvExporter;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
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

  public function exportCsv(): Response
  {
    $csv = $this->csvExporter->exportCsv();

    $response = new Response($csv);
    $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="appointments.csv"');

    return $response;
  }

}

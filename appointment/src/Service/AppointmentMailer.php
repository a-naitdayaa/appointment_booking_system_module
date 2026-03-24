<?php

namespace Drupal\appointment\Service;

use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

class AppointmentMailer {
  use StringTranslationTrait;

  public function __construct(
    private readonly MailManagerInterface         $mailManager,
    private readonly LanguageManagerInterface     $languageManager,
    private readonly LoggerChannelFactoryInterface $loggerFactory,
  ) {}

  public function sendConfirmation(
    string $to,
    string $customer_name,
    string $date,
    string $hour,
    string $agency,
    string $adviser,
  ): void {
    $langcode = $this->languageManager->getDefaultLanguage()->getId();

    $result = $this->mailManager->mail(
      module:   'appointment',
      key:      'appointment_confirmation',
      to:       $to,
      langcode: $langcode,
      params:   [
        'customer_name' => $customer_name,
        'date'          => $date,
        'hour'          => $hour,
        'agency'        => $agency,
        'adviser'       => $adviser,
      ],
      send: TRUE,
    );

    if (!$result['result']) {
      $this->loggerFactory->get('appointment')->error(
        'Failed to send confirmation email to @email',
        ['@email' => $to]
      );
    }
  }
}

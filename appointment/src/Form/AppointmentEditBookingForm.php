<?php

namespace Drupal\appointment\Form;

use Drupal\appointment\Service\AppointmentMailer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Url;

class AppointmentEditBookingForm extends ContentEntityForm {

  protected AppointmentMailer $mailer;

  public function __construct($entity_repository,
                              $entity_type_bundle_info,
                              $time,
                              AppointmentMailer $mailer)
  {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->mailer = $mailer;
  }

  public static function create(ContainerInterface $container) : static {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('appointment.mailer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array
  {
    $form = parent::buildForm($form, $form_state);

    $appointment = $this->entity;

    if ($appointment->get('status')->value === 'canceled') {
      $this->messenger()->addError($this->t('This appointment is canceled.'));
      $form_state->setRedirectUrl(Url::fromUri('internal:/appointments'));
    }

    unset($form['status']);

    $form['agency']['#disabled'] = TRUE;
    $form['adviser']['#disabled'] = TRUE;
    $form['title']['#disabled'] = TRUE;

    $form['actions']['cancel_appointment'] = [
      '#type' => "submit",
      '#value' => $this->t('Cancel Appointment'),
      '#submit' => ['::cancelAppointment'],
      '#limit_validation_errors' => [],
      '#attributes' => [
        'class' => ['button', 'button--danger'],
        'onclick' => "return confirm('Are you sure you want to cancel this appointment?');",
      ]
    ];


    return $form;
  }

  /**
   * Save edited appointment.
   */
  public function save(array $form, FormStateInterface $form_state) {
    $appointment = $this->entity;
    $appointment->save();

    $customer_info = $appointment->get('customer_info')->first();
    $date_value    = $appointment->get('date')->value;
    $date          = substr($date_value, 0, 10);
    $hour          = substr($date_value, 11, 5);

    $agency  = $this->entityTypeManager
      ->getStorage('agency_entity')
      ->load($appointment->get('agency')->target_id)
      ?->label() ?? '—';

    $adviser = $this->entityTypeManager
      ->getStorage('user')
      ->load($appointment->get('adviser')->target_id)
      ?->getDisplayName() ?? '—';

    $this->mailer->sendModificationConfirmation(
      to:            $customer_info->email ?? '',
      customer_name: $customer_info->name ?? '',
      date:          $date,
      hour:          $hour,
      agency:        $agency,
      adviser:       $adviser,
    );

    $this->messenger()->addStatus($this->t('Appointment updated successfully.'));

    $form_state->setRedirectUrl(Url::fromUri('internal:/appointments'));
  }


  /**
   * @param array $form
   * @param FormStateInterface $form_state
   * @return void
   */
  public function cancelAppointment(array &$form, FormStateInterface $form_state)
  {
    $appointment = $this->entity;
    $appointment->set('status', 'canceled');
    $appointment->save();

    $customer_info = $appointment->get('customer_info')->first();
    $date_value    = $appointment->get('date')->value;
    $date          = substr($date_value, 0, 10);
    $hour          = substr($date_value, 11, 5);

    $agency  = $this->entityTypeManager
      ->getStorage('agency_entity')
      ->load($appointment->get('agency')->target_id)
      ?->label() ?? '—';

    $adviser = $this->entityTypeManager
      ->getStorage('user')
      ->load($appointment->get('adviser')->target_id)
      ?->getDisplayName() ?? '—';

    $this->mailer->sendCancellationConfirmation(
      to:            $customer_info->email ?? '',
      customer_name: $customer_info->name ?? '',
      date:          $date,
      hour:          $hour,
      agency:        $agency,
      adviser:       $adviser,
    );

    $this->messenger()->addStatus($this->t('Appointment has been canceled.'));
    $form_state->setRedirectUrl(Url::fromUri('internal:/appointments'));
  }
}

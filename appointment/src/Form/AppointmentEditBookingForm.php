<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Url;

class AppointmentEditBookingForm extends ContentEntityForm {

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

    $this->messenger()->addStatus($this->t('Appointment has been canceled.'));
    $form_state->setRedirectUrl(Url::fromUri('internal:/appointments'));
  }
}

<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class AppointmentVerifyPhone extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'appointment_verify_phone_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $appointment = \Drupal::routeMatch()->getParameter('appointment_entity');
    $appointment_id = is_object($appointment) ? $appointment->id() : $appointment;

    

    $form['phone'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
    ];

    $form['appointment_id'] = [
      '#type' => 'hidden',
      '#value' => $appointment_id,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Verify'),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $appointment_id = $form_state->getValue('appointment_id');
    $phone = $form_state->getValue('phone');

    $appointment_entity = \Drupal::entityTypeManager()
                              ->getStorage("appointment_entity")
                              ->load($appointment_id);

    if (!$appointment_entity) {
      $this->messenger()->addError($this->t('Unable to find the appointment entity.'));
    }

    $customer_info = $appointment_entity->get('customer_info')->first();
    $appointment_phone = $customer_info?->getPhone();

    if($appointment_phone === $phone) {
      $this->messenger()->addStatus($this->t('Phone number verified successfully. You can now edit your appointment.'));

       $form_state->setRedirect('appointment_entity.edit_booking_form', ['appointment_entity' => $appointment_id]);

    }
    else {
      $this->messenger()->addError($this->t('The phone number you entered does not match our records. Please try again.'));
    }
  }
}

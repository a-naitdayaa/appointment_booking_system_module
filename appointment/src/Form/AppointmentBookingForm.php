<?php

declare(strict_types=1);

namespace Drupal\appointment\Form;


use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\appointment\Service\AppointmentMailer;
use Drupal\Core\Entity\EntityTypeManagerInterface;



class AppointmentBookingForm extends FormBase {

  protected EntityTypeManagerInterface $entityTypeManager;
  protected AppointmentMailer $mailer;

  public function __construct(AppointmentMailer $mailer,
                              EntityTypeManagerInterface $entityTypeManager)
  {
    $this->mailer = $mailer;
    $this->entityTypeManager = $entityTypeManager;
  }

  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('appointment.mailer'),
      $container->get('entity_type.manager'),
    );
  }
  public function getFormId() : string
  {
    return 'appointment_booking_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array
  {

    $form['#attributes']['novalidate'] = 'novalidate';

    $step = $form_state->get('step') ?? 1;
    $form_state->set('step', $step);


    /**
     * Step 1 - selecting an agency
     */
    if($step === 1) {
      $form['step_1'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['booking-step', 'is-active']],
        '#title' => $this->t('Step 1: Select appointment Agency'),
      ];

      $form['step_1']['agency'] = [
        '#type' => 'radios',
        '#title' => $this->t('Select an agency'),
        //'#title_display' => 'invisible',
        '#options' => $this->getAgencies(),
        //'#empty_option'  => $this->t('— Select an agency —'),
        '#attributes' => ['class' => ['booking-agency-select']],
        '#required' => true,
      ];

      $form['step_1']['nav'] = [
        '#type'       => 'container',
        '#attributes' => ['class' => ['booking-nav']],
      ];

      $form['step_1']['nav']['next'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('Continue →'),
        '#submit' => ['::nextStep'],
        '#attributes' => ['class' => ['booking-btn', 'booking-btn--primary']],
      ];
    }

    /**
     * step 2 - selecting agency specialization
     */
    if($step == 2){
      $form['step_2'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['booking-step']],
        '#title' => $this->t('Step 2: Select a specialization'),
      ];

      $form['step_2']['specialization'] = [
        '#type' => 'radios',
        '#title' => $this->t('Select a specialization'),
        //'#title_display' => 'invisible',
        '#options' => $this->getAgencySpecializations($form_state->getValue('agency')),
        //'#empty_option'  => $this->t('— Select a specialization —'),
        '#attributes' => ['class' => ['booking-specialization-select']],
        '#required' => true,
      ];

      $form['step_2']['nav'] = [
        '#type'       => 'container',
        '#attributes' => ['class' => ['booking-nav']],
      ];

      $form['step_2']['nav']['back'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('← Back'),
        '#submit' => ['::prevStep'],
        '#limit_validation_errors' => [],
        '#attributes' => ['class' => ['booking-btn']],
      ];

      $form['step_2']['nav']['next'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('Continue →'),
        '#submit' => ['::nextStep'],
        '#attributes' => ['class' => ['booking-btn', 'booking-btn--primary']],
      ];
    }

    /**
     * step 3 - selecting an adviser
     */
    if($step == 3){

      $form['step_3'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['booking-step', 'is-active']],
        '#title' => $this->t('Step 2: Select an adviser'),
      ];

      \Drupal::logger('appointment')->notice('Step 2: value of agency :', [$form_state->getValue('agency')]);

      $form['step_3']['adviser'] = [
        '#type' => 'radios',
        '#title' => $this->t('Select an adviser'),
        //'#title_display' => 'invisible',
        '#options' => $this->getAdvisers($form_state->get('selected_agency_id')), // old 'agency'
        //'#empty_option'  => $this->t('— Select an adviser —'),
        '#attributes' => ['class' => ['booking-adviser-select']],
        '#required' => true,
      ];

      $form['step_3']['nav'] = [
        '#type'       => 'container',
        '#attributes' => ['class' => ['booking-nav']],
      ];

      $form['step_3']['nav']['back'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('← Back'),
        '#submit' => ['::prevStep'],
        '#limit_validation_errors' => [],
        '#attributes' => ['class' => ['booking-btn']],
      ];


      $form['step_3']['nav']['next'] = [
        '#type'   => 'submit',
        '#value'  => $this->t('Continue →'),
        '#submit' => ['::nextStep'],
        '#attributes' => ['class' => ['booking-btn', 'booking-btn--primary']],
      ];
    }

    /**
     * Step 4 - selecting date and time
     */
    if ($step == 4) {
      $form['step_4'] = [
        '#type'       => 'container',
        '#attributes' => ['class' => ['booking-step', 'is-active']],
        '#attached'   => [
          'library'       => ['appointment/fullcalendar'],
          'drupalSettings' => [
            'appointment' => [
              'adviserId' => $form_state->get('selected_adviser_id'),
              'bookedSlots' => $this->getBookedSlots($form_state->get('selected_adviser_id')),
              'availableTime' => $this->getAdviserAvailableTime($form_state->get('selected_adviser_id')),
              'adviserStartTime' => 9,
            ],
          ],
        ],
      ];

      $form['step_4']['calendar'] = [
        '#type'       => 'container',
        '#attributes' => ['id' => 'booking-calendar'],
      ];

      $form['step_4']['title'] = [
        '#markup' => '<h2 class="booking-step__title">Choose a date & time</h2>',
      ];

      // Hidden fields to store the selected values.
      $form['step_4']['date'] = [
        '#type'     => 'hidden',
        '#default_value' => $form_state->get('appointment_date') ?? '',
        '#attributes' => ['class' => ['booking-date-value']],
      ];

      $form['step_4']['hour'] = [
        '#type'    => 'hidden',
        '#default_value' => $form_state->get('appointment_hour') ?? '',
        '#attributes' => ['class' => ['booking-hour-value']],
      ];

      $form['step_4']['selected_display'] = [
        '#markup' => '<div id="booking-selected-slot" class="booking-selected-slot"></div>',
      ];

      $form['step_4']['nav'] = [
        '#type'       => 'container',
        '#attributes' => ['class' => ['booking-nav']],
      ];

      $form['step_4']['nav']['back'] = [
        '#type'                    => 'submit',
        '#value'                   => $this->t('← Back'),
        '#submit'                  => ['::prevStep'],
        '#limit_validation_errors' => [],
        '#attributes'              => ['class' => ['booking-btn']],
      ];

      $form['step_4']['nav']['next'] = [
        '#type'       => 'submit',
        '#value'      => $this->t('Continue →'),
        '#submit'     => ['::nextStep'],
        '#attributes' => ['class' => ['booking-btn', 'booking-btn--primary']],
      ];
    }


    /**
     * step 5 - customer information
     */
    if($step == 5) {
      $form['step_5'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['booking-step', 'is-active']],
        '#title' => $this->t("Step 3: Enter your information's"),
      ];

      $form['step_5']['customer_info'] = [
        '#type' => 'container',
        '#title' => $this->t('Your Informations'),
        //'#title_display' => 'invisible',
        '#attributes' => ['class' => ['customer-info-field']],
        '#required' => true,
      ];

      $form['step_5']['customer_info']['name'] = [
        '#type'          => 'textfield',
        '#title'         => $this->t('Full name'),
        '#default_value' => $form_state->get('customer_name') ?? '',
        '#required'      => TRUE,
        '#attributes'    => ['class' => ['booking-form__input']],
      ];

      $form['step_5']['customer_info']['email'] = [
        '#type'          => 'email',
        '#title'         => $this->t('Email'),
        '#default_value' => $form_state->get('customer_email') ?? '',
        '#required'      => TRUE,
        '#attributes'    => ['class' => ['booking-form__input']],
      ];

      $form['step_5']['customer_info']['phone'] = [
        '#type'          => 'tel',
        '#title'         => $this->t('Phone'),
        '#default_value' => $form_state->get('customer_phone') ?? '',
        '#required'      => TRUE,
        '#attributes'    => ['class' => ['booking-form__input']],
      ];

      $form['step_5']['nav'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['booking-nav']],
      ];

      $form['step_5']['nav']['back'] = [
        '#type' => 'submit',
        '#value' => $this->t('← Back'),
        '#submit' => ['::prevStep'],
        '#limit_validation_errors' => [],
        '#attributes' => ['class' => ['booking-btn']],
      ];

      $form['step_5']['nav']['next'] = [
        '#type'       => 'submit',
        '#value'      => $this->t('Continue →'),
        '#submit'     => ['::nextStep'],
        '#attributes' => ['class' => ['booking-btn', 'booking-btn--primary']],
      ];
    }

    if ($step == 6) {
      $form['confirmation'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['booking-confirmation']],
      ];

      // --- User Info Section ---
      $form['confirmation']['user_info'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['confirmation-section', 'user-info']],
        '#markup' => '<h3>Personal Information</h3>',
      ];

      $form['confirmation']['user_info']['details'] = [
        '#theme' => 'appointment_user_info_summary',
        '#customer_name'  => $form_state->get('customer_name') ?? '—',
        '#customer_email' => $form_state->get('customer_email') ?? '—',
        '#customer_phone' => $form_state->get('customer_phone') ?? '—',
      ];

      $form['confirmation']['user_info']['modify'] = [
        '#type' => 'submit',
        '#value' => $this->t('Modify Info'),
        '#submit' => ['::goToCustomerInfoStep'],
        '#limit_validation_errors' => [],
        '#attributes' => ['class' => ['booking-btn']],
      ];

      // --- Appointment Info Section ---
      $form['confirmation']['appointment_info'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['confirmation-section', 'appointment-info']],
        '#markup' => '<h3>Appointment Details</h3>',
      ];

      $form['confirmation']['appointment_info']['details'] = [
        '#theme' => 'appointment_appointment_summary',
        '#date'          => $form_state->get('appointment_date') ?? '—',
        '#hour'          => $form_state->get('appointment_hour') ?? '—',
      ];

      $form['confirmation']['appointment_info']['modify'] = [
        '#type' => 'submit',
        '#value' => $this->t('Modify Time'),
        '#submit' => ['::goToAppointmentStep'],
        '#limit_validation_errors' => [],
        '#attributes' => ['class' => ['booking-btn']],
      ];

      // --- Confirm and Cancel ---
      $form['confirmation']['confirm'] = [
        '#type' => 'submit',
        '#value' => $this->t('Confirm Appointment'),
        '#attributes' => ['class' => ['booking-btn', 'booking-btn--primary']],
      ];

      $form['confirmation']['cancel'] = [
        '#type' => 'submit',
        '#value' => $this->t('Cancel Appointment'),
        '#submit' => ['::prevStep'],
        '#limit_validation_errors' => [],
        '#attributes' => ['class' => ['booking-btn']],
      ];
    }


    return $form;

  }



  // ================================================================
  // Runs on every submit. We check the current step and validate
  // only the fields that exist on the current step.
  // ================================================================
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $step        = $form_state->get('step');
    $trigger     = $form_state->getTriggeringElement();
    $is_back_btn = isset($trigger['#submit']) && in_array('::prevStep', $trigger['#submit']);

    if ($is_back_btn) {
      return;
    }

    switch ($step) {

      // ---- Step 1: agency must be selected ----
      case 1:
        if (empty($form_state->getValue('agency'))) {
          $form_state->setErrorByName('agency', $this->t('Please select an agency.'));
        }
        break;

      // ---- Step 2: specialization must be selected ----
      case 2:
        if (empty($form_state->getValue('specialization'))) {
          $form_state->setErrorByName('specialization', $this->t('Please select a specialization.'));
        }
        break;

      // ---- Step 3: adviser must be selected ----
      case 3:
        if (empty($form_state->getValue('adviser'))) {
          $form_state->setErrorByName('adviser', $this->t('Please select an adviser.'));
        }
        break;

      // ---- Step 4: date and time validation ----
      case 4:
        $date = $form_state->getValue('date');
        $hour = $form_state->getValue('hour');

        if (empty($date)) {
          $form_state->setErrorByName('date', $this->t('Please select a date.'));
        }
        elseif ($date < date('Y-m-d')) {
          $form_state->setErrorByName('date', $this->t('The date cannot be in the past.'));
        }

        if (empty($hour)) {
          $form_state->setErrorByName('hour', $this->t('Please select a time slot.'));
        }

        // Double-booking check
        if (!empty($date) && !empty($hour)) {
          $adviser_id  = $form_state->get('selected_adviser_id');
          $datetime    = $date . 'T' . $hour . ':00';

          $existing = Drupal::entityQuery('appointment_entity')
            ->condition('adviser', $adviser_id)
            ->condition('date', $datetime)
            ->condition('status', 'canceled', '!=')
            ->accessCheck(FALSE)
            ->execute();

          if (!empty($existing)) {
            $form_state->setErrorByName('hour', $this->t(
              'This time slot is already booked for the selected adviser. Please choose a different time.'
            ));
          }
        }
        break;

      // ---- Step 5: customer info validation ----
      case 5:
        $name  = $form_state->getValue('name');
        $email = $form_state->getValue('email');
        $phone = $form_state->getValue('phone');

        if (empty(trim($name))) {
          $form_state->setErrorByName('name', $this->t('Full name is required.'));
        }
        elseif (strlen(trim($name)) < 2) {
          $form_state->setErrorByName('name', $this->t('Name must be at least 2 characters.'));
        }

        if (empty($email)) {
          $form_state->setErrorByName('email', $this->t('Email address is required.'));
        }
        elseif (!Drupal::service('email.validator')->isValid($email)) {
          $form_state->setErrorByName('email', $this->t('Please enter a valid email address.'));
        }

        if (!empty($phone) && !preg_match('/^\+?[0-9\s\-()\.]{7,20}$/', $phone)) {
          $form_state->setErrorByName('phone', $this->t('Please enter a valid phone number.'));
        }
        break;
    }
  }

  /**
   * Fetches all agencies and returns an array of options for the select element.
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getAgencies() : array
  {
    $agencies = \Drupal::entityTypeManager()
      ->getStorage('agency_entity')
      ->loadMultiple();

    $options = [];
    foreach ($agencies as $agency) {
      $options[$agency->id()] = $agency->label();
    }

    return $options;
  }


  /**
   * Fetches advisers associated with the selected agency and returns an array of options for the select element.
   * @param int $agencyId
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getAdvisers(mixed $agencyId) : array
  {

    $uids = \Drupal::entityQuery('user')
      ->condition('status', 1)
      ->condition('roles', 'adviser')
      ->condition('agency', $agencyId)
      ->accessCheck(TRUE)
      ->execute();

    if(empty($uids)) {
      return [];
    }


    $users = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadMultiple($uids);

    $options = [];
    foreach ($users as $user) {
      $options[$user->id()] = $user->label();
    }
    return $options;
  }


  /**
   * Fetches specializations associated with the selected agency
   */
  private function getAgencySpecializations(mixed $agencyId) : array{
    $advisers = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->loadByProperties(['roles' => 'adviser', 'status' => 1, 'agency' => $agencyId]);

    $specializations = [];
    foreach($advisers as $adviser) {

      $specializationIds = array_column(
        $adviser->get('specializations')->getValue(),
        'target_id'
      );

      $terms = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadMultiple($specializationIds);

      foreach ($terms as $term) {
        if (!in_array($term->label(), $specializations)) {
          $specializations[$term->id()] = $term->label();
        }
      }
    }

    return $specializations;
  }

  /**
   * Handles the Next button submission
   * @param array $form
   * @param FormStateInterface $form_state
   * @return void
   */
  public function nextStep(array &$form, FormStateInterface $form_state): void {
    $step = $form_state->get('step');

    switch ($step) {
      case 1:
        $form_state->set('selected_agency_id', $form_state->getValue('agency'));
        \Drupal::messenger()->addMessage($this->t('selected agency ID: @id', ['@id' => $form_state->get('selected_agency_id')]));
        break;
      case 2:
          $form_state->set('selected_specialization_id', $form_state->getValue('specialization'));
          \Drupal::messenger()->addMessage('Selected specialization ID: ' . $form_state->get('selected_specialization_id'));
          break;

      case 3:
        $form_state->set('selected_adviser_id', $form_state->getValue('adviser'));
        break;

      case 4:
        $form_state->set('appointment_date', $form_state->getValue('date'));
        $form_state->set('appointment_hour', $form_state->getValue('hour'));
        break;

      case 5:
        $form_state->set('customer_name',     $form_state->getValue('name'));
        $form_state->set('customer_email',    $form_state->getValue('email'));
        $form_state->set('customer_phone',    $form_state->getValue('phone'));
        break;
    }

    $form_state->set('step', $step + 1);
    $form_state->setRebuild(TRUE);
  }


  /**
   * Handles the Previous button submission
   * @param array $form
   * @param FormStateInterface $form_state
   * @return void
   */
  public function prevStep(array &$form, FormStateInterface $form_state): void {
    $step = $form_state->get('step');
    $form_state->set('step', max(1, $step - 1));
    $form_state->setRebuild(TRUE);
  }

  public function goToCustomerInfoStep(array &$form, FormStateInterface $form_state): void {
    $form_state->set('step', 5);
    $form_state->setRebuild(TRUE);
  }

  public function goToAppointmentStep(array &$form, FormStateInterface $form_state): void {
    $form_state->set('step', 4);
    $form_state->setRebuild(TRUE);
  }

  private function getBookedSlots(mixed $adviserId): array {
    if (!$adviserId) return [];

    $ids = \Drupal::entityQuery('appointment_entity')
      ->condition('adviser', $adviserId)
      ->condition('status', 'canceled', '!=')
      ->accessCheck(FALSE)
      ->execute();

    $appointments = \Drupal::entityTypeManager()
      ->getStorage('appointment_entity')
      ->loadMultiple($ids);

    $slots = [];
    foreach ($appointments as $appointment) {
      $slots[] = $appointment->get('date')->value;
    }

    return $slots;
  }

  public function getAdviserAvailableTime(mixed $adviserId) {
    $adviser = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load($adviserId);

    $working_hours = $adviser->get('working_hours')->getValue()[0]['value'] ;

    return $working_hours;
  }


  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $transaction = \Drupal::database()->startTransaction();

    try {
      $date     = $form_state->get('appointment_date');
      $hour     = $form_state->get('appointment_hour');
      $datetime = $date . 'T' . $hour . ':00';

      $appointment = $this->entityTypeManager
        ->getStorage('appointment_entity')
        ->create([
          'title'         => $this->t('Appointment - @date @hour', [
            '@date' => $date,
            '@hour' => $hour,
          ]),
          'date'          => $datetime,
          'agency'        => $form_state->get('selected_agency_id'),
          'adviser'       => $form_state->get('selected_adviser_id'),
          'specialization' => $form_state->get('selected_specialization_id'),
          'customer_info' => [
            'name'  => $form_state->get('customer_name'),
            'email' => $form_state->get('customer_email'),
            'phone' => $form_state->get('customer_phone'),
          ],
          'status'        => 'pending',
        ]);

      //dump($form_state->get('customer_name'));

      $appointment->save();

      $this->mailer->sendConfirmation(
        to:            $form_state->get('customer_email'),
        customer_name: $form_state->get('customer_name'),
        date:          $form_state->get('appointment_date'),
        hour:          $form_state->get('appointment_hour'),
        agency:        $this->entityTypeManager
                          ->getStorage('agency_entity')
                          ->load($form_state->get('selected_agency_id'))
                          ?->label() ?? '—',
        adviser:       $this->entityTypeManager
                          ->getStorage('user')
                          ->load($form_state->get('selected_adviser_id'))
                          ?->getDisplayName() ?? '—',
      );

      $this->messenger()->addStatus($this->t(
        'Your appointment has been booked for @date at @hour.',
        ['@date' => $date, '@hour' => $hour]
      ));

      $form_state->setRedirectUrl(\Drupal\Core\Url::fromUri('internal:/appointments'));

    } catch (\Exception $e) {
      $transaction->rollBack();

      \Drupal::logger('appointment')->error('Appointment creation failed: @message', [
        '@message' => $e->getMessage(),
      ]);

      $this->messenger()->addError($this->t(
        'Something went wrong while booking your appointment. Please try again.'
      ));
    }
  }

  public function cancelForm(array &$form, FormStateInterface $form_state): void {
    $form_state->set('step', 1);
    $form_state->setRebuild(TRUE);
  }
}

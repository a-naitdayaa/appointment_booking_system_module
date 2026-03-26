<?php

declare(strict_types=1);

namespace Drupal\appointment\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

#[FieldWidget(
  id: "customer_info_field_widget",
  label: new TranslatableMarkup("Customer Info Field Widget"),
  field_types: ["customer_info_field"]
)]
class CustomerInfoFieldWidget extends WidgetBase {

  public function formElement(FieldItemListInterface $items,   // all values for this field
                                                     $delta,                          // index of the current value
                              array                  $element,                  // base element
                              array                  &$form,                    // the full parent form
                              FormStateInterface     $form_state   // current form state
  ) : array
  {

    $element['#title'] = $this->t('Customer Info');
    $element['#title_display'] = 'before';

    $element['customer_info'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['customer-info-field']],
      '#element_validate' => '::validateCustomerInfo',
    ];

    $element['customer_info']['name'] = [
      '#type'          => 'textfield',
      '#title'         => t('Name'),
      '#default_value' => $items[$delta]->name ?? '',
      '#required'      => FALSE,
    ];

    $element['customer_info']['email'] = [
      '#type'          => 'email',
      '#title'         => t('Email'),
      '#default_value' => $items[$delta]->email ?? '',
      '#required'      => FALSE,
    ];

    $element['customer_info']['phone'] = [
      '#type'          => 'tel',
      '#title'         => t('Phone'),
      '#default_value' => $items[$delta]->phone ?? '',
      '#required'      => FALSE,
    ];

    $element['#attached']['library'] = ['appointment/customer_info_field_widget'];

    return $element;
  }

  /**
   * Validate customer info fields.
   */
  public function validateCustomerInfo(array $element, FormStateInterface $form_state): void
  {
    $values = $form_state->getValue($element['#parents']);
    $name   = $values['customer_info']['name'] ?? NULL;
    $email  = $values['customer_info']['email'] ?? NULL;

    if (empty($name)) {
      $form_state->setError($element['customer_info']['name'], $this->t('Name is required.'));
    }

    if (empty($email)) {
      $form_state->setError($element['customer_info']['email'], $this->t('Email is required.'));
    }
  }

  /**
   * @param array $values
   * @param array $form
   * @param FormStateInterface $form_state
   * @return array
   *
   * This method is used to massage the form values before they are saved to the database.
   * It takes the nested 'customer_info' array and flattens it into individual fields for name, email, and phone.
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state): array
  {
    foreach ($values as &$value) {
      $value['name']  = $value['customer_info']['name'] ?? NULL;
      $value['email'] = $value['customer_info']['email'] ?? NULL;
      $value['phone'] = $value['customer_info']['phone'] ?? NULL;
      unset($value['customer_info']);
    }
    return $values;
  }
}

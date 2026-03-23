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

    $element['customer_info'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['customer-info-field']],
    ];

    $element['customer_info']['name'] = [
      '#type'          => 'textfield',
      '#title'         => t('Name'),
      '#default_value' => $items[$delta]->name ?? '',
      '#required'      => TRUE,
    ];

    $element['customer_info']['email'] = [
      '#type'          => 'email',
      '#title'         => t('Email'),
      '#default_value' => $items[$delta]->email ?? '',
      '#required'      => TRUE,
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
}

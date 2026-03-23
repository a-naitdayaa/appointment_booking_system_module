<?php

declare(strict_types=1);

namespace Drupal\appointment\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;


#[FieldFormatter(
  id: "customer_info_field_formatter",
  label: new TranslatableMarkup("Customer Info Field Formatter"),
  field_types: ["customer_info_field"]
)]
class CustomerInfoFieldFormatter extends FormatterBase {

  public function viewElements(FieldItemListInterface $items, $langcode) : array
  {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = [
      '#theme' => 'customer_info',
      '#name' => $item->name,
      '#email' => $item->email,
      '#phone' => $item->phone
      ];
    }
    return $elements;
  }
}

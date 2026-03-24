<?php

declare(strict_types=1);

namespace Drupal\appointment\Plugin\Field\FieldType;

use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\Exception\MissingDataException;


#[FieldType(
  id: "customer_info_field",
  label: new TranslatableMarkup("Customer Info"),
  description: new TranslatableMarkup("A field type for storing customer information."),
  default_widget: "customer_info_field_widget",
  default_formatter: "customer_info_field_formatter"
)]
class CustomerInfoField extends FieldItemBase implements FieldItemInterface{

  /**
   * Defines fields properties.
   *
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array
  {
    $properties['name'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setRequired(TRUE);

    $properties['email'] = DataDefinition::create('email')
      ->setLabel(new TranslatableMarkup('Email'))
      ->setRequired(TRUE);

    $properties['phone'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Phone'))
      ->setRequired(TRUE)
      ->addConstraint('Regex', [
        'pattern' => '/^\+?[0-9\s\-()]+$/',
        'message' => 'Invalid phone number format.',
      ]);


    return $properties;
  }

  /**
   * Defines how the field data is stored in the database.
   *
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) : array
  {
    return [
      'columns' => [
        'name' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'email' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'phone' => [
          'type' => 'varchar',
          'length' => 20,
        ],
      ],
    ];
  }

  /**
   * @throws MissingDataException
   */
  public function isEmpty() : bool
  {
    $name = $this->get('name')->getValue();
    $email = $this->get('email')->getValue();
    $phone = $this->get('phone')->getValue();

    return empty($name) && empty($email) && empty($phone);
  }

  // Getters

  public function getName(): string {
    return $this->get('name')->getValue() ?? '';
  }

  public function getEmail(): string {
    return $this->get('email')->getValue() ?? '';
  }

  public function getPhone(): string {
    return $this->get('phone')->getValue() ?? '';
  }
}

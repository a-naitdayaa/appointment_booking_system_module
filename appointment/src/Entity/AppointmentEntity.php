<?php

declare(strict_types=1);

namespace Drupal\appointment\Entity;

use Drupal\appointment\Access\AppointmentAccessControlHandler;
use Drupal\appointment\AppointmentListBuilder;
use Drupal\content_translation\ContentTranslationHandler;
use Drupal\Core\Entity\Attribute\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\Exception\UnsupportedEntityTypeDefinitionException;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\EntityOwnerTrait;
use Drupal\views\EntityViewsData;

#[ContentEntityType(
  id: 'appointment_entity',
  label: new TranslatableMarkup('Appointment entity'),
  label_collection: new TranslatableMarkup('Appointment entities'),
  entity_keys: [
    'id' => 'id',
    'label' => 'title',
    'uuid' => 'uuid',
    'owner' => 'uid',
    'langcode' => 'langcode',
  ],
  handlers: [
    'access' => AppointmentAccessControlHandler::class,
    'list_builder' => AppointmentListBuilder::class,
    'view_builder' => EntityViewBuilder::class,
    'views_data' => EntityViewsData::class,
    'translation' => ContentTranslationHandler::class,
    'route_provider' => [
      'html' => 'Drupal\Core\Entity\Routing\AdminHtmlRouteProvider',
    ],
    'form' => [
      'default' => ContentEntityForm::class,
      'delete'  => ContentEntityDeleteForm::class,
    ],
  ],
  links: [
    'canonical' => "/appointment/{appointment_entity}",
    'add-form' => "/appointment/add",
    'edit-form' => "/appointment/{appointment_entity}/edit",
    'delete-form' => "/appointment/{appointment_entity}/delete",
    'collection' => "/admin/structure/appointments",
  ],
  admin_permission: 'administer appointment entities',
  base_table: 'appointment',
  data_table: 'appointment_entity_data',
  translatable: true,
  field_ui_base_route: 'appointment_entity.settings',
)]
class AppointmentEntity extends ContentEntityBase implements AppointmentEntityInterface {
  use EntityChangedTrait;
  use EntityOwnerTrait;


  /**
   * {@inheritdoc}
   *
   * When a new entity instance is added, set the user_id entity reference to
   * the current user as the creator of the instance.
   */
  public function preSave(EntityStorageInterface $storage): void
  {
    parent::preSave($storage);
    if(!$this->getOwnerId()){
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }


  /**
   * {@inheritdoc}
   * @throws UnsupportedEntityTypeDefinitionException
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) : array
  {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Adding any default base field definitions from EntityOwnerTrait.
    $fields += static::ownerBaseFieldDefinitions($entity_type);


    //============================================================
    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Title'))
      ->setRequired(true)
      ->setDescription("Appointment title")
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);


    //============================================================
    $fields['date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(new TranslatableMarkup('Date'))
      ->setRequired(true)
      ->setDescription("Appointment date")
      ->setSettings([
        'datetime_type' => 'datetime',
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);


    //============================================================
    $fields['agency'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Agency'))
      ->setRequired(true)
      ->setDescription("Agency name")
      ->setSettings([
        'target_type' => 'agency_entity',
        'handler' => 'default',
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 15,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);

    //============================================================
    $fields['adviser'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Adviser'))
      ->setRequired(true)
      ->setDescription("The adviser assigned to this appointment.")
      ->setSettings([
        'target_type' => 'user',
        'handler' => 'default',
      ])
      ->setSetting('handler_settings', [
        'filter' => [
          'type' => 'role',
          'role' => ['adviser' => 'adviser'],
        ],
        'include_anonymous' => FALSE,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 25,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 25,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);


    //===========================================================
    $fields['specialization'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('Specialization'))
      ->setRequired(true)
      ->setDescription("The specialization this appointment is for.")
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', [
        'target_bundles' => ['specializations' => 'appointment_type'],
      ])
      ->setDisplayOptions('form', [
        'type'   => 'entity_reference_autocomplete',
        'weight' => 12,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type'  => 'entity_reference_label',
        'weight' => 12,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);


    //============================================================
    $fields['customer_info'] = BaseFieldDefinition::create('customer_info_field')
      ->setLabel(new TranslatableMarkup('Customer Info'))
      ->setRequired(true)
      ->setDescription("Customer info field")
      ->setDisplayOptions('form', [
        'type' => 'customer_info_field_widget',
        'weight' => 20,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'customer_info_field_formatter',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);

    //============================================================
    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(new TranslatableMarkup('Status'))
      ->setRequired(true)
      ->setDescription("Appointment status")
      ->setDefaultValue("pending")
      ->setSettings([
        'allowed_values' => [
          'pending' => 'Pending',
          'confirmed' => 'Confirmed',
          'canceled' => 'Canceled',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'options_select',
        'weight' => 10,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'list_default',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);

    //============================================================
    $fields['notes'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Notes'))
      ->setRequired(false)
      ->setDescription("Additional notes for the appointment")
      ->setSettings([
        'text_processing' => 0,
      ])
      ->setDefaultValue(NULL)
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
        'weight' => 5,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(new TranslatableMarkup('Changed'))
      ->setDescription(new TranslatableMarkup('The time the agency was last edited.'));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Created'))
      ->setDescription(new TranslatableMarkup('The time the agency was created.'));

    return $fields;
  }
}

<?php

declare(strict_types=1);

namespace Drupal\appointment\Entity;

use Drupal\appointment\Access\AgencyAccessControlHandler;
use Drupal\appointment\AgencyListBuilder;
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
  id: 'agency_entity',
  label: new TranslatableMarkup('Agency entity'),
  label_collection: new TranslatableMarkup('Agency entities'),
  entity_keys: [
    'id' => 'id',
    'label' => 'name',
    'uuid' => 'uuid',
    'owner' => 'uid',
    'langcode' => 'langcode',
  ],
  handlers: [
    'access' => AgencyAccessControlHandler::class,
    'list_builder' => AgencyListBuilder::class,
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
    'canonical' => "/agency/{agency_entity}",
    'add-form' => "/agency/add",
    'edit-form' => "/agency/{agency_entity}/edit",
    'delete-form' => "/agency/{agency_entity}/delete",
    'collection' => "/admin/structure/agencies",
  ],
  admin_permission: 'administer agency entities',
  base_table: 'agency',
  data_table: 'agency_entity_data',
  translatable: true,
  field_ui_base_route: 'agency_entity.settings',
)]
class AgencyEntity extends ContentEntityBase implements AgencyEntityInterface {
  use EntityChangedTrait;
  use EntityOwnerTrait;

  public function preSave(EntityStorageInterface $storage): void
  {
    parent::preSave($storage);
    if(!$this->getOwnerId()){
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }


  /**
   * @throws UnsupportedEntityTypeDefinitionException
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) : array {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Name'))
      ->setDescription("The name of the agency.")
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);

    $fields['address'] = BaseFieldDefinition::create('string')
      ->setRequired(TRUE)
      ->setDescription("The address of the agency.")
      ->setLabel(new TranslatableMarkup('Address'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -3,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -3,
      ])
      ->setDisplayConfigurable('view', true)
      ->setDisplayConfigurable('form', true);

    $fields['operating_hours'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Operating Hours'))
      ->setDescription("The operating hours of the agency.")
      ->setRequired(TRUE)
      ->setSettings([
        'min' => 0,
        'max' => 24,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => -2,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('form', true)
      ->setDisplayConfigurable('view', true);

    $fields['contact_info'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Contact Information'))
      ->setDescription("The contact information of the agency.")
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -1,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -1,
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

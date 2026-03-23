<?php

declare(strict_types=1);

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for appointment_entity entity.
 *
 * @ingroup appointment
 */
class AgencyListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type)
  {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id())
    );
  }

  /**
   * Constructs a new AppointmentListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage){
    parent::__construct($entity_type, $storage);
  }


  /**
   * {@inheritdoc}
   */
  public function render(): array
  {
    $build = parent::render();

    $build['add'] = [
      '#type' => 'link',
      '#title' => $this->t('+ Add Agency'),
      '#url' => \Drupal\Core\Url::fromRoute('entity.agency_entity.add_form'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
      '#weight' => -10,
    ];

    $build['table']['#empty'] = $this->t('No agencies found.');

    if (isset($build['table'])) {
      $build['table']['#attributes']['class'][] = 'table';
      $build['table']['#attributes']['class'][] = 'table-striped';
      $build['table']['#attributes']['class'][] = 'table-bordered';
    }

    return $build;
  }



  /**
   * {@inheritdoc}
   */
  public function buildHeader() : array {
    $header['name'] = $this->t('Name');
    $header['address'] = $this->t('Adresse');
    $header['operating_hours'] = $this->t('Operating Hours');
    $header['contact_info'] = $this->t('Contact Info');

    return $header + parent::buildHeader();
  }


  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) : array {
    $row['name'] = $entity->label();
    $row['address'] = $entity->get('address')->value;
    $row['operating_hours'] = $entity->get('operating_hours')->value;
    $row['contact_info'] = $entity->get('contact_info')->value;


    return $row + parent::buildRow($entity);
  }
}

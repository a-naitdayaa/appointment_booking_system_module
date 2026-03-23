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
class AppointmentListBuilder extends EntityListBuilder {

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
      '#title' => $this->t('+ Add Appointment'),
      '#url' => \Drupal\Core\Url::fromRoute('entity.appointment_entity.add_form'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
      '#weight' => -10,
    ];

    $build['table']['#empty'] = $this->t('No appointments found.');

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
   $header['title'] = $this->t('Title');
   $header['date'] = $this->t('Date');
   $header['agency'] = $this->t('Agency');
   //$header['status'] = $this->t('Status');
   $header['adviser'] = $this->t('Adviser');
   //$header['customer_info'] = $this->t('Customer info');
   //$header['notes'] = $this->t('Notes');
    $header['appointment_type'] = $this->t('Appointment type');
    //$header['actions'] = $this->t('Actions');

   return $header + parent::buildHeader();
  }


  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) : array {
    $row['title'] = $entity->label();
    $row['date'] = $entity->get('date')->value
      ? date('Y-m-d H:i', strtotime($entity->get('date')->value))
      : '-';

    $row['agency'] = $entity->get('agency')->entity?->label() ?? '-';

    $row['adviser'] = $entity->get('adviser')->entity->label() ?? '-';

    $row['appointment_type'] = $entity->get('adviser')->entity
      ?->get('specializations')->entity
      ?->label() ?? '-';



    return $row + parent::buildRow($entity);
  }
}

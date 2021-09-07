<?php

namespace Drupal\dms_instance;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of DMS Instance entities.
 *
 * @ingroup dms_instance
 */
class DmsInstanceEntityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('DMS Instance ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\dms_instance\Entity\DmsInstanceEntity $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.dms_instance.edit_form',
      ['dms_instance' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}

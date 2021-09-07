<?php

namespace Drupal\dms_instance;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\dms_instance\Entity\DmsInstanceEntityInterface;

/**
 * Defines the storage handler class for DMS Instance entities.
 *
 * This extends the base storage class, adding required special handling for
 * DMS Instance entities.
 *
 * @ingroup dms_instance
 */
class DmsInstanceEntityStorage extends SqlContentEntityStorage implements DmsInstanceEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(DmsInstanceEntityInterface $entity) {
    return $this->database->query(
      'SELECT revision_id FROM {dms_instance_revision} WHERE id=:id ORDER BY revision_id',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT revision_id FROM {dms_instance_field_data_revision} WHERE uid = :uid ORDER BY revision_id',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(DmsInstanceEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {dms_instance_field_data_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('dms_instance_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}

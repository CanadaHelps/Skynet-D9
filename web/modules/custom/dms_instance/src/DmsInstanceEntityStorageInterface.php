<?php

namespace Drupal\dms_instance;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface DmsInstanceEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of DMS Instance revision IDs for a specific DMS Instance.
   *
   * @param \Drupal\dms_instance\Entity\DmsInstanceEntityInterface $entity
   *   The DMS Instance entity.
   *
   * @return int[]
   *   DMS Instance revision IDs (in ascending order).
   */
  public function revisionIds(DmsInstanceEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as DMS Instance author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   DMS Instance revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\dms_instance\Entity\DmsInstanceEntityInterface $entity
   *   The DMS Instance entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(DmsInstanceEntityInterface $entity);

  /**
   * Unsets the language for all DMS Instance with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}

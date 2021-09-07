<?php

namespace Drupal\dms_instance\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining DMS Instance entities.
 *
 * @ingroup dms_instance
 */
interface DmsInstanceEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the DMS Instance name.
   *
   * @return string
   *   Name of the DMS Instance.
   */
  public function getName();

  /**
   * Sets the DMS Instance name.
   *
   * @param string $name
   *   The DMS Instance name.
   *
   * @return \Drupal\dms_instance\Entity\DmsInstanceEntityInterface
   *   The called DMS Instance entity.
   */
  public function setName($name);

  /**
   * Gets the DMS Instance creation timestamp.
   *
   * @return int
   *   Creation timestamp of the DMS Instance.
   */
  public function getCreatedTime();

  /**
   * Sets the DMS Instance creation timestamp.
   *
   * @param int $timestamp
   *   The DMS Instance creation timestamp.
   *
   * @return \Drupal\dms_instance\Entity\DmsInstanceEntityInterface
   *   The called DMS Instance entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the DMS Instance revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the DMS Instance revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\dms_instance\Entity\DmsInstanceEntityInterface
   *   The called DMS Instance entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the DMS Instance revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the DMS Instance revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\dms_instance\Entity\DmsInstanceEntityInterface
   *   The called DMS Instance entity.
   */
  public function setRevisionUserId($uid);

  public function getAegirInstance();

}

<?php

namespace Drupal\dms_instance\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a DMS Instance revision.
 *
 * @ingroup dms_instance
 */
class DmsInstanceEntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The DMS Instance revision.
   *
   * @var \Drupal\dms_instance\Entity\DmsInstanceEntityInterface
   */
  protected $revision;

  /**
   * The DMS Instance storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $dmsInstanceEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dmsInstanceEntityStorage = $container->get('entity_type.manager')->getStorage('dms_instance');
    $instance->connection = $container->get('database');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dms_instance_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => format_date($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.dms_instance.version_history', ['dms_instance' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $dms_instance_revision = NULL) {
    $this->revision = $this->DmsInstanceEntityStorage->loadRevision($dms_instance_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->DmsInstanceEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('DMS Instance: deleted %title revision %revision.', ['%title' => $this->revision->label(), '%revision' => $this->revision->getRevisionId()]);
    $this->messenger()->addMessage(t('Revision from %revision-date of DMS Instance %title has been deleted.', ['%revision-date' => format_date($this->revision->getRevisionCreationTime()), '%title' => $this->revision->label()]));
    $form_state->setRedirect(
      'entity.dms_instance.canonical',
       ['dms_instance' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT revision_id) FROM {dms_instance_field_data_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.dms_instance.version_history',
         ['dms_instance' => $this->revision->id()]
      );
    }
  }

}

<?php

namespace Drupal\dms_instance\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\dms_instance\Entity\DmsInstanceEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;

/**
 * Class DmsInstanceEntityController.
 *
 *  Returns responses for DMS Instance routes.
 */
class DmsInstanceEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a DMS Instance revision.
   *
   * @param int $dms_instance_revision
   *   The DMS Instance revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($dms_instance_revision) {
    $dms_instance = $this->entityTypeManager()->getStorage('dms_instance')
      ->loadRevision($dms_instance_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('dms_instance');

    return $view_builder->view($dms_instance);
  }

  /**
   * Page title callback for a DMS Instance revision.
   *
   * @param int $dms_instance_revision
   *   The DMS Instance revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($dms_instance_revision) {
    $dms_instance = $this->entityTypeManager()->getStorage('dms_instance')
      ->loadRevision($dms_instance_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $dms_instance->label(),
      '%date' => $this->dateFormatter->format($dms_instance->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a DMS Instance.
   *
   * @param \Drupal\dms_instance\Entity\DmsInstanceEntityInterface $dms_instance
   *   A DMS Instance object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(DmsInstanceEntityInterface $dms_instance) {
    $account = $this->currentUser();
    $dms_instance_storage = $this->entityTypeManager()->getStorage('dms_instance');

    $langcode = $dms_instance->language()->getId();
    $langname = $dms_instance->language()->getName();
    $languages = $dms_instance->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $dms_instance->label()]) : $this->t('Revisions for %title', ['%title' => $dms_instance->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all dms instance revisions") || $account->hasPermission('administer dms instance entities')));
    $delete_permission = (($account->hasPermission("delete all dms instance revisions") || $account->hasPermission('administer dms instance entities')));

    $rows = [];

    $vids = $dms_instance_storage->revisionIds($dms_instance);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\dms_instance\DmsInstanceEntityInterface $revision */
      $revision = $dms_instance_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $dms_instance->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, Url::fromRoute('entity.dms_instance.revision', [
            'dms_instance' => $dms_instance->id(),
            'dms_instance_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $dms_instance->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.dms_instance.translation_revert', [
                'dms_instance' => $dms_instance->id(),
                'dms_instance_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.dms_instance.revision_revert', [
                'dms_instance' => $dms_instance->id(),
                'dms_instance_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.dms_instance.revision_delete', [
                'dms_instance' => $dms_instance->id(),
                'dms_instance_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['dms_instance_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}

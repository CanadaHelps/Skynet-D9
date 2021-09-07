<?php

namespace Drupal\dms_instance\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;

class DmsInstanceStatusController extends ControllerBase {


  /**
   * Guzzle\Client instance.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  public function renderStatuses() {
    $config = \Drupal::config('dms_instance.settings');
    $instance_statues = $config->get('instance_statuses');
    $instance_statues_saved = explode("\n", $instance_statues);
    $statuses = [];
    foreach ($instance_statues_saved as $instanceStatus) {
      $status = explode(' | ', $instanceStatus);
      $statuses[$status[0]] = trim($status[1]);
    }
    return new JsonResponse([
      'data' => $statuses,
      'method' => 'GET',
    ]);
  }

}

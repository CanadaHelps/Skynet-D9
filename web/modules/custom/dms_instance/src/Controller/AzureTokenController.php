<?php

namespace Drupal\dms_instance\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;

class AzureTokenController extends ControllerBase {


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
    $module_handler = \Drupal::service('module_handler');
    $module_path = $module_handler->getModule('dms_instance')->getPath();
    require_once($module_path . DIRECTORY_SEPARATOR . 'dms_instance_secrets.inc');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  public function renderToken() {
    try {
      $request = $this->httpClient->post('https://login.microsoftonline.com/' . DMS_AZURE_APPLICATION_ID . '/oauth2/token?api-version=1.0', ['multipart' => [
        ['name' => 'grant_type', 'contents' => 'client_credentials'], 
        ['name' => 'client_id', 'contents' => DMS_AZURE_CLIENT_ID],
        ['name' => 'resource', 'contents' => 'https://management.core.windows.net/'],
        ['name' => 'client_secret', 'contents' => DMS_AZURE_CLIENT_SECRET],
      ]]);
      if ($request->getStatusCode() !== 200) {
        $result = [];
      }
      else {
        $result = json_decode($request->getBody()->getContents(), TRUE);
      }
    }
    catch (Exception $e) {
      $result = $e->getMessage();
    }
    return new JsonResponse([
      'data' => $result,
      'method' => 'GET',
    ]);
  }

}

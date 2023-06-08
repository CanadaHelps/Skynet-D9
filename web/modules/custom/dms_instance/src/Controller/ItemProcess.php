<?php

namespace Drupal\dms_instance\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Database\Database;
use Drupal\dms_instance\Queue\DMSInstanceQueue;
use Drupal\Core\Controller\ControllerBase;

class ItemProcess extends ControllerBase {


  public function processJobReturn() {
    $item_id = \Drupal::request()->query->get('job_id');
    $aegir_instance = \Drupal::request()->query->get('aegir_instance');
    $dms_instance_id = \Drupal::request()->query->get('dms_instance_id');
    $dms_instance = \Drupal::entityTypeManager()->getStorage('dms_instance')->loadByProperties(['uuid' => $dms_instance_id]);
    reset($dms_instance);
    $dms_instance = array_values($dms_instance)[0];
    $civicrm_site_key = \Drupal::request()->query->get('site_key');
    $dms_instance->civicrm_site_key = $civicrm_site_key;
    $previous_status = $dms_instance->get('instance_status')->getString();
    /**
     * These are the stauses not to be changed
     * 18 - CH Data Sync Completed
     * 19 - Config Pushed to Instance
     * 27 - CH Data Sync Started
    **/
    $ignored_status = [18, 19, 27];
    if(!in_array($previous_status, $ignored_status)) {
      $dms_instance->instance_status = 17;
      $dms_instance->setNewRevision();
      $dms_instance->save();
    }
    $queue = new DMSInstanceQueue($aegir_instance, Database::getConnection());
    $item = new \stdClass();
    $item->item_id = $item_id;
    $queue->deleteItem($item);

    $httpClient = \Drupal::httpClient();
    $ch_end_point = \Drupal::service('key.repository')->getKey('ch_end_point')->getKeyValue();

    $body = [
      'BusinessNumber' => $dms_instance->get('business_registration_number')->getString(),
      'APIHostURL' => 'https://'.$dms_instance->get('instance_prefix')->getString()."-dms.canadahelps.org",
      'RedirectURL' => 'https://'.$dms_instance->get('instance_prefix')->getString()."-dms.canadahelps.org",
      'Key' => $civicrm_site_key,
      'APIKey' => 'vqkn5KNPs1mJSQZVNZptswH1YBEPujh3',
      'InitialLoadDays' => $dms_instance->get('sync_days')->getString(),
    ];
    //check for empty fields
    foreach($body as $bodyParams) {
      if(empty($bodyParams) || $bodyParams == NULL) {
        return new JsonResponse([
          'data' => ['item deleted ' . $item_id],
          'method' => 'GET',
        ]);
      }
    }
    //check for previous status
    if(in_array($previous_status, $ignored_status)) {
      return new JsonResponse([
        'data' => ['item deleted ' . $item_id],
        'method' => 'GET',
      ]);
    }
    try {
      $response = $httpClient->post(
        $ch_end_point, [
        'body' => json_encode($body),
        'headers' => [
          'Accept' => 'application/json',
          'Content-Type' => 'application/json'
        ]
      ]);
      $ch_response = $response->getBody()->getContents();
      if($ch_response === 'false') {
        // ID 26: CH-Data Sync failed. We would get boolean false from the server
        $dms_instance->instance_status = 26;
        $dms_instance->setNewRevision();
        $dms_instance->save();
      }
      if($ch_response === 'true') {
        // ID 27: CH-Data Sync Started. We would get boolean true from the server
        $dms_instance->instance_status = 27;
        $dms_instance->setNewRevision();
        $dms_instance->save();
      }
    }
    catch (\GuzzleHttp\Exception\BadResponseException $e) {
      // ID 26: CH-Data Sync failed. We would get Internal Error 500 from the server
      $dms_instance->instance_status = 26;
      $dms_instance->setNewRevision();
      $dms_instance->save();
    }

    return new JsonResponse([
      'data' => ['item deleted ' . $item_id],
      'method' => 'GET',
    ]);
  }

}

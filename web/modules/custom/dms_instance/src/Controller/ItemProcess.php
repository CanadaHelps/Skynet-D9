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
    $dms_instance->instance_status = 17;
    $dms_instance->setNewRevision();
    $dms_instance->save();
    $queue = new DMSInstanceQueue($aegir_instance, Database::getConnection());
    $item = new \stdClass();
    $item->item_id = $item_id;
    $queue->deleteItem($item);

    $httpClient = \Drupal::httpClient();
    $httpClient->post(
      'https://beta.canadahelps.com/site/api/dms/resgister-dms',
      [
        'BusinessNumber' => $dms_instance->business_registration_number,
        'APIHostURL' => 'https://'.$dms_instance->instance_prefix.".canadahelps.org",
        'RedirectURL' => 'https://'.$dms_instance->instance_prefix.".canadahelps.org",
        'APIKey' => $dms_instance->civicrm_api_key,
        'Key' => $dms_instance->civicrm_site_key,
        'InitialLoadDays' => $dms_instance->sync_days,
      ]
    );
    
    return new JsonResponse([
      'data' => ['item deleted ' . $item_id],
      'method' => 'GET',
    ]);
  }

}

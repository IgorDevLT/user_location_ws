<?php

namespace Drupal\user_location_ws;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\user_location_ws\Form\UserLocationWsSettingsForm;
use Drupal\user_location_ws\Exception\MunicipalityProviderException;
use Drupal\user_location_ws\Exception\MunicipalityProviderExceptionMissingKey;

class UserLocationWsClient {

  /**
   * @var \GuzzleHttp\Client
   */
  protected $client;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Cache ID for list of municipalities.
   */
  const CACHE_ID = 'user_location_ws_municipality_list';

  /**
   * UserLocationWsClient constructor.
   *
   * @param $http_client_factory \Drupal\Core\Http\ClientFactory
   * @param $cacheBackend \Drupal\Core\Cache\CacheBackendInterface
   * @param $time \Drupal\Component\Datetime\TimeInterface
   * @param $config \Drupal\Core\Config\Config
   */
  public function __construct(
    $http_client_factory,
    CacheBackendInterface $cacheBackend,
    TimeInterface $time,
    ConfigFactoryInterface $configFactory
  ) {
    $this->client = $http_client_factory->fromOptions();
    $this->cacheBackend = $cacheBackend;
    $this->time = $time;
    // Use config from related form.
    $this->config = $configFactory->getEditable(UserLocationWsSettingsForm::CONFIG_NAME);
  }

  /**
   * Fetches data from WS.
   *
   * @return array
   */
  public function obtainData() {

    // Ok, so I ran into WS API quota here, so will use cache mechanism here.
    // Cache is used as storage here.
    if ($cache = $this->cacheBackend->get(static::CACHE_ID)) {
      return $cache->data;
    }

    $config = $this->config->get('ws');
    if (empty($config['service_url'])) {
      throw new MunicipalityProviderException('No service URL.');
    }
    if (empty($config['api_key'])) {
      throw new MunicipalityProviderExceptionMissingKey('No API key.');
    }

    $query = [
      'key' => $config['api_key'] ?? '',
      'group' => 'municipality',
      'municipality' => '',
    ];

    $data = [];
    $total = $current = 0;

    // Tested API calls locally and further reflects flow for obtaining all data in paged nature.
    do {
      $response = $this->client->get($config['service_url'], [
        'query' => $query,
      ]);

      $body = Json::decode($response->getBody());

      // Noticed that WS can ran into API quota or other API restrictions, so lets handle that.
      if (empty($body['status']) || $body['status'] != 'success') {
        throw new MunicipalityProviderException('WS API request failure');
      }

      // Format recieved is weird but lets get only what we need here.
      $municipalities = array_column($body['data'], 'municipality');
      $data = array_merge($data, $municipalities);
      $current = $body['page']['current'] ?? 0;
      $total = $body['page']['total'] ?? 0;
      $current++;
      $query['page'] = $current;
    } while ($current <= $total);

    // Possible todo here: move cache max-age to config.
    $expire = $this->time->getCurrentTime() + 50 * 3600;
    $this->cacheBackend->set(static::CACHE_ID, $data, $expire);

    return $data;
  }

}

<?php
namespace App\Services\Api;

use phpDocumentor\Reflection\Types\This;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

class ApplicationsApi {

    private $client;
    private $params;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->client = $client;
        $this->params = $params;

    }


    public function fetch(String $uri, Array $httpParams)
    {

        $response = $this->client->request('GET',$uri, $httpParams);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200)
        {
            return $response;
        }

        return false;
    }


    /**
     * Retrieve application version through API call
     * @param string $application | The name of application define in config/services.yaml
     * @return string $version
     */

    public function getVersion(String $application): String {

        if ($this->params->get('api')) 
        {
            if (array_key_exists($application,$this->params->get("api")) == false) 
            {
                return false;
            }
        } else {
            return false;
        }
        
        $config = $this->params->get('api')[$application];
        $httpParams = array();

        // Has authorization ?
        if (array_key_exists('authorization',$config) && array_count_values($config['authorization']) > 0)
        {
            $authorization = $this->getAuthorization($config);
            $httpParams += $authorization;
        }

        $uri = $config['uri'] . $config['endpoint'];

        $fetch = $this->fetch($uri,$httpParams);

        if ($fetch == false) 
        {
            return false;
        }

        $content = $fetch->toArray();
        $headers = $fetch->getHeaders();

        switch ($application) {
            case 'wapt':
                return $content['result']['version'];
            break;
            case 'grafana':
                return $content['buildInfo']['version'];
            break;
            case 'glpi':
                return $content['cfg_glpi']['version'];
            break;
            case 'kapacitor':
                return $headers['x-kapacitor-version']['0'];
            break;
            case 'cachethq':
                return $content['data'];
            break;

            default:
                return false;
                break;
        }

    }


    /**
     * Return correct authorization parameters for http request
     * @param Array $config Retrieve config from config/services.yaml
     * @return Array
     */
    public function getAuthorization(Array $config): Array
    {
        $uri = $config['uri'];
        $authorization = $config['authorization'];

        switch ($authorization['type']) {
            case 'auth_basic':
                $authorization = array(
                    'auth_basic' => [$authorization['username'], $authorization['password']]
                );
                break;
            case 'auth_bearer':
                $authorization = array(
                    'auth_bearer' => $authorization['token']
                );
                break;
            case 'auth_ntlm':
                $authorization = array(
                    'auth_ntlm' => [$authorization['username'], $authorization['password']]
                );
                break;
            case 'auth_sessiontoken':
                $authEndpoint = $uri . $authorization['auth_endpoint'];
                $sessionToken = $this->newApiAuthentification($authorization['user_token'], $authorization['app_token'], $authEndpoint);
                $authorization = array(
                    'headers' => [
                        'Session-Token' => $sessionToken,
                        'App-Token'=> $authorization['app_token']
                    ]
                );

                break;
            default:
                # code...
                break;
        }

        return $authorization;
    }


    /**
     * Some API need to be authenticate with token to get a session token
     * @param string $userToken
     * @param string $appToken
     * @return string $session
     */
    public function newApiAuthentification (String $userToken, String $appToken, String $authEndpoint): String
    {

        $httpParams = array(
            'headers' => [
                'Content-Type: application/json',
                'App-Token: '. $appToken,
                'Authorization: user_token '. $userToken
            ]
        );

        $fetch = $this->fetch($authEndpoint,$httpParams);
        $content = $fetch->toArray();
        $authSessionToken = $content['session_token'];

        return $authSessionToken;
    }
}

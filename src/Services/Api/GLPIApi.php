<?php

namespace App\Services\Api;

use App\Repository\ApplicationsRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GLPIApi extends ApplicationsRepository
{

    private $client;
    private $params;
    private $apiEndpoint;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $params)
    {
        $this->client = $client;

        if ($this->testApiConfig($params)) 
        {
            $this->params = $params->get('glpi');
            $this->apiEndpoint = $this->params["uri"] . 'apirest.php';
        }
        
    }

    public function testApiConfig($params)
    {
        if ($params->has('glpi')) 
        {
            $params = $params->get('glpi');
            return true;
        }

        return false;
    }


    public function fetch(String $uri, String $method, Array $httpParams = [])
    {
        $response = $this->client->request($method,$uri, $httpParams);
        $statusCode = $response->getStatusCode();
        return $response;
    }




    /**
     * Some API need to be authenticate with token to get a session token
     * @param string $userToken
     * @param string $appToken
     * @return string $session
     */
    public function newApiAuthentification (): String
    {

        $httpParams = array(
            'headers' => [
                'Content-Type: application/json',
                'App-Token: '. $this->params['app_token'],
                'Authorization: user_token '. $this->params['user_token']
            ]
        );

        $endpoint = $this->apiEndpoint . '/initSession';
        $fetch = $this->fetch($endpoint,'GET',$httpParams);
        $content = $fetch->toArray();

        return $content['session_token'];
    }

    /**
     * Some API need to be authenticate with token to get a session token
     * @param string $uri
     * @param string $method
     */
    public function newApiCall(String $endpoint, String $method, Array $data = [])
    {
        dump($data);
        $sessionToken = $this->newApiAuthentification();
        $headers = array(
            'headers' => [
                'Content-Type: application/json',
                'App-Token: '. $this->params['app_token'],
                'Session-Token: ' . $sessionToken
            ],
        );

        $httpParams = array_merge($headers,$data);


        $uri = $this->apiEndpoint . '/' . $endpoint;
        return $this->fetch($uri,$method,$httpParams);
    }

    /**
     * @param String $email
     * @return Array Return user informations
     */
    public function findUserByMail(String $email)
    {
        $searchFilter = "/search/User?criteria[0][field]=2&criteria[0][searchtype]=2&criteria[0][value]=&criteria[1][link]=AND&criteria[1][field]=1&criteria[1][searchtype]=2&criteria[1][value]=";
        $searchFilter = $searchFilter . $email;
        $result = $this->newApiCall($searchFilter,'GET');
        $content = $result->toArray();
        dump($content);
        if (!array_key_exists("data",$content)) {
            return false;
        }
        return $content['data'][0];
    }

    /**
     * @param String $email
     * @return String Return user id
     */
    public function getUserId(String $email): string
    {
        if ($this->findUserByMail($email))
        {
            return $this->findUserByMail($email)[2];
        }
        return false;
    }

    /**
     * @param String $id
     */
    public function deleteTicket(String $id) 
    {
        return $this->newApiCall('Ticket/' . $id,'DELETE');
    }

    /**
     * @param String $title 
     * @param String $content Content of the ticket
     * @param String $userId Id of the user who open the ticket
     * @param String $userAssignedId Id of the user to be assigned
     * @return $id Id of the created ticket
     */

    public function addTicket(String $title, String $content, String $userId, String $userAssignedId) 
    {

        dump($this->params);
        $data = array (
            'json' => array(
            'input' => array (
                    'name' => $title,
                    "content" => $content,
                    '_users_id_requester' => $userId,
                    '_users_id_assign' => $userAssignedId,
                    'itilcategories_id' => $this->params['itilcategories_id'],
                    'entities_id' => $this->params['entities_id'],
                    'status' =>'3' //Status Planifié
                )
            )
            );
        
        return $this->newApiCall("Ticket","POST",$data)->toArray()['id'];
    }

    /**
     * @param String $id Id of Ticket
     * @param String $userId Id of the user who open the ticket
     * @return
     */
    public function closeTicket(String $ticketId, String $userId) 
    {
        $data = array (
            'json' => array(
            'input' => array (
                'itemtype' => 'Ticket',
                'items_id' => $ticketId,
                'status' => '5',
                'users_id' => $userId
                )
            )
            );
        $uri = 'Ticket/' . $ticketId;

        return $this->newApiCall($uri,"PUT",$data);
    }

    /**
     * @param String $id Id of Ticket
     * @param String $userId Id of the user who open the ticket
     * @return
     */
    public function assignTicket(String $ticketId, String $userId)
    {
        $data = array (
            'json' => array(
            'input' => array (
                    'tickets_id' => $ticketId,
                    "users_id" => $userId,
                    'type' => 2,

                )
            )
            );
        $uri = 'Ticket/' . $ticketId . '/Ticket_User';

        return $this->newApiCall($uri,"POST",$data);
    }

    /**
     * @param String $ticketId Id of Ticket
     * @param String $userId If of the user
     */
    public function unAssignTicket(String $ticketId, String $userId)
    {
        $id = $this->getAssignedTicketUser($ticketId,$userId);

        $uri = 'Ticket/' . $ticketId . '/Ticket_User/' . $id;
        return $this->newApiCall($uri,'DELETE');
    }

    /**
     * @param String $ticketId Id of Ticket
     * @param String $userId If of the user
     */
    public function getAssignedTicketUser(String $ticketId, String $userId) 
    {
        $uri = 'Ticket/' . $ticketId . '/Ticket_User';
        $result = $this->newApiCall($uri,'GET')->toArray();

        foreach ($result as $key => $value) {
            if ($value['users_id'] == $userId)
            {
                return $value['id'];
            }
        }

        return false;
    }

    /**
     * @param String $ticketId Id of Ticket
     * @param String $content
     * @param String $userId
     * @return
     */
    public function addTicketFollowUp(String $ticketId, String $content, String $userId)
    {
        $data = array (
            'json' => array(
            'input' => array (
                'itemtype' => 'Ticket',
                'items_id' => $ticketId,
                'content' => $content,
                'is_private' => '0',
                'users_id' => $userId

                )
            )
            );
        $uri = 'Ticket/' . $ticketId . '/ITILFollowup';
        
        return $this->newApiCall($uri,"POST",$data);
    }
    
    /**
     * getApiUserId
     *
     * @return void
     */
    public function getApiUserId()
    {
        $result = $this->newApiCall('getFullSession/','GET')->toArray();
        return $result["session"]["glpiID"];
    }
}

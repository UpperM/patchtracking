<?php

namespace App\Services\Api;

use App\Repository\ApplicationsRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GithubApi extends ApplicationsRepository
{

    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }


    /**
     * Fetch github repository latest release version
     * 
     * @param string $url Url of Github repository
     * @return string
     */
    public function fetchGitHubLatestRelease(String $url): string
    {
        $url = $this->getGithubUri($url);
        
        $response = $this->client->request(
            'GET',
            $url
        );

        $content = $response->getContent();
        $content = $response->toArray();

        // Extract tag name from array
        $githubVersion = $content['tag_name'];
        $version = str_replace("v","",$githubVersion);

        
        return $version;
    }

    /**
     * Convert Github repository url to api repository url
     * 
     * @param string $url Url of Github repository
     * @return string
     */
    public function getGithubUri($githubUrl): string
    {
        $url = str_replace ( "https://github.com/","", $githubUrl );
        $url = rtrim($url,"/");
        $url = "https://api.github.com/repos/". $url . "/releases/latest";
        return $url;
    }

}

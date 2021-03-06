<?php

namespace App\ResourceSpace;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ResourceSpace
{
    private $apiUrl;
    private $apiUsername;
    private $apiKey;

    public function __construct(ParameterBagInterface $params)
    {
        $resourceSpaceApi = $params->get('resourcespace_api');
        $this->apiUrl = $resourceSpaceApi['url'];
        $this->apiUsername = $resourceSpaceApi['username'];
        $this->apiKey = $resourceSpaceApi['key'];
    }

    public function getAllResources($key, $value)
    {
        $allResources = $this->doApiCall('do_search&param1=' . $key . ':' . $value);

        if ($allResources == 'Invalid signature') {
            echo 'Error: invalid ResourceSpace API key. Please paste the key found in the ResourceSpace user management into config/connector.yml.' . PHP_EOL;
//            $this->logger->error('Error: invalid ResourceSpace API key. Please paste the key found in the ResourceSpace user management into config/connector.yml.');
            return NULL;
        }

        $resources = json_decode($allResources, true);
        return $resources;
    }

    public function getResourceDataIfFieldContains($ref, $fieldName, $filter)
    {
        $resourceData = $this->getResourceInfo($ref);
        $isValid = false;
        if($resourceData != null) {
            if(!empty($resourceData)) {
                foreach($resourceData as $field) {
                    if($field['name'] == $fieldName) {
                        if(in_array($field['value'], $filter)) {
                            $isValid = true;
                        } else {
                            $isValid = false;
                            break;
                        }
                    }
                }
            }
        }
        return $isValid ? $resourceData : null;
    }

    private function getResourceInfo($id)
    {
        $data = $this->doApiCall('get_resource_field_data&param1=' . $id);
        return json_decode($data, true);
    }

    public function getResourceUrl($id)
    {
        $data = $this->doApiCall('get_resource_path&param1=' . $id . '&param2=0');
        return json_decode($data, true);
    }

    private function doApiCall($query)
    {
        $query = 'user=' . $this->apiUsername . '&function=' . $query;
        $url = $this->apiUrl . '?' . $query . '&sign=' . $this->getSign($query);
        $data = file_get_contents($url);
        return $data;
    }

    private function getSign($query)
    {
        return hash('sha256', $this->apiKey . $query);
    }
}

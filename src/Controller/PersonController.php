<?php

namespace Src\PersonController;

use Src\TableGateways\PersonGateway;

class PersonController
{
    private $db;
    private $requestMethod;
    private $userId;

    private $personGateway;

    public function __construct($db, $requestMethod, $userId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;

        $this->personGateway = new PersonGateway($db);
    }

    public function processRequest()
    {
        switch($this->requestMethod) {
            case 'GET' : 
                if ($this->userId) {
                    $response = $this->getUser($this->userId);
                }else {
                    $response = $this->getAllUsers();
                };
                break;
            case 'POST':
                $response = $this->createUser();
                break;
            case 'PUT' :
                $response = $this->updateUser($this->userId);
                break;
            case 'DELETE' : 
                $response = $this->deleteUser($this->userId);
                break;
            default : 
                $response = $this->notFound();
                break;
        }

        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    public function getAllUsers()
    {
        $result = $this->personGateway->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    public function getUser($id)
    {
        $result = $this->personGateway->find($id);
        if(!$result){
            return $this->notFound();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    public function createUser()
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validatePerson($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->personGateway->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    public function updateUser($id)
    {
        $result = $this->personGateway->find($id);
        if (!$result){
            return $this->notFound();
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if(! $this->validatePerson($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->personGateway->update($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    public function deleteUser($id)
    {
        $result = $this->personGateway->find($id);
        if (! $result) {
            return $this->notFound();
        }
        $this->personGateway->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    public function validatePerson($input)
    {
        if(!isset($input['firstname'])){
            return false;
        }
        if(!isset($input['lastname'])){
            return false;
        }
        return true;
    }

    public function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    public function notFound()
    {
        $response['status_code_header'] = 'HTTP/1.1 422  Not Found';
        $response['body'] = null;
        return $response;
    }
}
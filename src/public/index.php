<?php
session_start();
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

//config
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['file']   = '../chat.db';

$app = new \Slim\App(['settings' => $config]);

//containers
$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    try {
    $pdo = new PDO('sqlite:' . $db['file']);
    } catch(PDOException $e) {
        echo($e->showMessage());
    }


    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return new Query($pdo);
};

$container['view'] = new \Slim\Views\PhpRenderer('../templates/');

//routes
    
    //main page
    $app->get('/', function (Request $request, Response $response) {
    
    $messages = $this->db->selectAll('messages');
    
    $response = $this->view->render($response, 'index.phtml', ['messages' => $messages]);
    return $response;
    
    });
    
    //change name&offline status for the previous name
    $app->post('/logout', function (Request $request, Response $response) {
    
    $this->db->logOff('users', $_SESSION['name']);
    $_SESSION['name'] = '';
    $response = $response->withRedirect("/");
    return $response;
    
    });

    //pick name
    $app->post('/choose', function (Request $request, Response $response) {

        $this->db->logOff('users', $_SESSION['name']);
        
        $data = $request->getParsedBody();
        $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
        
        
        $this->db->setUser('users', $name);
        $_SESSION['name'] = $name;
        
        $response = $response->withRedirect("/");
        return $response;
    });
        
    //show list of users
    $app->get('/users', function (Request $request, Response $response) {
        
        $users = $this->db->selectAll('users');
        
        $response = $this->view->render($response, 'users.phtml', ['users' => $users]);
        return $response;
        
    });
    
    //send message
    $app->post('/send', function (Request $request, Response $response) {
    
    $data = $request->getParsedBody();
    $receiver = filter_var($data['receiver'], FILTER_SANITIZE_STRING);
    $message = filter_var($data['message'], FILTER_SANITIZE_STRING);
    
    $params['author'] = $_SESSION['name'];
    $params['receiver'] = $receiver;
    $params['message'] = $message;
    
    $this->db->insert('messages', $params);
    
    $response = $response->withRedirect("/");
    return $response;
    });
    

$app->run();
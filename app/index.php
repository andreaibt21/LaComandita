<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

include_once './controllers/UsuarioController.php';
include_once './controllers/ProductoController.php';
include_once './controllers/MesaController.php';
include_once './controllers/PedidoController.php';
include_once './controllers/PedidoProductoController.php';

include_once './controllers/SectorController.php';
include_once './controllers/TipoUsuarioController.php';

include_once './db/AccesoDatos.php';
include_once './middlewares/UsuarioMW.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Set base path
$app->setBasePath('/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

$app->get('[/]', function (Request $request, Response $response) {    
  $response->getBody()->write("¡La Comandita!(como la Churrasquita pero menos cheto)");
  return $response;

});

$app->post('/empleados/login[/]', \UsuarioController::class . ':Login');

//Usuarios
$app->group('/empleados', function (RouteCollectorProxy $group) 
{
  //ABM
  $group->post('/alta[/]', \UsuarioController::class . ':Alta');
  $group->delete('/baja/{id}[/]', \UsuarioController::class . ':Baja');
  $group->post('/modificacion[/]', \UsuarioController::class . ':Modificacion'); 
  $group->get('/lista[/]', \UsuarioController::class . ':Listar');  
});

//Productos
$app->group('/productos', function (RouteCollectorProxy $group) 
{
  $group->post('/alta[/]', \ProductoController::class . ':Alta'); 
  $group->delete('/baja/{id}[/]', \ProductoController::class . ':Baja');
  $group->post('/modificacion[/]', \ProductoController::class . ':Modificacion');  
  $group->get('/lista[/]', \ProductoController::class . ':Listar');  
});

$app->run();

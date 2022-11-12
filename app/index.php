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
include_once './controllers/ServicioController.php';
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
//Genera el Token
$app->post('/empleados/login[/]', \UsuarioController::class . ':Login');

//Sector 1-Cocina 2-Barra 3-choperas 4-candyBar
$app->group('/sector', function (RouteCollectorProxy $group) 
{
  //ABM
  $group->post('/alta[/]', \SectorController::class . ':Alta');
  $group->post('/modificacion[/]', \SectorController::class . ':Modificacion');
  $group->delete('/baja/{id}[/]', \SectorController::class . ':Baja');
  $group->get('/lista[/]', \SectorController::class . ':Listar');  
});

//Tipo de usuario 1-socio 2-mozo 3-bartender 4-cervecero 5-cocinero 6-repostero
$app->group('/tipousuario', function (RouteCollectorProxy $group) 
{
  //ABM
  $group->post('/alta[/]', \TipoUsuarioController::class . ':Alta');
  $group->post('/modificacion[/]', \TipoUsuarioController::class . ':Modificacion');
  $group->delete('/baja/{id}[/]', \TipoUsuarioController::class . ':Baja');
  $group->get('/lista[/]', \TipoUsuarioController::class . ':Listar'); 
}); 

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

//Mesa
$app->group('/mesa', function (RouteCollectorProxy $group) 
{
  //ABM
  $group->post('/alta[/]', \MesaController::class . ':Alta'); 
  $group->delete('/baja/{id}[/]', \MesaController::class . ':Baja');
  $group->post('/modificacion[/]', \MesaController::class . ':Modificacion'); 
  $group->get('/lista[/]', \MesaController::class . ':Listar'); 
});

//Pedido
$app->group('/servicio', function (RouteCollectorProxy $group) 
{
  //ABM
  $group->post('/alta[/]', \ServicioController::class . ':Alta');
  $group->delete('/baja/{id}[/]', \ServicioController::class . ':Baja');
  $group->post('/modificacion[/]', \ServicioController::class . ':Modificacion');
  //Subir Foto
  $group->post('/subirfoto[/]', \ServicioController::class . ':SubirFoto');
  //Manejo del pedido
  $group->get('/paraservir[/]', \ReportesAPI::class . ':PedidoProductoListoParaServir'); 
  $group->post('/comiendo[/]', \ServicioController::class . ':PasarAComiendo'); 
  $group->post('/pagando[/]', \ServicioController::class . ':PasarAPagando'); 
})
  ->add(\UsuarioMW::class. ':ValidarMozo')
  ->add(\UsuarioMW::class. ':ValidarToken');

$app->run();

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
include_once './controllers/ReportesController.php';
include_once './controllers/SectorController.php';
include_once './controllers/TipoUsuarioController.php';
include_once './controllers/EncuestaController.php';

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
})
->add(\UsuarioMW::class. ':ValidarSocio')
->add(\UsuarioMW::class. ':ValidarToken');;

//Tipo de usuario 1-socio 2-mozo 3-bartender 4-cervecero 5-cocinero 6-repostero
$app->group('/tipousuario', function (RouteCollectorProxy $group) 
{
  //ABM
  $group->post('/alta[/]', \TipoUsuarioController::class . ':Alta');
  $group->post('/modificacion[/]', \TipoUsuarioController::class . ':Modificacion');
  $group->delete('/baja/{id}[/]', \TipoUsuarioController::class . ':Baja');
  $group->get('/lista[/]', \TipoUsuarioController::class . ':Listar'); 
})
->add(\UsuarioMW::class. ':ValidarSocio')
->add(\UsuarioMW::class. ':ValidarToken'); 

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
   //CSV
  $group->get('/guardar[/]', \ProductoController::class . ':ExportarTabla');  
  $group->post('/leer[/]', \ProductoController::class . ':ImportarTabla');  
})
->add(\UsuarioMW::class. ':ValidarSocio')
->add(\UsuarioMW::class. ':ValidarToken');

//Mesa
$app->group('/mesa', function (RouteCollectorProxy $group) 
{
  //ABM
  $group->post('/alta[/]', \MesaController::class . ':Alta'); 
  $group->delete('/baja/{id}[/]', \MesaController::class . ':Baja');
  $group->post('/modificacion[/]', \MesaController::class . ':Modificacion'); 
  $group->get('/lista[/]', \MesaController::class . ':Listar'); 
})
->add(\UsuarioMW::class. ':ValidarSocio')
->add(\UsuarioMW::class. ':ValidarToken');

//Pedido
$app->group('/pedido', function (RouteCollectorProxy $group) 
{
  //ABM
  $group->post('/alta[/]', \PedidoController::class . ':Alta');
  $group->delete('/baja/{id}[/]', \PedidoController::class . ':Baja');
  $group->post('/modificacion[/]', \PedidoController::class . ':Modificacion');
  //Subir Foto
  $group->post('/subirfoto[/]', \PedidoController::class . ':SubirFoto');
  //Manejo del pedido
  $group->get('/paraservir[/]', \ReportesController::class . ':PedidoProductoListoParaServir'); 
  $group->post('/comiendo[/]', \PedidoController::class . ':PasarAComiendo'); 
  $group->post('/pagando[/]', \PedidoController::class . ':PasarAPagando'); 
})
  ->add(\UsuarioMW::class. ':ValidarMozo')
  ->add(\UsuarioMW::class. ':ValidarToken');

  $app->get('/pedido/lista[/]', \PedidoController::class . ':Listar');

//Cerrar pedido
$app->post('/pedido/cerrar[/]', \PedidoController::class . ':CerrarPedido') 
  ->add(\UsuarioMW::class. ':ValidarSocio')
  ->add(\UsuarioMW::class. ':ValidarToken');

//PedidoProducto
$app->group('/pedidoproducto', function (RouteCollectorProxy $group) 
{
  //ABM
  $group->post('/alta[/]', \PedidoProductoController::class . ':Alta'); 
  $group->delete('/baja/{id}[/]', \PedidoProductoController::class . ':Baja'); 
  $group->post('/modificacion[/]', \PedidoProductoController::class . ':Modificacion'); 
})
  ->add(\UsuarioMW::class. ':ValidarMozo')
  ->add(\UsuarioMW::class. ':ValidarToken');

  //Listado de pedidos activos
$app->get('/pedido/listabarra[/]', \PedidoProductoController::class . ':ListarPedidosBarra')
->add(\UsuarioMW::class. ':ValidarBartender')
->add(\UsuarioMW::class. ':ValidarToken');  
$app->get('/pedido/listachoperas[/]', \PedidoProductoController::class . ':ListarPedidosChoperas')
->add(\UsuarioMW::class. ':ValidarCervecero')
->add(\UsuarioMW::class. ':ValidarToken');;  
$app->get('/pedido/listacocina[/]', \PedidoProductoController::class . ':ListarPedidosCocina')
->add(\UsuarioMW::class. ':ValidarCocinero')
->add(\UsuarioMW::class. ':ValidarToken');  
$app->get('/pedido/listacandybar[/]', \PedidoProductoController::class . ':ListarPedidosCandybar') 
->add(\UsuarioMW::class. ':ValidarRepostero')
->add(\UsuarioMW::class. ':ValidarToken');

//Reportes
$app->group('/reportes', function (RouteCollectorProxy $group) 
{
  $group->get('/demorapedidoscerrados[/]', \ReportesController::class . ':DemoraPedidosCerrados');  
  $group->get('/estadomesas[/]', \ReportesController::class . ':EstadoMesas');  
  $group->get('/mejorescomentarios[/]', \ReportesController::class . ':MejoresComentarios');  
  $group->get('/mesamasusada[/]', \ReportesController::class . ':MesaMasUsada'); 
})
  ->add(\UsuarioMW::class. ':ValidarSocio')
  ->add(\UsuarioMW::class. ':ValidarToken');

$app->post('/reportes/demorapedidomesa[/]', \ReportesController::class . ':DemoraPedidoMesa'); 

//Manejo estados Pedido Producto
$app->post('/pedido/enpreparacion[/]', \PedidoProductoController::class . ':PedidoEnPreparacion');
$app->post('/pedido/listo[/]', \PedidoProductoController::class . ':PedidoListo');

//encuesta
$app->post('/encuesta/nuevaencuesta[/]', \EncuestaController::class . ':Alta'); 

$app->run();

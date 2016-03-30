<?php

date_default_timezone_set("Asia/Bangkok");

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

require_once '../vendor/autoload.php';
require_once 'global.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->post('/insert', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $users_id = $request->get('users_id');
    $agent_id = $request->get('agent_id');
    $waste_id = $request->get('waste_id');
    $qty = $request->get('qty');

    if(!$conn->connect_error) {

        $query_insert = "INSERT INTO collection(users_id, agent_id, waste_id, qty)
values ($users_id, $agent_id, $waste_id, $qty)";

        $result = $conn->query($query_insert) or die(mysqli_error($conn));
        //$result = $conn->query($query);

        if($result)
        {
            $data['status'] = 'ok';
        }

    }

    $dataJson = json_encode($data);
    return new Response($dataJson, 200, array(
        'Content-Type' => 'application/json'
    ));
});

$app->run();
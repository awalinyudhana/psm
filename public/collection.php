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


$app->get('/get', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'data' => array()
    );

    if(!$conn->connect_error) {
        $query = "SELECT c.*, u.name as users_name, a.name as agent_name, c.name as coverage_name
FROM collection c
JOIN users u ON u.users_id = c.users_id
JOIN coverage c ON c.coverage_id = u.coverage_id
JOIN agent a ON a.agent_id = c.agent_id";

        $result = $conn->query($query);
        if($result->num_rows > 0) {
            $data['status'] = 'success';
            while($row = $result->fetch_assoc()) {
                $data['data'][] = $row;
            }
        } else {
            $data['status'] = 'error - not found';
        }
    }

    $dataJson = json_encode($data);
    return new Response($dataJson, 200, array(
        'Content-Type' => 'application/json'
    ));
});

$app->get('/users', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'data' => array()
    );

    $users_id = $request->get('users_id');

    if(!$conn->connect_error) {
        $query = "SELECT c.*, u.name as users_name, a.name as agent_name, c.name as coverage_name
FROM collection c
JOIN users u ON u.users_id = c.users_id
JOIN coverage c ON c.coverage_id = u.coverage_id
JOIN agent a ON a.agent_id = c.agent_id
WHERE c.users_id = $users_id
ORDER BY c.date";

        $result = $conn->query($query);
        if($result->num_rows > 0) {
            $data['status'] = 'success';
            while($row = $result->fetch_assoc()) {
                $data['data'][] = $row;
            }
        } else {
            $data['status'] = 'error - not found';
        }
    }

    $dataJson = json_encode($data);
    return new Response($dataJson, 200, array(
        'Content-Type' => 'application/json'
    ));
});

$app->run();
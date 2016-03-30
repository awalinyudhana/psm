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

$app->post('/payment', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $agent_id = $request->get('agent_id');
    $commission = $request->get('commission');

    if(!$conn->connect_error) {

        $query = "SELECT * FROM agent a WHERE a.agent_id = $agent_id";

        $result = $conn->query($query) or die(mysqli_error($conn));

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();

            if($commission <= $row['commission']){

                $query_insert = "INSERT INTO redeem
(users_id, point)
values ($agent_id, $commission)";

                $result = $conn->query($query_insert) or die(mysqli_error($conn));

                if($result)
                {
                    $data['status'] = 'ok';
                }
            }else{
                $data = ['status' => 'error', 'message' => 'not enough commission'];
            }
        }else{
            $data = ['status' => 'error', 'message' => 'username not found'];
        }
    }

    $dataJson = json_encode($data);
    return new Response($dataJson, 200, array(
        'Content-Type' => 'application/json'
    ));
});

$app->post('/process', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $payment_id = $request->get('payment_id');
    $admin_id = $request->get('admin_id');
    $note = $request->get('note');
    $date_approved = date('Y-m-d h:i:s');

    if(!$conn->connect_error) {

        $query_update = "UPDATE payment p SET p.status = 1, p.admin_id = $admin_id, p.note = '$note',
p.date_approved = '$date_approved' WHERE p.payment_id = $payment_id";

        $result = $conn->query($query_update) or die(mysqli_error($conn));
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


$app->post('/reject', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $payment_id = $request->get('payment_id');
    $admin_id = $request->get('admin_id');
    $note = $request->get('note');
    $date_approved = date('Y-m-d h:i:s');

    if(!$conn->connect_error) {

        $query_update = "UPDATE payment p SET p.status = 2, p.admin_id = $admin_id, p.note = '$note',
p.date_approved = '$date_approved' WHERE p.payment_id = $payment_id";

        $result = $conn->query($query_update) or die(mysqli_error($conn));
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

$app->get('/pending', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'data' => array()
    );

    if(!$conn->connect_error) {
        $query = "SELECT p*. a.name as agent_name, c.name as coverage_name FROM payment p
JOIN agent a ON a.agent_id = p.agent_id
JOIN coverage c ON c.coverage_id = a.coverage_id
WHERE p.status = 0";

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

$app->get('/history', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'data' => array()
    );

    if(!$conn->connect_error) {
        $query = "SELECT p*. a.name as agent_name, c.name as coverage_name FROM payment p
JOIN agent a ON a.agent_id = p.agent_id
JOIN coverage c ON c.coverage_id = a.coverage_id
 WHERE r.status != 0";

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
//udpate
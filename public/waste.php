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

$app->get('/get', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'data' => array()
    );

    if(!$conn->connect_error) {
        $query = "SELECT * FROM waste";

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

$app->post('/insert', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $name = strtolower($request->get('name'));
    $kode = substr(strtoupper($request->get('name')), 0, 3);
    $point = $request->get('point');
    $commission = $request->get('commission');
    $unit = $request->get('unit');

    if(!$conn->connect_error) {
        $query = "INSERT INTO waste
(name, kode, point, commission, unit)
values ('$name', '$kode', $point, $commission, '$unit')";


        $result = $conn->query($query) or die(mysqli_error($conn));
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


$app->post('/update', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $waste_id = $request->get('waste_id');
    $name = strtolower($request->get('name'));
    $kode = substr(strtoupper($request->get('name')), 0, 3);
    $point = $request->get('point');
    $commission = $request->get('commission');
    $unit = $request->get('unit');

    if(!$conn->connect_error) {
        $query = "UPDATE waste SET name = '$name', kode='$kode', point = $point, unit = '$unit', commission = $commission
WHERE waste_id = $waste_id";


        $result = $conn->query($query) or die(mysqli_error($conn));
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
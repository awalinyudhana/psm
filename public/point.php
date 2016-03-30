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

$app->post('/redeem', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $users_id = $request->get('users_id');
    $point = $request->get('point');

    if(!$conn->connect_error) {

        $query = "SELECT * FROM users u WHERE u.users_id = $users_id";

        $result = $conn->query($query) or die(mysqli_error($conn));

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();

            if($point <= $row['point']){

                $query_insert = "INSERT INTO redeem
(users_id, point)
values ($users_id, $point)";

                $result = $conn->query($query_insert) or die(mysqli_error($conn));

                if($result)
                {
                    $data['status'] = 'ok';
                }
            }else{
                $data = ['status' => 'error', 'message' => 'not enough point'];
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

    $redeem_id = $request->get('redeem_id');
    $admin_id = $request->get('admin_id');
    $note = $request->get('note');
    $date_approved = date('Y-m-d h:i:s');

    if(!$conn->connect_error) {

        $query_update = "UPDATE redeem r SET r.status = 1, r.admin_id = $admin_id, r.note = '$note',
r.date_approved = '$date_approved' WHERE r.redeem_id = $redeem_id";

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

    $redeem_id = $request->get('redeem_id');
    $admin_id = $request->get('admin_id');
    $note = $request->get('note');
    $date_approved = date('Y-m-d h:i:s');

    if(!$conn->connect_error) {

        $query_update = "UPDATE redeem r SET r.status = 2, r.admin_id = $admin_id, r.note = '$note', r.date_approved = '$date_approved'
WHERE r.redeem_id = $redeem_id";

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
        $query = "SELECT r*. u.name as users_name, c.name as coverage_name FROM redeem r
JOIN users u ON u.users_id = r.users_id
JOIN coverage c ON c.coverage_id = u.coverage_id
WHERE r.status = 0";

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
        $query = "SELECT r*. u.name as users_name, c.name as coverage_name FROM redeem r
JOIN users u ON u.users_id = r.users_id
JOIN coverage c ON c.coverage_id = u.coverage_id
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
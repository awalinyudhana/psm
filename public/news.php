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
        $query = "SELECT * from news WHERE status = 1";

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

$app->get('/getV2', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'records' => array(),
        'total' => 0,
        'last' => 0
    );

    if(!$conn->connect_error) {
        $page = ( !empty( $request->get('page') ) ? $request->get('page') : 1 );
        $limit = ( !empty( $request->get('limit') ) ? $request->get('limit') : 6 );;
        $offset = ($page - 1) * $limit;
        $total = 0;

        $query = "SELECT * FROM news WHERE status = 1 ORDER BY date_published DESC ";
        $querylimit = "LIMIT $offset,$limit";

        $query2 = "SELECT COUNT(*) as total FROM (".$query.") as a";

        $result = $conn->query($query2) or die(mysqli_error($conn));

        if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            $total = $row['total'];
          }
        }

        $result = $conn->query($query.$querylimit);
        if($result->num_rows > 0) {
            $data['status'] = 'ok';
            while($row = $result->fetch_assoc()) {
                $data['records'][] = $row;
            }
        } else {
            $data['status'] = 'error - not found';
        }

        $data['total'] = $total;

        if($total > 0)
        {
          $data['last'] = ceil( $total / $limit );
        }

        $data['next'] = 1;

        if($data['last'] > 1 && $page < $data['last'])
        {
          $data['next'] = $page + 1;
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
        $query = "SELECT * from news WHERE status = 0";

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

$app->get('/detail', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed',
        'data' => array()
    );

    $id = $request->get('news_id');

    if(!$conn->connect_error) {
        $query = "SELECT * from news WHERE news_id = $id and status = 1";

        $result = $conn->query($query);
        if($result->num_rows > 0) {
            $data['status'] = 'success';
            $row = $result->fetch_assoc();
            $data['data'] = $row;
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

    $title = $request->get('title');
    $body = $request->get('body');

    if(!$conn->connect_error) {
        $query = "INSERT INTO news
(title, body)
values ('$title', '$body')";


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


$app->get('/disable', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $id = $request->get('news_id');

    if(!$conn->connect_error) {
        $query = "UPDATE news SET status = '0' WHERE news_id = '$id'";

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

$app->get('/enable', function(Request $request) use($app)
{
    $conn = konekDb();
    $data = array(
        'status'  => 'error - db failed'
    );

    $id = $request->get('news_id');
    $date_approved = date('Y-m-d h:i:s');

    if(!$conn->connect_error) {
        $query = "UPDATE news SET status = '1', date_published = '$date_approved' WHERE news_id = '$id'";

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
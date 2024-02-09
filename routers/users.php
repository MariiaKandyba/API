<?php

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day 
}

// Access-Control headers are received during OPTIONS requests 
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
}


function route($method, $urlData, $formData)
{
    require_once "Services/PDOService.php";
    require_once "Model/User.php";
    //GET /Users/{UserId} 
    if ($method === 'GET' && count($urlData) === 1) {
        $UserId = $urlData[0];

        $con = new PDOService("localhost", "UsersDB_MariiaKandyba");
        $prod = $con->getByKey("id",  $UserId, "Users");
        if (empty($prod)) {
            $prod = $con->getByKey("name",  $UserId, "Users");
        }
        header('Content-Type:application/json');
        echo json_encode(array(
            'method' => 'GET',
            'User' => $prod
        ));
        return;
    }
    //return All User 
    if ($method === 'GET' && empty($urlData)) {


        $con = new PDOService("localhost", "UsersDB_MariiaKandyba");
        $prods = $con->getAll("Users");

        header('Content-Type:application/json');
        echo json_encode(array(
            'method' => 'GET',
            'Users' => $prods
        ));
        return;
    }

    //POST /Users 
    //BODY name='p1'& price=20 
    if ($method === 'POST' && empty($urlData)) {
        try {
            $insertP = new User($formData->name, $formData->email, $formData->password);
            $con = new PDOService("localhost", "UsersDB_MariiaKandyba");
            $id = $con->add($insertP, "Users");


            header('HTTP/1.0 200 Ok');
            header('Content-Type:application/json');
            echo json_encode(array(
                'method' => 'POST',
                'id' => $id
            ));
            return;
        } catch (PDOException $e) {
            header('HTTP/1.0 201 Created');
            header('Content-Type:application/json');
            echo json_encode(array(
                'method' => 'POST',
                'message' => "User exsisst already"
            ));
            return;
        }
    }
    //PUT /Users/{UserId} 
    //BODY name='p1'& price=20 
    if ($method === 'PUT' && count($urlData) === 1) {
        $UserId = $urlData[0];
        $updateP = new User($formData->name, $formData->email, $formData->password, $UserId);
        $con = new PDOService("localhost", "UsersDB_MariiaKandyba");
        $affectedRows = $con->update($updateP, "Users", ["id" => $updateP->id]);
        $updatedUser = $con->getByKey("id", $UserId, "Users");
        header('Content-Type:application/json');
        echo json_encode(array(
            'method' => 'PUT',
            'affectedRows' => $affectedRows,
            'User' => $updatedUser
        ));
        return;
    }
    //DELETE /Users/{UserId} 
    if ($method === 'DELETE' && count($urlData) === 1) {
        $UserId = $urlData[0];
        $con = new PDOService("localhost", "UsersDB_MariiaKandyba");
        $con->remove($UserId, "Users");
        header('Content-Type:application/json');
        echo json_encode(array(
            'method' => 'DELETE',
            'id' => $UserId
        ));
        return;
    }
    header('HTTP/1.0 400 Bad Request');
    echo json_encode(
        array(
            'error' => 'Bad Request'
        )

    );
}

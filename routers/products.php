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


require_once "Model/Product.php";
require_once "Services/PDOService.php";

function route($method, $urlData, $formData)
{
    //GET /products/{productId} 
    if ($method === 'GET' && count($urlData) === 1) {
        $productId = $urlData[0];

        $con = new PDOService("localhost", "ProductsDB_MariiaKandyba");
        $prod = $con->getByKey("id",  $productId, "Products");
        if(empty($prod)){
        $prod = $con->getByKey("title",  $productId, "Products");
        }
        header('Content-Type:application/json');
        echo json_encode(array(
            'method' => 'GET',
            'product' => $prod
        ));
        return;
    }
    //return All product 
    if ($method === 'GET' && empty($urlData)) {


        $con = new PDOService("localhost", "ProductsDB_MariiaKandyba");
        $prods = $con->getAll("Products");

        header('Content-Type:application/json');
        echo json_encode(array(
            'method' => 'GET',
            'products' => $prods
        ));
        return;
    }

    //POST /products 
    //BODY name='p1'& price=20 
    if ($method === 'POST' && empty($urlData)) {
        $insertP = new Product($formData->title, $formData->price);
        $con = new PDOService("localhost", "ProductsDB_MariiaKandyba");
        $id = $con->add($insertP, "Products");

        $createdProduct = $con->getByKey("id", $id, "Products");
        header('HTTP/1.0 201 Created');
        header('Content-Type:application/json');
        echo json_encode(array(
            'method' => 'POST',
            'product' => $createdProduct
        ));
        return;
    }
    //PUT /products/{productId} 
    //BODY name='p1'& price=20 
    if ($method === 'PUT' && count($urlData) === 1) {
        $productId = $urlData[0];
        $updateP = new Product($formData->title, $formData->price, $productId);
        $con = new PDOService("localhost", "ProductsDB_MariiaKandyba");
        $affectedRows = $con->update($updateP, "Products", ["id" => $updateP->id]);
        $updatedProduct = $con->getByKey("id", $productId, "Products");
        header('Content-Type:application/json');
        echo json_encode(array(
            'method' => 'PUT',
            'affectedRows' => $affectedRows,
            'product' => $updatedProduct
        ));
        return;
    }
    //DELETE /products/{productId} 
    if ($method === 'DELETE' && count($urlData) === 1) {
        $productId = $urlData[0];
        $con = new PDOService("localhost", "ProductsDB_MariiaKandyba");
        $con->remove($productId, "Products");
        header('Content-Type:application/json');
        echo json_encode(array(
            'method' => 'DELETE',
            'id' => $productId
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

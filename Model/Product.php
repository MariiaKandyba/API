<?php
class Product{
    public $id;
    public $title;
    public $price;
    public function __construct($title='',$price='',$id=0)
    {
     $this->title=$title; 
     $this->price=$price;    
     $this->id=$id; 
    }
}
?>
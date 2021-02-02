<?php
require APPPATH . 'libraries/CreatorJwt.php';
require APPPATH.'libraries/REST_Controller.php';
class Bids extends REST_Controller{
  var $data;
  var $perPage;
  public function __construct(){
    parent::__construct();
    $this->objOfJwt = new CreatorJwt();
    $this->load->library(array('form_validation'));
    $this->load->model(array('userlogin_model','profile_model','general_model'));
    $this->perPage = 2;
    
  }

  /*
    INSERT: POST REQUEST TYPE
    UPDATE: PUT REQUEST TYPE
    DELETE: DELETE REQUEST TYPE
    LIST: Get REQUEST TYPE
  */

  
  public function index_post(){
    // insert data method

  }
  public function index_patch(){
    parse_str(file_get_contents("php://input"),$data);
    print_r($data);die;
  }
  // PUT
  public function index_put(){
    // updating data method
    parse_str(file_get_contents("php://input"),$data);
    print_r($data);die;
    
  }

  // DELETE: 
  public function index_delete(){
   
  }

  // GET:
  public function index_get(){
  }
}

 ?>

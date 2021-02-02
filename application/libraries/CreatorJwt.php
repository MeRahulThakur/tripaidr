<?php 
//application/libraries/CreatorJwt.php
    require APPPATH . '/libraries/JWT.php';

    class CreatorJwt
    {
       

        /*************This function generate token private key**************/ 

        PRIVATE $key = "1234567890qwertyuiopvcxzasdfghjkl"; 
        public function GenerateToken($data)
        {          
            $jwt = JWT::encode($data, $this->key);
            return $jwt;
        }
        

       /*************This function DecodeToken token **************/

        public function DecodeToken($token)
        {   
            try{       
            $decoded = JWT::decode($token, $this->key, array('HS256'));
            $decodedData = (array) $decoded;
            return $decodedData;
            }
            catch(Exception $e){
                return array( "status" => false, "message" =>$e->getMessage());die;
            }
            catch (Error $err) {
                return array( "status" => false, "message" =>$err->getMessage());die;
            }
        }
    }
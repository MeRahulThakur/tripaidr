<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
 * Profile Model 
 * AP
 * @
 */

class Profile_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getProfileDetailsById($id)
    {
        $this->db->select('*');
        $this->db->where('id', $id);
        $this->db->from('users');
        $query = $this->db->get();
        $row = $query->row_array();
        return $row;
    }

    
    // validate email already exits in user register
    public function validateEmail($email = false, $iUserId = false) {
        $this->db->select("id");
        $this->db->where("email", $email);
        if($iUserId){
            $this->db->where("id", $iUserId);
            $this->db->where("status", "1");
        }
        $result = $this->db->get('users');
        $result = $result->row_array();
        return $result;
    }
    
    //validateContactNumber during registration
    public function validateContactNumber($sContactNumber = false, $iUserId = false) {
        $this->db->select("id");
        $this->db->where("mobile", $sContactNumber);
        if($iUserId){
            $this->db->where("id", $iUserId);
            $this->db->where("status", "1");
        }
        $result = $this->db->get('users');
        $result = $result->row_array();
        return $result;
    }
    //add user
    public function addRecord($sTable = false, $aData = array(),$password) {
        if ($sTable && $aData) {
            $this->db->insert($sTable,$aData);
            $iInsertId = $this->db->insert_id();
            $query = "update users set password ='".passwordHash($password)."' where id=$iInsertId";
            $this->db->query($query);
            return $iInsertId;
        } else
            return false;
    }
}

<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Userlogin_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }
    /*
     * user authentication checking
     * @param array('email','password')
     */
    public function user_authentication($val)
    {
        $this->db->select('*');
        $this->db->where('email', $val['username']);
        $this->db->where('status', 1);
        $this->db->where('is_delete', 0);
        $query = $this->db->get('users');
        $result = $query->row_array();
        $passwordVerify = false;
        if (password_verify($val['password'], $result['password'])) {
            $passwordVerify = true;
        }
        if ($query->num_rows() && $passwordVerify) {
            // If count value >0 then prepared the array
            //ganaret refresh token
            $refreshToken=md5($result['id'].time());
            $refresh_token_time=time()+refresh_token_expire_time;
            $newdata = array(
                'id' => $result['id'],
                'name' => $result['name'],
                'role' => $result['role'],
                'email_verified' => $result['email_verified'],
                'refresh_token' => $refreshToken,
                'refreshTokenExpiredTime' => date('Y-m-d H:i',$refresh_token_time),
                'is_login' => 1
            );
            // last login status update
            $data = array(
                "last_login_time" => date('Y-m-d H:i:s'),
                "refresh_token" => $refreshToken,
                "refresh_token_time" =>$refresh_token_time,
                "is_login" => 1,
            );
            $this->db->where('id', $result['id']);
            $this->db->update("users", $data);
            // set the session value 
            return $newdata;
        } else {
            // only return false when not authentication
            return false;
        }
    }


    /*
     * user logout
     */
    public function userLogout($userId)
    {
        $data = array(
            "is_login" => 0,
        );
        $result = false;
        if ($userId) {
            $this->db->where('id',$userId);
            $this->db->update("users", $data);
            $result = $this->db->affected_rows();
            if ($result) {
                return TRUE;
            }
        } 
		return FALSE;
    }

    /*
     * ChangePassword
     */
    public function changePassword($user_id, $password, $oldPassword)
    {
        $this->db->select('id, email, password');
        $this->db->where('id', $user_id);
        $query = $this->db->get('users');
        $result = $query->row_array();
        $passwordVerify = false;
        if (password_verify($oldPassword, $result['password'])) {
            $passwordVerify = true;
        }

        if ($passwordVerify) {
            // password change
            $data = array(
                "password" => passwordHash($password)
            );
            $this->db->where('id', $result['id']);
            $this->db->update("users", $data);
            return  $this->db->affected_rows();
        } else {
            return false;
        }
    }


    /*
     * forgotPassword
     */
    public function forgotPassword($email, $password)
    {
        $this->db->select('id,email,password');
        $this->db->where('email', $email);
        $query = $this->db->get('users');
        $result = $query->row_array();


        if (isset($result['id']) && $result['id']) {
            // password change
            $data = array(
                "password" => passwordHash($password)
            );
            $this->db->where('id', $result['id']);
            $this->db->update("users", $data);
            return  $this->db->affected_rows();
        } else {
            return false;
        }
    }
    // reset password email link click
    public function resetPassword($token, $password)
    {
        $this->db->select('id,email,password');
        $this->db->where('verified_token', $token);
        $query = $this->db->get('users');
        $result = $query->row_array();


        if (isset($result['id']) && $result['id']) {
            // password change
            $data = array(
                "password" => passwordHash($password)
            );
            $this->db->where('id', $result['id']);
            $this->db->update("users", $data);
            return  $this->db->affected_rows();
        } else {
            return false;
        }
    }
    // chang Password
    public function changPassword($id, $password)
    {
        $this->db->select('id,email,password');
        $this->db->where('id', $id);
        $query = $this->db->get('users');
        $result = $query->row_array();


        if (isset($result['id']) && $result['id']) {
            // password change
            $data = array(
                "password" => passwordHash($password)
            );
            $this->db->where('id', $result['id']);
            $this->db->update("users", $data);
            return  $this->db->affected_rows();
        } else {
            return false;
        }
    }

    public function refresh_token($id)
    {
        if ($id) {
            //ganaret refresh token
            $refreshToken=md5($id.time());
            //  update data
            $data = array(
                "refresh_token" => $refreshToken,
                "refresh_token_time" => time()+refresh_token_expire_time,
            );
            $this->db->where('id', $id);
            $this->db->update("users", $data);
            return $id;
        } else {
            return false;
        }
    }


   



   
}

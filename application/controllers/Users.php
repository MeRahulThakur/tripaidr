<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH . 'libraries/CreatorJwt.php';
require APPPATH.'libraries/REST_Controller.php';
class Users extends REST_Controller
{

    var $data;
    var $perPage;
    public function __construct()
    {
        parent::__construct();
        $this->objOfJwt = new CreatorJwt();
        $this->load->library(array('form_validation'));
        $this->load->model(array('userlogin_model','profile_model','general_model'));
        $this->perPage = 2;
    }
  
    //USER signin
    public function signin_post()
    {
        if (($this->input->post('email'))) {
            $this->form_validation->set_rules('email', 'Username', 'trim|required|min_length[6]');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
            $arr = array();
            $arr['username'] = trim($this->input->post('email'));
            $arr['password'] = $this->input->post('password');
                if ($this->form_validation->run()) {
                    $authentic_user = $this->userlogin_model->user_authentication($arr);
                    if ($authentic_user) {
                        //login success full user name password match
                            $tokenData=$authentic_user;
                            $tokenData['expire_time']=time()+expire_time;
                            $aSuccess=array(
                                'status' => TRUE, 
                                'message' => 'Successfully login',
                                'result'=>$authentic_user,
                                'token' => $this->objOfJwt->GenerateToken($tokenData),
                                'expiredTime' => date('Y-m-d H:i',$tokenData['expire_time'])
                            );
                     
                    } else {
                        $aSuccess=array(
                            'status' => FALSE, 
                            'message' => 'Invalid username or password'
                        );
                       
                    }
                }
                else{

                    $aSuccess = array(
                        'status' => FALSE, // error
                        'message' =>  comma_error(validation_errors()),
                    );

                }
            
        } else {
            $aSuccess=array(
                'status' => FALSE, 
                'message' => 'Invalid method'
            );
            
        }
      
        $this->response($aSuccess, REST_Controller::HTTP_OK);
    }

   

    public function logout_post()
    {
        
        $received_Token = $this->input->request_headers();
        if(isset($received_Token['Token']) && $received_Token['Token'])
        {
             $jwtData = $this->objOfJwt->DecodeToken($received_Token['Token']);
             if(time() < $jwtData['expire_time']){
                $userId=$jwtData['id'];
                $this->userlogin_model->userLogout($userId);
                $return=array(
                    'status' => True, 
                    'message' => 'User logout'
                );
                $this->response($return, REST_Controller::HTTP_OK);
             }else{
                $return=array( "status" => false, "message" => 'Token time out');
                $this->response($return, REST_Controller::HTTP_OK);
             }
        }
        else
            {
            $return=array( "status" => false, "message" => 'Othentication failed');
            $this->response($return, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

   

    // add new user registration
    public function signup_post()
    {
        //echo APPPATH;die;
        $this->form_validation->set_rules('name', 'name', 'trim|required');
        $this->form_validation->set_rules('type', 'type', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|callback_isEmailAvailable');
        //$this->form_validation->set_rules('mobile', 'Mobile number', 'trim|required|min_length[10]|callback_isMobileAvailable');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
        //$this->form_validation->set_rules('cpassword', 'Confirm password', 'trim|required|min_length[6]|matches[password]');

        //check mandatory form element validation
        if ($this->form_validation->run()) {
            //make profile form data for insert into DB
            $password = trim($this->input->post('password'));
            $emailVerify=md5(time().mt_rand(10,100));
            $aProfileData = array(
                'name' => trim($this->input->post('name')),
                'role' => trim($this->input->post('type')),
                'email' => trim($this->input->post('email')),
                'mobile' => trim($this->input->post('mobile')),
                'verified_token' =>$emailVerify,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 1,
                'is_delete' => 0
            );
            //insert user data
            $aSuccess=array();
            $iInsertId = $this->profile_model->addRecord('users', $aProfileData, $password);
            if ($iInsertId) {
                $email=$this->input->post('email');
                $subject='Tripaider verify email'; 
                $content="Click Here <a href=".base_url('emailverify').'/'.$emailVerify.">Click </a>"; 
                $type='html';// optional
                sendGrid($email,$subject,$content,$type);
                $aSuccess = array(
                    'status' => TRUE, // success
                    'message' => 'Successfully user registered. Please sign in'
                );
            }
        } else {
            $aSuccess = array(
                'status' => FALSE, // error
                'message' =>  comma_error(validation_errors())
            );
        }
        $this->response($aSuccess, REST_Controller::HTTP_OK);
    }

    //isEmailAvailable check during registration
    public function isEmailAvailable($email)
    {
        $this->form_validation->set_message('isEmailAvailable', 'Email already exists');
        $result = $this->profile_model->validateEmail($email);
        if (empty($result)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    //isMobileAvailable check during registration
    public function isMobileAvailable($sContactNumber)
    {
        $this->form_validation->set_message('isMobileAvailable', 'Mobile Number already exists');
        $result = $this->profile_model->validateContactNumber($sContactNumber);
        if (empty($result)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

  


    //user forgotpassword from login page
    public function forgotPassword_post()
    {
        $this->form_validation->set_rules('email', 'Email', 'trim|required');
        if ($this->form_validation->run()) {
            $user=$this->general_model->getDetails('users',array('email'=>$this->input->post('email')));
            if ($user['id']) {
                $restPasswordToken=md5(time().mt_rand(10,100));
                $verify_expire_time=time()+verify_expire_time;
                $result=$this->general_model->updateRecord('users',array('email'=>$this->input->post('email')),array('verified_token'=>$restPasswordToken,'verified_token_time'=>$verify_expire_time));
                $email=$this->input->post('email');
                $subject='Tripaider reset password'; 
                $bodyData=array();
                $bodyData['name']=$user['name']; 
                $bodyData['temptext']=base_url('reset-password').'/'.$restPasswordToken; 
                $bodyData['link']=base_url('reset-password').'/'.$restPasswordToken; 
                //mail template
                $content = $this->load->view('emailtemplate/forgot_password.php',$bodyData,TRUE);
                $type='html';// optional
                sendGrid($email,$subject,$content,$type);
                $aSuccess = array(
                    'status' => true, // success
                    'message' => 'Successfully password sent to your registered email'
                );
            } else {
                $aSuccess = array(
                    'status' => false, // error
                    'message' => 'Error ! email address is invalid'
                );
            }
        } else {
            $aSuccess = array(
                'status' => '0', // error
                'message' => 'Validation Error !' . comma_error(validation_errors())
            );
        }
        $this->response($aSuccess, REST_Controller::HTTP_OK);
    }

    //get token
    public function gettoken_post()
    {
        $this->form_validation->set_rules('refreshToken', 'Refresh Token', 'trim|required');
        if ($this->form_validation->run()) {
            $user=$this->general_model->getDetails('users',array('refresh_token'=>$this->input->post('refreshToken')));
            if ($user['id'] && $user['refresh_token_time'] >= time()) {
                $userid=$this->userlogin_model->refresh_token($user['id']);
                $getUser=$this->profile_model->getProfileDetailsById($userid);
                $tokenData = array(
                    'id' => $getUser['id'],
                    'name' => $getUser['name'],
                    'role' => $getUser['role'],
                    'email_verified' => $getUser['email_verified'],
                    'refresh_token' => $getUser['refresh_token'],
                    'is_login' => $getUser['is_login'],
                    'expire_time'=>time()+expire_time
                );
                $aSuccess = array(
                    'status' => true, // success
                    'refresh_token' => $getUser['refresh_token'],
                    'refreshTokenExpiredTime' => date('Y-m-d H:i',$getUser['refresh_token_time']),
                    'token' => $this->objOfJwt->GenerateToken($tokenData),
                    'expiredTime' => date('Y-m-d H:i',$tokenData['expire_time']),
                    'message' => 'Successfully token generate'
                );
            } else {
                $aSuccess = array(
                    'status' => false, // error
                    'message' => 'Error ! Refresh Token is invalid'
                );
            }
        } else {
            $aSuccess = array(
                'status' => '0', // error
                'message' => 'Validation Error !' . comma_error(validation_errors())
            );
        }
        $this->response($aSuccess, REST_Controller::HTTP_OK);
    }

    //user reset password
    public function resetPassword_post($token=false)
    {
        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
        $this->form_validation->set_rules('confirmPassword', 'Confirm password', 'trim|required|matches[password]');
        if ($this->form_validation->run()) {
            if($token){
                if($getData=$this->general_model->getDetails('users',array('verified_token'=>$token))){
                    if(isset($getData['verified_token_time']) && $getData['verified_token_time'] >= time()){
                        $result=$this->userlogin_model->resetPassword($token,$this->input->post('password'));
                        $email= $getData['email'];
                        $subject='Tripaider Password change successfully'; 
                        $bodyData=array();
                        $bodyData['name']=$getData['name']; 
                        $bodyData['text']='Your Password change successfully'; 
                        //mail template
                        $content = $this->load->view('emailtemplate/reset_password.php',$bodyData,TRUE);
                        $type='html';// optional
                        sendGrid($email,$subject,$content,$type);
                        $return=array( "status" => true, "message" => 'Password change successfully');
                        $this->response($return, REST_Controller::HTTP_OK);
                    }
                    else{
                        $return=array( "status" => false, "message" => 'sorry is time out please try again');
                        $this->response($return, REST_Controller::HTTP_OK);
                    }
                }
                else{
                    $return=array( "status" => false, "message" => 'Something went wrong');
                    $this->response($return, REST_Controller::HTTP_OK);
                }
            }
            else{
                $return=array( "status" => false, "message" => HTTP_UNAUTHORIZED);
                $this->response($return, REST_Controller::HTTP_UNAUTHORIZED);
            }
        } else {
            $return = array(
                'status' => '0', // error
                'message' => 'Validation Error !' . comma_error(validation_errors()),
            );
        }
        $this->response($return, REST_Controller::HTTP_OK);
    }

    public function profile_get()
    {
        $received_Token = $this->input->request_headers();
        if(isset($received_Token['Token']) && $received_Token['Token'])
        {
             $jwtData = $this->objOfJwt->DecodeToken($received_Token['Token']);
             if(time() < $jwtData['expire_time']){
                $profileData=$this->profile_model->getProfileDetailsById($jwtData['id']);
                unset($profileData['password']);
                unset($profileData['updated_at']);
                $this->response($profileData, REST_Controller::HTTP_OK);
             }else{
                $return=array( "status" => false, "message" => 'Token time out');
                $this->response($return, REST_Controller::HTTP_OK);
             }
        }
        else
            {
            $return=array( "status" => false, "message" => 'Othentication failed');
            $this->response($return, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }
    // user email verify form email link
    public function emailVerifyUser_get($token=false)
    {
        if($token){
            if($this->general_model->getDetails('users',array('verified_token'=>$token))){
                $result=$this->general_model->updateRecord('users',array('verified_token'=>$token),array('email_verified'=>1));
                $return=array( "status" => true, "message" => 'Email verified successfully');
                $this->response($return, REST_Controller::HTTP_OK);
            }
            else{
                $return=array( "status" => false, "message" => 'Something went wrong');
                $this->response($return, REST_Controller::HTTP_OK);
            }
        }
        else{
            $return=array( "status" => false, "message" => HTTP_UNAUTHORIZED);
            $this->response($return, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }

    public function change_password_post()
    {
        $received_Token = $this->input->request_headers();
        if(isset($received_Token['Token']) && $received_Token['Token'])
        {
            $jwtData = $this->objOfJwt->DecodeToken($received_Token['Token']);
            if(time() < $jwtData['expire_time']){
                $this->form_validation->set_rules('oldPassword', 'Old password', 'trim|required');
                $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
                $this->form_validation->set_rules('confirmPassword', 'Confirm password', 'trim|required|matches[password]');
                if ($this->form_validation->run()) {
                        $getData=$this->general_model->getDetails('users',array('id'=>$jwtData['id']));
                        if(password_verify($this->input->post('oldPassword'), $getData['password'])){

                            if(password_verify($this->input->post('password'), $getData['password'])){
                                 $return=array( "status" => false, "message" => 'Should not set the current password as the new password.');
                                $this->response($return, REST_Controller::HTTP_OK);
                            }
                            else{
                                $result=$this->userlogin_model->changPassword($jwtData['id'],$this->input->post('password'));
                                $return=array( "status" => true, "message" => 'Password change successfully');
                                $this->response($return, REST_Controller::HTTP_OK);
                            }
                        }
                        else{
                            $return=array( "status" => false, "message" => 'Old password not match');
                            $this->response($return, REST_Controller::HTTP_OK);
                        }
                } 
                else {
                    $return = array(
                        'status' => '0', // error
                        'message' => 'Validation Error !' .comma_error(validation_errors()),
                    );
                    $this->response($return, REST_Controller::HTTP_OK);
                }
                
            }
            else{
                $return=array( "status" => false, "message" => 'Token time out');
                $this->response($return, REST_Controller::HTTP_OK);
             }
        }
        else
            {
            $return=array( "status" => false, "message" => 'Othentication failed');
            $this->response($return, REST_Controller::HTTP_UNAUTHORIZED);
        }
    }
   

}

<?php

require_once ("Secure.php");

class User extends Secure {

	function __construct() {
        parent::__construct();
		$this->load->helper('encrpt');
		$this->load->library('encrypt');
    }

    function _remap($method, $params = array()) {
 
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $params);
        }

        $directory = getcwd();
        $class_name = get_class($this);
        $this->display_error_log($directory,$class_name,$method);
    }

    private function _init()
	{
		
		$this->output->set_template('default');	
		$this->load->section('header', 'include/header');
		$this->load->section('sidebar', 'include/sidebar');
		$this->load->section('ribbon', 'include/ribbon');
		$this->load->section('footer', 'include/footer');
		$this->load->section('shortcut', 'include/shortcut');

		$this->output->set_common_meta('Users', 'description', 'keyword');
		$this->output->set_meta('author','Randy Rebucas');
		
	}

	function index()
	{

		if ($this->input->is_ajax_request()) 
		{
			$data['module'] = get_class();
			$this->load->view('ajax/users', $data);
        } 
		else
		{
			$this->_init();
		}
	}

	function load_ajax() {
	
		if ($this->input->is_ajax_request()) 
		{	
			$this->load->library('datatables');
	        $isfiltered = $this->input->post('filter');

	        $this->datatables->select("users.id as id, CONCAT(IF(up.lastname != '', up.lastname, ''),',',IF(up.firstname != '', up.firstname, '')) as fullname, username, email, r.role_name as rolename, DATE_FORMAT(users.created, '%M %d, %Y') as created, avatar, DATE_FORMAT(CONCAT(IF(up.bYear != '', up.bYear, ''),'-',IF(up.bMonth != '', up.bMonth, ''),'-',IF(up.bDay != '', up.bDay, '')), '%M %d, %Y') as birthday, address, mobile, blood_type, DATE_FORMAT(users.last_login, '%M %d, %Y') as last_login", false);
	        
			$this->datatables->where('users.deleted', 0);
			$this->datatables->where('users.role_id !=', 82);
			$this->datatables->where('users.license_key', $this->license_id);
			if($isfiltered > 0){
				$this->datatables->where('DATE(created) BETWEEN ' . $this->db->escape($isfiltered) . ' AND ' . $this->db->escape($isfiltered));
			}
			$this->datatables->join('users_profiles as up', 'users.id = up.user_id', 'left', false);
	        $this->datatables->join('users_role as r', 'users.role_id = r.role_id', 'left', false);
	        
	        $this->datatables->from('users');

	        echo $this->datatables->generate('json', 'UTF-8');
    	}else{
	    	$this->session->set_flashdata('alert_error', 'Sorry! Page cannot open by new tab');
            redirect('');
	    }
    }
	
    function view($id = -1){
        if ($this->input->is_ajax_request()) 
		{

			$data['info'] = $this->Patient->get_info($id);
			
			$roles = array('' => 'Select');

			foreach ($this->Role->get_all($this->license_id, 82, 1)->result_array() as $row) {
				$roles[$row['role_id']] = $row['role_name'];
			}
			$data['roles'] = $roles;
		
	        $this->load->view("ajax/users_form", $data);
	    }else{
	    	$this->session->set_flashdata('alert_error', 'Sorry! Page cannot open by new tab');
            redirect('');
	    }
    }
	
	function doSave($id = -1){
		
		$bod = explode('/', $this->input->post('bod'));

		$this->load->library('pass_secured');
		if ($id==-1) {
			$user_data=array(
				'username'      =>$this->input->post('username'),        
				'email'         =>$this->input->post('email'),
				'password'      =>$this->pass_secured->encrypt($this->input->post('password')),
				'role_id'		=>$this->input->post('role_id'),
				'license_key'	=>$this->license_id,
				'last_ip'       =>$this->input->ip_address(),
				'created'       => date('Y-m-d H:i:s')
			);
		} else {
			$user_data=array(
				'username'      =>$this->input->post('username'),        
				'email'         =>$this->input->post('email'),
				'role_id'		=>$this->input->post('role_id')
			);
		}

		$profile_data = array(
			'firstname'		=>$this->input->post('first_name'),
			'mi'			=>$this->input->post('mi'),
			'lastname'		=>$this->input->post('last_name'),
			'bMonth'		=>$bod[1],
			'bDay'			=>$bod[0],
			'bYear'			=>$bod[2],
			'gender'		=>$this->input->post('gender'),
			'blood_type'	=>$this->input->post('blood_type'),
			'home_phone'	=>$this->input->post('home_phone'),
			'mobile'		=>$this->input->post('mobile'),
			'address'		=>$this->input->post('address'),
			'zip'			=>$this->input->post('zip'),
			'city'			=>($this->input->post('city')) ? $this->input->post('city') : $this->config->item('default_city'),
			'state'			=>($this->input->post('state')) ? $this->input->post('state') : $this->config->item('default_state'),
			'country'		=>($this->input->post('country')) ? $this->input->post('country') : $this->config->item('default_country')
		);

		$extend_data = array(
			'other_info'	=>$this->input->post('other_info'),
			'comments'		=>$this->input->post('comments')
		);
		
		if($this->Patient->save($user_data, $profile_data, $extend_data, $id))
		{
			if($id==-1)
			{
				echo json_encode(array('success'=>true,'message'=>$profile_data['lastname']));
			}
			else //previous employee
			{
				echo json_encode(array('success'=>true,'message'=>$profile_data['lastname']));
			}
		}
		else//failure
		{	
			echo json_encode(array('success'=>false,'message'=>$profile_data['lastname']));
		}
			
	}
	
    function doReset($email){
    	if ($this->input->is_ajax_request()) 
		{
	    	//send
			//$email;
			
			echo json_encode(array('success' => true, 'message' => 'Reset link send!'));
	    }else{
	    	$this->session->set_flashdata('alert_error', 'Sorry! Page cannot open by new tab');
            redirect('');
	    }
    }
	
    function delete($user_id){

    	if ($this->users->delete_user($user_id)) {
			echo json_encode(array('success' => true, 'message' => 'User successfully deletd!'));
		} else {
			echo json_encode(array('success' => false, 'message' => 'User cannot be deletd!'));
		}

    }

     function update($id = -1){
    	if ($this->input->is_ajax_request()) 
		{
	    	$data['info'] = $this->Patient->get_profile_info($id);
	        $this->load->view("ajax/users_update", $data);
	    }else{
	    	$this->session->set_flashdata('alert_error', 'Sorry! Page cannot open by new tab');
            redirect('');
	    }
    }

    function details($id = -1){
    	if ($this->input->is_ajax_request()) 
		{
	    	$data['info'] = $this->Patient->get_profile_info($id);
	        $this->load->view("ajax/users_detail", $data);
	    }else{
	    	$this->session->set_flashdata('alert_error', 'Sorry! Page cannot open by new tab');
            redirect('');
	    }
    }
	
}

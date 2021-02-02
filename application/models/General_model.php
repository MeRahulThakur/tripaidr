<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class General_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function pushRecord($tablename = false ,$data=array())
    {
        $this->db->insert($tablename,$data);
        return $this->db->insert_id();
    }
    
    public function pushMultiRecord($tablename = false ,$data=array())
    {
		$this->db->insert_batch($tablename, $data);
        return $this->db->insert_id();
    }
	
    public function updateRecord($tablename = false ,$where_clause = array(), $data=array())
    {
      
        foreach($where_clause as $key=>$val){
                $this->db->where_in($key,$val);
        }
        $this->db->update($tablename,$data); 
        return $this->db->affected_rows();
    }
    
    public function deleteRecord($tablename = false,$where_clause = array())
    {
			
		foreach($where_clause as $key=>$val)
        {
			$this->db->where_in($key,$val);
        }
		$this->db->delete($tablename); 
      return true;
    }
    
    public function getDetails($tablename = FALSE, $where_clause = FALSE) {
        $this->db->select('*');
		if($where_clause)
			$this->db->where($where_clause);
        $this->db->from($tablename);
        $query =  $this->db->get();
        $row = $query->row_array();
        return $row;
    }

    public function getRecords($tablename = FALSE, $where_clause = FALSE) {
        $this->db->select('*');
		if($where_clause)
			$this->db->where($where_clause);
        $this->db->from($tablename);
        $query =  $this->db->get();
        $result = $query->result_array();
        return $result;
    }
	
	public function countRecords($where_clause = array(), $fieldname = false, $table_name = false, $like = array())
    {
		$this->db->select("count(".$fieldname.") as total");
		$this->db->from($table_name);
		if($like)			
			$this->db->like($like);
        if($where_clause)
        {
            foreach($where_clause as $key=>$val){
                $this->db->where_in($key,$val);
            }
        }
		
        $query = $this->db->get();
        $row = $query->row_array();
		return $row['total'];    
    }
	
	public function getAllRecords($perpage = NULL, $from = 0 , $tablename = false, $where_clause = array(), $like = array())
    {
        $this->db->select("*");
		if($like)			
			$this->db->like($like);
        if ($perpage !== NULL) {
            $this->db->limit($perpage, $from);
        }
        if($where_clause)
        {
            foreach($where_clause as $key=>$val){
				$this->db->where_in($key,$val);
			}
        }
        $this->db->from($tablename);
        $query = $this->db->get();
        $result = $query->result_array();
		return $result;

    }

    
}
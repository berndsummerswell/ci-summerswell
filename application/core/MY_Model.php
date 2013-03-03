<?php
/**
 * -----------------------------------------------------------------------------
 * MY_Model : enhancement of standard CI_Model class
 * 
 * Adds basic DB functions. This class will be overwritten 
 * by the individual DB tables models.
 * 
 * @author berndvf
 * -----------------------------------------------------------------------------
 */
class MY_Model extends CI_Model {  
  
  /**
   * $base_table contains the table in the database that will be used
   * automatically in the functions below.
   * @var String 
   */
  public $base_table = "";
  /**
   * $id_field is the name of the Identifier field for the above table. It is
   * used automatically in the functions below.
   * @var String
   */
  public $id_field   = "id";
  
  /**
   * Default Constructor
   * The $base_table will be set automatically to the first URI segment. This
   * can be overridden in the constructor of the descendant class.
   * For example if the URI was /user/login, then the base_table would be set
   * to "user"
   */
  public function __construct() {
    parent::__construct();
    $this->base_table = $this->uri->rsegment(1);
  }
  
  /**
   * This will return one record array from the DB, based on the ID value passed in
   * @param Int $id_value
   * @return Array
   */
  function get_one( $id_value ) {
    return $this->db->get_where( $this->base_table, array( $this->id_field => $id_value ) )->row_array();
  }
  
  /**
   * This will retrieve all records in the table, optionally ordered by a field
   * @param String $orderby
   * @return Array
   */
  function get_all( $orderby = "id" ) {
    return $this->db->order_by( $orderby )->get( $this->base_table )->result_array();
  }
  
  /**
   * This will return a filtered list of records, optionally ordered by a field 
   * @param String/Array $filter
   * @param String $orderby
   * @return Array
   */
  function get_filtered( $filter, $orderby = "id" ) {
    return $this->db->where( $filter )
                    ->order_by( $orderby )
                    ->get( $this->base_table )
                    ->result_array();
  }
  
  /**
   * This will add a record into the DB table. Validation should be done 
   * before this point.
   * @param Array $data
   */
  function add( $data ) {
    $this->db->insert( $this->base_table, $data );
  }
  
  /**
   * This will add a record into the DB table and return the resultant ID field.
   * @param Array $data
   * @return Int
   */
  function add_id( $data ) {
    $this->db->insert( $this->base_table, $data );
    return $this->db->insert_id();
  }
  
  /**
   * This will update a record in the DB with the supplied $data Array,
   * @param Int $id_value
   * @param Array $data
   */
  function update( $id_value, $data ) {
    $this->db->update( $this->base_table,
                       $data,
                       array( $this->id_field => $id_value ) );
  }
  
  /**
   * This will delete a DB record based on the supplied identifier value.
   * @param type $id_value
   */
  function delete( $id_value ) {
    $this->db->delete( $this->base_table,
                       array( $this->id_field => $id_value ) );
  }
  
  
  
}

?>

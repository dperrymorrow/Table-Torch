<?php 

if (! defined('BASEPATH')) exit('No direct script access');

class Table_torch {
	
	public $dilem = "::";
	public $settings = array();
	public $CI;
	public $load_prefix = "";

	
	public $url_vals = array( 	'search_field'=>'', 
								'keyword'=>'', 
								'sort_field'=>'', 
								'sort_dir'=>'', 
								'table'=>'', 
								'action'=>'', 
								'key'=>'' );

	//php 5 constructor
	function __construct() {
		
		$this->CI = &get_instance();
		$this->CI->load->config( 'table_torch' );
		define( 'PARAM_DILEM', $this->dilem );
		$this->load_prefix = dirname(__FILE__).DIRECTORY_SEPARATOR;
		$this->CI->load->library( array( 'table', 'pagination', 'security' ));
		$this->CI->load->database();
		$this->CI->table->set_template( $this->CI->config->item( 'table_torch_table_formatting') );

	}
	
	function route(){

		$this->_disect_url();
		$redirect = FALSE;

		if( empty( $this->url_vals[ 'table' ] )){

			$tables = $this->CI->config->item( 'table_torch_tables' );
			$this->url_vals[ 'table' ] = key( $tables );

			$this->url_vals[ 'action' ] = 'listing';
			$redirect = TRUE;
		}
		
		define( 'TORCH_METHOD', $this->url_vals[ 'action' ] );
		define( 'TORCH_TABLE', $this->url_vals[ 'table' ] );
		define( 'TORCH_KEY', $this->url_vals[ 'key' ] );
		
		$this->_check_table();
		$this->CI->table_torch_model->define_primary_key();

		if( $redirect ){
			redirect( torch_url( $this->url_vals, FALSE ));
		}else{
			$method = TORCH_METHOD;
			$this->$method();
		}
	}


	function listing(){

		$config = $this->CI->config->item( 'table_torch_pagination_settings' );
		$config['base_url'] = torch_url( $this->url_vals );
		$config['total_rows'] = $this->CI->table_torch_model->get_count( $this->url_vals ); 
		$config['uri_segment' ] = $this->CI->uri->total_segments();


		$this->CI->pagination->initialize($config);

		$data[ 'table' ] = TORCH_TABLE;
		$data[ 'total_count' ] = $config[ 'total_rows' ];
		$data[ 'rows' ] = $this->_table_data( $this->CI->uri->segment( $config[ 'uri_segment'] ) );
		$data[ 'tables' ] = $this->CI->config->item( 'table_torch_torch_tables' );
		$data[ 'url_params' ] = $this->url_vals;
		$data[ 'options' ] = $this->_field_options();
		
		$tbl_settings = $data[ 'tables' ][ TORCH_TABLE ];
		
		if( isset( $tbl_settings[ 'add' ] )){
			$data[ 'add' ] = $tbl_settings[ 'add' ];
		}else{
			$data[ 'add' ] = FALSE;
		}

		if( method_exists( $this->CI, TORCH_TABLE."_listing" )){
			$method = TORCH_TABLE."_listing";
			$this->CI->$method( $data );
		}else{
			$this->load_view( 'listing', $data, TRUE );
		}
		
	}
	
	function add(){
		
		
		$data[ 'tables' ] = $this->CI->config->item( 'table_torch_tables' );
		$data[ 'table' ] = TORCH_TABLE;
		$data[ 'desc' ] = $this->CI->table_torch_model->describe_table();
		$data[ 'row' ] = NULL;
		
		if( method_exists( $this->CI, TORCH_TABLE."_add" )){
			$method = TORCH_TABLE."_add";
			$this->CI->$method( $data );
		}else{
			$this->load_view( 'form', $data, TRUE );
		}
	}
	


	
	function edit(){
		
		$data[ 'desc' ] = $this->CI->table_torch_model->describe_table();
		$data[ 'table' ] = TORCH_TABLE;
		$data[ 'row' ] = $this->CI->table_torch_model->get_by_key();
		$data[ 'tables' ] = $this->CI->config->item( 'table_torch_tables' );
		
		if( method_exists( $this->CI, TORCH_TABLE."_edit" )){
			$method = TORCH_TABLE."_edit";
			$this->CI->$method( $data );
		}else{
			$this->load_view( 'form', $data, TRUE );
		}
		
		

	}
	
	/// FORM ACTIONS /////
	
	function insert(){
		
		$data = $this->CI->table_torch_model->prep_data();
		
		if( method_exists( $this->CI, 'before_insert' ) ){
			$data = $this->CI->before_insert( TORCH_TABLE, $data );
		}
		
		$data[ 'id' ] = $this->CI->table_torch_model->insert( $data );
		
		if( method_exists( $this->CI, 'after_insert' ) ){
			$this->CI->after_insert( TORCH_TABLE, $data );
		}
		
		if( isset($_POST[ 'refer'])){
			redirect( $_POST[ 'refer' ] );
		}else{
			redirect( torch_url( array( 'table'=>TORCH_TABLE, 'action'=>'listing' ), FALSE ) );
		}
		
	}
	
	function search(){
		
		foreach ($this->url_vals as $key => $value) {
			if( isset( $_POST[ $key ] )){
				$this->url_vals[ $key ] = $_POST[ $key ];
			}
		}
		
		redirect( torch_url( $this->url_vals, FALSE ) );
		
	}
	
	function delete(){
		
		if( method_exists( $this->CI, 'before_delete' ) ){
			$this->CI->before_delete( TORCH_TABLE, TORCH_KEY );
		}
		
		$this->CI->table_torch_model->delete();
		
		if( method_exists( $this->CI, 'after_delete' ) ){
			$this->CI->after_delete( TORCH_TABLE, TORCH_KEY );
		}
		
		redirect( torch_url( array( 'table'=>TORCH_TABLE, 'action'=>'listing' ), FALSE ) );
	}
	
	function update(){

		$data = $this->CI->table_torch_model->prep_data();
		
		if( method_exists( $this->CI, 'before_update' ) ){
			$d = $data;
			$d[ PRIMARY_KEY ] = $_POST[ PRIMARY_KEY ];
			$data = $this->CI->before_update( TORCH_TABLE, $d );
			unset( $data[ PRIMARY_KEY ] ); 
		}
		
		$this->CI->table_torch_model->update( $data );
		
		if( method_exists( $this->CI, 'after_update' ) ){
			$this->CI->after_update( TORCH_TABLE, $d );
		}

		if( isset($_POST[ 'refer'])){
			redirect( $_POST[ 'refer' ] );
		}else{
			redirect( torch_url( array( 'table'=>TORCH_TABLE, 'action'=>'listing' ), FALSE ) );
		}

	}

	public function load_view( $view_file, $data, $torch_view_dir=FALSE ){
		

		
		if( $torch_view_dir or $this->CI->config->item( 'table_torch_template_in_torch_dir' ) ){
			$this->CI->load->add_package_path($this->load_prefix .'views/');
		}
		
		$data[ 'contents' ] = $this->CI->load->view( $view_file, $data, TRUE );
		$this->CI->load->view( $this->CI->config->item( 'table_torch_template_file' ), $data );
		
		$this->CI->load->remove_package_path($this->load_prefix .'views/');
	}



	protected function _check_table(){

		if( TORCH_TABLE == null ){
			show_error( $this->CI->lang->line( 'table_torch_table_not_specified' ) );
		}

		$tables = $this->CI->config->item( 'table_torch_tables' );

		if( !isset($tables[ TORCH_TABLE ])){
			show_error( $this->CI->lang->line( 'table_torch_table_not_in_config' ) );
		}
	}



	protected function _table_data( $offset=0 ){


		$tables = $this->CI->config->item( 'table_torch_tables' );
		$prefs = $tables[ TORCH_TABLE ];
		$humanize = $this->CI->config->item( 'table_torch_humanize_fields' );
		
		$paginate_prefs = $this->CI->config->item( 'table_torch_pagination_settings' );
		$limit = $paginate_prefs[ 'per_page' ];
		$funct = $this->CI->config->item( 'table_torch_function' );
		
		$rows = $this->CI->table_torch_model->get_listing( $this->url_vals, $limit, $offset );

		for ($i=0; $i < count( $rows ); $i++) { 
			$row = $rows[ $i ];
			$actions = '';

			if( $prefs[ 'edit' ] ){
				$actions .= anchor( torch_url(	array( 	'action'=>'edit',
														'table'=>TORCH_TABLE,
														'key'=>$row[ PRIMARY_KEY ] ), FALSE ),
											 
									'Edit', array( 'class'=>'actionLink', 'id'=>'editLink') );
			}

			if( $prefs[ 'delete' ] ){
				$confirm = $this->CI->lang->line( 'table_torch_delete_confirm' );
				$actions .= anchor( torch_url( array( 	'action'=>'delete',
														'table'=>TORCH_TABLE,
														'key'=>$row[ PRIMARY_KEY ]), FALSE ), 
									'Delete', array( 'onclick'=>"return confirm('$confirm')", 'class'=>'actionLink' ) );
			}

			$tmp[ 'actions' ] = $actions;
			foreach ($rows[ $i ] as $key => $value ){ 
				
				
				if( !empty( $prefs[ 'formats' ][ $key ])) {
					$value = $prefs[ 'formats' ][ $key ]( $value, $key, TORCH_TABLE );

				} elseif( !empty( $funct )){
					$value = $funct( $value );
				}
				
				$tmp[ $key ] = $value; 
				
			}
			$rows[ $i ] = $tmp;
		}

		$headers[ 0 ] = $this->CI->lang->line( 'table_torch_actions' );

		$desc = $this->CI->table_torch_model->describe_table();
		$org_vals = $this->url_vals;

		foreach ($desc as $row ){
			$class = '';
			$prefix = '';
			
			if( $org_vals[ 'sort_field' ] == $row[ 'Field' ] ){
				
				if(  $org_vals[ 'sort_dir' ] == 'ASC' ){
					$class = 'desc';
					$prefix = '&#9660;&nbsp;';
					
					$this->url_vals[ 'sort_dir' ] = 'DESC';
				}else{
					$class = 'asc';
					$prefix = '&#9650;&nbsp;';
					$this->url_vals[ 'sort_dir' ] = 'ASC';
				}
			} else{
				$this->url_vals[ 'sort_dir' ] = 'ASC';
			}
			
			$this->url_vals[ 'sort_field' ] = $row[ 'Field' ];
			$fieldname = $row[ 'Field' ];
			
			if( $humanize ){
				$fieldname = humanize( $fieldname );
			}
			array_push( $headers, anchor( torch_url( $this->url_vals, FALSE ), $prefix . $fieldname, array( 'class'=>$class ))); 
		}
		
		$this->url_vals = $org_vals;
		$this->CI->table->set_heading( $headers );
		return $rows;

	}
	
	protected function _field_options(){
		
		$fields = $this->CI->table_torch_model->describe_table();
		$options = array();
		foreach ($fields as $field ) {
			$options[ $field[ 'Field' ] ] = $field[ 'Field' ];
		}
		
		return $options;
	}
	
	
	protected function _disect_url(){
		
		$segs = $this->CI->uri->segment_array();

		foreach( $segs as $segment){
			
			if( strpos( $segment, PARAM_DILEM ) !== FALSE ){
				$arr = explode( PARAM_DILEM, $segment );
				$this->url_vals[ $arr[ 0 ] ] = $arr[ 1 ];
			}
		}
	}

	

}
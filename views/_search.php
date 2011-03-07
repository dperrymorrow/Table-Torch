<?php
echo form_open( torch_url( array( 'action'=>'search', 'table'=>TORCH_TABLE ), FALSE ), array( 'id'=>'searchForm' ) );
echo form_hidden( 'table', TORCH_TABLE );

foreach ( $url_params as $key => $value) {
	if( !empty( $value )){
		echo form_hidden( $key, $value );
	}
}

echo form_dropdown( 'search_field', $options, $url_params[ 'search_field'] );
echo form_input( 'keyword', $url_params[ 'keyword' ] );
echo form_submit( 'submit', $this->lang->line( 'table_torch_search' ) );
echo anchor( torch_url( array( 'action'=>'listing', 'table'=>TORCH_TABLE ), FALSE ), $this->lang->line( 'table_torch_clear_search') );
echo form_close();
?>

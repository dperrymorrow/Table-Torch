<?php

function table_torch_title( $table ){

	$CI = &get_instance();
	
	
	if( $CI->router->fetch_method() == 'listing' ){
		$singular=FALSE;
	}else{
		$singular = TRUE;
	}

	$str = humanize( $table );
	if( $singular == TRUE ){
		return singular( $str );
	}else{
		return $str;
	}
}

function torch_url( $params, $prepend_base=TRUE ){
	
	$CI = &get_instance();
	$segs = $CI->uri->segment_array();
	
	
	$base = '';
	$torch_url = '';
	foreach ($segs as $seg) {
		if( strpos( $seg, PARAM_DILEM ) === FALSE ){
			$base .= $seg.'/';
		}else{
			break;
		}
	}
	
	if( substr_count( $base, '/' ) != 2 ){
		$base = $CI->router->fetch_class() . '/' . $CI->router->fetch_method() .'/';
	}
	
	foreach ($params as $key => $value) {
		if( !empty( $value )){
			$torch_url .= $key.PARAM_DILEM.$value.'/';
		}
	}
	
	
	if( $prepend_base ){
		$url = site_url( $base . $torch_url);
	}else{
		$url =	$base . $torch_url;
	}
	
	return $url;
	
} 

function table_torch_nav(){
	
	
	$CI = &get_instance();
	$tables = $CI->config->item( 'table_torch_tables' );
	$prefs = $tables[ TORCH_TABLE ];
	
	$extra_links = $CI->config->item( 'table_torch_extra_nav_links' );
	if( isset( $_SERVER['HTTP_REFERER'] )){
		$refer = $_SERVER['HTTP_REFERER'];
	}else{
		$refer = torch_url( array() );
	}
	
	$str = "<ul id=\"navHeader\">\n";
	
	
	if( TORCH_METHOD == 'edit' or  TORCH_METHOD == 'add' ){
		$str .= "<li class=\"backLink\"><a href=\"$refer\">" . $CI->lang->line( 'table_torch_back_to_listing' ) ."</a></li>\n";
	}else if( TORCH_METHOD == 'listing' and $prefs[ 'add' ] == TRUE ){
		$str .= "<li class=\"backLink\">\n" . anchor( torch_url( array( 'table'=>TORCH_TABLE, 'action'=>'add' ), FALSE ), $CI->lang->line( 'table_torch_add_new_link' )) ."</li>\n";
	}
	
	foreach ( $tables as $key => $table ){
		if( $key == TORCH_TABLE ){
			$class = 'active';
		}else{
			$class = '';
		}
		$label = ucwords( plural( table_torch_title( $key ) ));
		$url = torch_url( array( 'table'=>$key, 'action'=>'listing' ) );
		$str .= "<li><a href=\"$url\" class=\"$class\">$label</a></li>\n";
	}
	
	foreach ($extra_links as $url => $label) {
		$str .= "<li>".anchor( $url, $label )."</li>\n";
	}
	
	return $str ."\n</ul>\n";
}


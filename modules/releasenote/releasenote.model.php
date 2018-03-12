<?php
/* Copyright (C) NAVER <http://www.navercorp.com> */
/**
 * @class  tagModel
 * @author NAVER (developers@xpressengine.com)
 * @brief tag model class of the module
 */
class releasenoteModel extends releasenote
{
	/**
	 * @brief Initialization
	 */
	 
	 public function triggerModuleListInSitemap( &$arr){
		array_push( $arr , 'releasenote');
	}
	 
	//static $mem_list;
	function init()
	{
		if( !$mem_list )
		{
			$output = getMemberList($args );
			$mem_list = $output->data ;
		}	
	}
	function getMemberNameBySrl ( $member_srl , &$mem_list  )
	{	
		if( !$member_srl ) return "";
		$args->member_srl = $member_srl ;
		return $this->getMemberName( $args, $mem_list ); 
	}
	function getMemberName( $args , &$mem_list )
	{
		foreach( $mem_list as $key=> $val)
		{
			if( $val->member_srl == $args->member_srl  ){ 
				return $val->user_name ;
			}
		}
		return $args->member_srl ; // 없으면 그냥 숫자를 보낸다. 
	}
	function getMemberList( $args )
	{
		$output = executeQuery('releasenote.getMemberInfos', $args );
		return $output->data;
	}
	
	function getMantisByStatus( $args ){
		// 80 번은 resolved , 90 closed 
		if(!$args->status)  $args->status = 80 ;
		if(!$args->note_srl )  $args->note_srl = 0 ;
		if(!$args->page_size )  $args->page_size = 200 ;
		$query_str->query_head   = " select  *" ;
		$query_str->querystr     = " FROM xe_view_mantis  v ";
		$query_str->querystr    .= " left outer join xe_releasenote_mantis_id m ON( v.id =m.mantis_id and m.note_srl = %d ) ";
		$query_str->querystr    .= " WHERE status  = %d   and ( m.mantis_id is null || id <> m.mantis_id )  and ( m.note_srl is null   || m.note_srl <> %d ) ";
		
		$query_str->querystr = sprintf( $query_str->querystr , $args->note_srl  , $args->status , $args->note_srl ) ;
		return $this->getExecuteQuery($args , $query_str );
	}
	
	function getExecuteQuery($args  , $query_str){
		// 이건 중앙에서 만든 모듈만 뽑는다. 즉 m.site_srl = 0 인것만 뽑는다. 
		//  m.site_srl = 0  이 아닌것들은   각 카페에서 만든 리스트 이다. 
	
		if( !$args->page)  $args->page=1;
        if( !$args->page_size ) $args->page_size = 20 ; 
		if( !$args->site_srl )  $args->site_srl = 0 ; 
		
		if(!$query_str->count_head)$query_str->count_head ="       select count(0) as count ";
		$query_str->query_tail .=" limit  " . ($args->page -1) * $args->page_size ." , " . $args->page * $args->page_size  ;
	
		$oDB   = &DB::getInstance();
		$query =$oDB->_query($query_str->query_head . $query_str->querystr . $query_str->query_tail  );	

		$result = $oDB->_fetch( $query );
		$output -> data = $result ;
		if(  is_array ($result)  ) {
			$output -> data = $result ;
		}else {
			$arr = array();
			$arr[0] = $result ;
			$output -> data = $arr ;
		}
		$query= $oDB->_query( $query_str->count_head . $query_str->querystr ) ;
		$result = $oDB->_fetch($query);
		
		$output->query        = $query_str->query_head . $query_str->querystr . $query_str->query_tail ;
		$output->page 		 = $args->page ;
		$output->total_count = $result->count; 
		$output->total_page  = round (  $output->total_count  / $args->page_size)  +1 ;
		$output->page_size  = $args->page_size ;
		$oPageHandler = new PageHandler(  $output->total_count,  $output->total_page   ,  $output->page );
		$output -> page_navigation = $oPageHandler ;
	
		return $output ;
	}
	
	function saveComment($args)
	{
		$oDB = &DB::getInstance();

		$args->comment = str_replace( "'" ,"''" , $args->comment ) ;
		$args->comment = str_replace( "<script>" ,"" , $args->comment ) ;
		
		$query_str = sprintf( "update xe_releasenote set comment = concat( ifnull(comment ,'') , '%s')   where note_srl =%d" , $args->comment , $args->note_srl   );
		$query = $oDB->_query( $query_str);
		
		$result = $oDB->_fetch($query) ;
		return $query ;
	}

}/* End of file tag.model.php */
/* Location: ./modules/tag/tag.model.php */
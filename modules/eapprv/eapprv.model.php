<?php
class eapprvModel extends eapprv
{
	
	
	public function triggerModuleListInSitemap( &$arr){
		array_push( $arr , 'eapprv');
	}
	
	/*
	* 사용자 별로 결재 라인 정보를 가져 옵니다. 
	*/
	function getEapprvLines(  $args  )
	{
		/**
			$eapprv_line_srl  , $member_srl 두가지 정보가 필요합니다.   1번 arg는 없으면 사용자 라인 전체를 가져 오죠. 
		*/
		
		//debugPrint( $args );
		$output = executeQuery('eapprv.getEapprvLines', $args  );
		//debugPrint(    $output    );
		
		if(  is_array ($output)  ) {
			
		}else {
			$arr = array();
			$arr[0] = $output->data ;
			$output->data = $arr ;
		}
		
		
		return  $output  ;
	}
	

	/*
	*@ 결재 진행중인 Document 리스트    P 진행중     ,  R eject      ,   A pproved , Not Yet 
	*/		
	function getDocumentList( $args ) {
				 
	//		  and ( `e`.`member_srl` = 465 or `p`.`member_srl` = 465 or `s`.`member_srl` = 465 ) 
	
	// 이건 중앙에서 만든 모듈만 뽑는다. 즉 m.site_srl = 0 인것만 뽑는다. 
		//  m.site_srl = 0  이 아닌것들은   각 카페에서 만든 리스트 이다. 

		if( !$args->page)  $args->page=1;
        if( !$args->page_size ) $args->page_size = 20 ; 
		if( !$args->site_srl )  $args->site_srl = 0 ; 
		if( $args->sort )  $args->sort = "e.regdate  " ; 
		if( !$args->sort )  $args->sort = "e.eapprv_srl  " ; 
		if( !$args->sort_order )  $args->sort_order = " desc  " ; 
		//if( $args->sort_order )  $args->sort_order = " asc  " ; 
				

		if( $args->search_keyword)  $search_keyword = " and  (d.title like '%" . mb_substr( $args->search_keyword , 0, 8 ,'UTF-8') ."%' or d.content like '%".mb_substr( $args->search_keyword , 0, 8 ,'UTF-8')."%' )";
		if( $args->search_member_srl) $search_keyword .= " and e.member_srl = " .$args->search_member_srl;
//debugPrint($args);
		if( !$args->type ) $args->type = __AUTH_OWNER__;
		$distinct ="";
		if(1)//if( $args->type== __AUTH_LINE__  ) //결재 권한인 경우 
		{ $distinct =" distinct " ;}
		

		//if( $args->type ==  __AUTH_PROCESSING__ ){ $processing = " and p.stt<>3 " ;}
		
		$query_head = " SELECT ".$distinct." `d`.`title`, `d`.comment_count , `e`.*  , `m`.*  , `mod`.mid";
		if($args->type == __AUTH_LINE__) $query_head .=" ,`p`.line_stt as proc_stt ";
		$count_head = " select count(distinct e.eapprv_srl) as count ";

		$querystr .= " FROM  `xe_eapprv` as `e`  ";
        $querystr .= "    left join  `xe_documents` as `d`  On( d.document_srl = e.document_srl ) " ;
		$querystr .= "    left join  `xe_member` as `m`  On( e.member_srl = m.member_srl ) " ;
		$querystr .= "    left join  `xe_modules` as `mod`  On( e.module_srl = mod.module_srl ) " ;
		if($args->type == __AUTH_LINE__)  $querystr .= "     left join  `xe_eapprv_line` as `p`   On (e.eapprv_srl  = p.eapprv_srl and  `p`.`member_srl` = ".$args->member_srl ." and p.stt = '".__STT_ACCEPT__."' ) " ;
		if($args->type == __AUTH_COMPLETE__ || $args->type==__AUTH_PROCESSING__ ) $querystr .= "     left join  `xe_eapprv_line` as `p`   On (e.eapprv_srl  = p.eapprv_srl and  `p`.`member_srl` = ".$args->member_srl . $processing . " ) " ;
		if($args->type == __AUTH_SHARE__) $querystr .= "    left outer join  `xe_eapprv_share` as `s`  On (e.eapprv_srl = s.eapprv_srl  and  `s`.`member_srl` = ".$args->member_srl.") " ;
		$querystr .= " WHERE 1=1 ";
		if($args->module_srl) 					$querystr .= "    and `e`.module_srl = " . $args->module_srl ;
		if($args->type == __AUTH_OWNER__) 		$querystr .= "    and `e`.`member_srl` = ".$args->member_srl."  " ; 
		if($args->type == __AUTH_COMPLETE__) 	$querystr .= "    and `p`.`member_srl` = ".$args->member_srl." and e.stt_int =" . __STT_OK__ ; 
		if($args->type == __AUTH_LINE__  )  	$querystr .= "    and `p`.`member_srl` = ".$args->member_srl." and e.stt_int = " .__STT_ACCEPT__  ; 
		if($args->type == __AUTH_PROCESSING__) $querystr .= "    and `p`.`member_srl` = ".$args->member_srl." and e.stt_int = " .__STT_ACCEPT__  ; 
		if($args->type == __AUTH_SHARE__) 		$querystr .= "    and `s`.`member_srl` = ".$args->member_srl."  ";// and e.stt_int <> ".__STT_REJECT__ ; 
		
		
		if($args->stt) $querystr .= " `e`.`stt` = '".$args->stt."' " ;
		//$querystr .= " and  ;
		$querystr .= $search_keyword;

		$query_tail = " order by ".$args->sort .$args->sort_order;
		if($args->type == __AUTH_COMPLETE__  ||$args->type == __AUTH_OWNER__ ||$args->type ==__AUTH_SHARE__ ) $query_tail .="  limit  " . ($args->page -1) * $args->page_size ." , " . $args->page * $args->page_size  ;

		$oDB = &DB::getInstance();
		$query=$oDB->_query($query_head . $querystr . $query_tail  );	
		$result = $oDB->_fetch( $query );
//debugPrint( $args->type ."##". $query_head . $querystr . $query_tail );
		if(  is_array ($result)  ) {
			$output -> data = $result ;
		}else {
			$arr = array();
			$arr[0] = $result ;
			$output->data = $arr ;
		}

		$query= $oDB->_query( $count_head . $querystr ) ;

		$result = $oDB->_fetch($query);
		$output->page 		 = $args->page ;
		$output->total_count = $result->count?$result->count:0; 
		$output->total_page  = ceil ($result->count  / $args->page_size)   ;
		$output->page_size   = $args->page_size ;
		$oPageHandler 		 = new PageHandler(  $output->total_count,  $output->total_page   ,  $output->page );
		$output -> page_navigation = $oPageHandler ;
//debugPrint( $output );
		return $output ;
		//return executeQuery('eapprv.getOnProcDocList' , $args ) ;
	}	
	
	function getDocumentForWidgetOnlyRecent10(){
		/*
		select distinct e.eapprv_srl  , e.document_srl , d.title , d.content , e.stt1 , e.stt2 , e.stt3
from xe_eapprv e 
left join xe_documents d ON( e.document_srl  = e.document_srl )
left join xe_eapprv_line l ON ( e.eapprv_srl = l.eapprv_srl and l.member_srl = 4) 
order by e.regdate desc limit 0,10
		*/
		return $output ;
	}
}

?>
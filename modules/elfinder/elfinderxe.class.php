<?
/**
* @calss elfinderxe
 * @author SeungJun Lee(TUW) (temflic@gmail.com)
* @brief elFinderXE File Manager class
**/

class elfinderxe extends ModuleObject
{
	// 모듈 검색 옵션
	var $search_option = array(
		'title',
		'content',
		'title_content',
		'comment',
		'user_name',
		'nick_name',
		'user_id',
		'tag'
	);

	// 모듈 정렬 방법
	var $order_target = array(
		'list_order',
		'update_order',
		'regdate',
		'voted_count',
		'blamed_count',
		'readed_count',
		'comment_count',
		'title'
	);

	var $skin = "default";		// 스킨 이름
	var $list_count = 15;		// 한 페이지에 표시될 모듈의 갯수
	var $page_count = 10;		// 한번에 표시할 페이지의 갯수
	var $category_list = NULL;	// 카테고리 목록
	
	function moduleInstall()
	{	// 모듈 설치
		
	}

	function checkUpdate()
	{	// 모듈 업데이트 확인
	
	}

	function moduleUpdate()
	{	// 모듈 업데이트 진행
	
	}

	function moduleUninstall()
	{	// 모듈 삭제
		$output = executeQueryArray('elfinderxe.getAllElfinderxe');
		if(!$output->data)	return new Object();
		set_time_limit(0);
		$oModuleController = &getColtroller('module');
		foreach($output->data as $elfinderxe)
		{
			$oModuleController->deleteModule($elfinderxe->module_srl);
		}
		return new Object();
	}

	function recompileCache()
	{	// 캐시파일 재생성
	
	}	
}
	
?>
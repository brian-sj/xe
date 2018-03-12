<?php
    /**
     * @class  전자 결재
     * @author 강명희 brian
     * @brief  전자 결재를 의미한다.
     **/
			

			
					
    class eapprv extends ModuleObject {

		//var $search_option = array('title','content','title_content','comment','user_name','nick_name','user_id','tag'); ///< 검색 옵션
        //var $order_target = array('list_order', 'update_order', 'regdate', 'voted_count', 'blamed_count', 'readed_count', 'comment_count', 'title'); // 정렬 옵션
		var $status_code  = array( __STT_ACCEPT__ => "진행" , __STT_OK__ => "승인" ,  __STT_REJECT__ => "반려"  , __STT_READY__ => "-" );
		var $status_label = array( __AUTH_SHARE__ => "참조" , __AUTH_COMPLETE__ => "완료" , __AUTH_OWNER__ => "내 문서" , __AUTH_PROCESSING__ => "진행") ;

        var $skin = "default"; ///< skin name
        var $list_count = 20; ///< the number of documents displayed in a page
        var $page_count = 10; ///< page number
        var $category_list = NULL; ///< category list
		
		
		private $triggers = array(
			array(
				'name' => 'menu.getModuleListInSitemap' ,
				'module' => 'eapprv',
				'type' => 'model',
				'func' => 'triggerModuleListInSitemap',
				'position' => 'after',
			)
		);
		
		
		
		function __construct()	{

		}
		
        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
		 
        function moduleInstall() {
			// use action forward(enabled in the admin model)
			$oModuleController = getController('module');
			//$oModuleController->insertTrigger('member.getMemberMenu', 'board', 'controller', 'triggerMemberMenu', 'after');
			foreach( $this->triggers as $trigger )
			{
				$oModuleController->insertTrigger(
					$trigger['name'],
					$trigger['module'],
					$trigger['type'],
					$trigger['func'],
					$trigger['position']
				);
				
			}
            return new Object();
        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/

		 function checkUpdate() {
            $oModuleModel = &getModel('module');
			/*
            // 2007. 10. 17 get the member menu trigger
            if(!$oModuleModel->getTrigger('member.getMemberMenu', 'eapprv', 'controller', 'triggerMemberMenu', 'after')) return true;

            // 2011. 09. 20 when add new menu in sitemap, custom menu add
            if(!$oModuleModel->getTrigger('menu.getModuleListInSitemap', 'eapprv', 'model', 'triggerModuleListInSitemap', 'after')) return true;
			*/
			
			foreach( $this->triggers as $trigger ){
					$res = $oModuleModel->getTrigger(
									$trigger['name'],
									$trigger['module'],
									$trigger['type'],
									$trigger['func'],
									$trigger['position']
							);
				if(!$res ){
					return true ;
				}
			
			}
		
		return false ;
            //return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
			$oModuleModel = getModel('module');
			$oModuleController = getController('module');
			
			foreach( $this->triggers as $trigger ){
					$res = $oModuleModel->getTrigger(
					$trigger['name'],
					$trigger['module'],
					$trigger['type'],
					$trigger['func'],
					$trigger['position']
				);
				if(!$res ){
					$oModuleController->insertTrigger(
						$trigger['name'],
						$trigger['module'],
						$trigger['type'],
						$trigger['func'],
						$trigger['position']
					);
				}
			}
			
			return new Object(0, 'success_updated');
        }

		function moduleUninstall() {
			$oModuleController = getController('module');		
			foreach( $this->triggers as $trigger ){
					$res = $oModuleController->deleteTrigger(
									$trigger['name'],
									$trigger['module'],
									$trigger['type'],
									$trigger['func'],
									$trigger['position']
							);
			}
			
			return  new Object();
		}
        /**
         * @brief 캐시 파일 재생성
         **/
        function recompileCache() {
        }

    }
?>

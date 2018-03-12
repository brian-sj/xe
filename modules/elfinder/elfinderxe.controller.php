<?
/**
 * @class  elfinderxeController
 * @author SeungJun Lee(TUW) (temflic@gmail.com)
 * @brief  elFinderXE module Controller class
 **/

/* Include connector files */
require_once(_XE_PATH_.'modules/elfinderxe/includes/php/elFinderConnector.class.php');
require_once(_XE_PATH_.'modules/elfinderxe/includes/php/elFinder.class.php');
require_once(_XE_PATH_.'modules/elfinderxe/includes/php/elFinderVolumeDriver.class.php');
require_once(_XE_PATH_.'modules/elfinderxe/includes/php/elFinderVolumeLocalFileSystem.class.php');

class elfinderxeController extends elfinderxe
{
	function init()
	{	/* 초기화 */
		// 
	}

	function procElfinderxeFileManager()
	{	/* elFinder의 Back-end 부분을 담당 */

		// 권한에 따른 백엔드 허용 명령 설정
		$denied_cmd = array();
		if(!$this->grant->fread)
		{	// 파일 열기 및 다운로드 권한이 없는 경우
			array_push($denied_cmd, 'info', 'view', 'open', 'getfile', 'quicklook', 'download');
		}
		if(!$this->grant->fwrite)
		{	// 파일 쓰기 및 업로드 권한이 없는 경우
			array_push($denied_cmd, 'duplicate', 'mkfile', 'upload', 'copy', 'paste');
		}
		if(!$this->grant->fedit)
		{	// 파일 편집 및 삭제 권한이 없는 경우
			array_push($denied_cmd, 'rename', 'cut', 'rm', 'edit', 'resize');
		}
		if(!$this->grant->mkdir)
		{	// 폴더 생성 권한이 없는 경우
			array_push($denied_cmd, 'mkdir');
		}
		if(!$this->grant->archive)
		{	// 압축 권한이 없는 경우
			array_push($denied_cmd, 'archive');
		}
		if(!$this->grant->extract)
		{	// 파일 열기 및 다운로드 권한이 없는 경우
			array_push($denied_cmd, 'extract');
		}

		// ElfinderXE Conntector 시작
		$opts = array(
			'roots' => array (
				array (
					'driver'        => $this->module_info->driver,
					'path'          => $this->module_info->root_path,
					'URL'           => $this->module_info->root_url,
					'alias'			=> $this->module_info->root_alias,
					'tmbSize'		=> (int)$this->module_info->tmb_size,
					'tmbCrop'		=> ($this->module_info->tmb_crop=='CROP')?TRUE:FALSE,
					'tmbBgColor'	=> 'transparent',
					'accessControl' => 'extensionFilter',
					'acceptedName'	=> 'extensionRenameFilter',
					'disabled'		=> $denied_cmd,
				)
			)
		);

		// run elFinder
		$connector = new elFinderConnector(new elFinder($opts));
		$connector->run();
	}
}

/*
*	@brief extensionFilter
*	return true  : 표시하지 않음
*	return false : 표시함(접근은 불가능함)
*	return null  : 판단 유보(필터링되지 않은 경우 - elFinder 설정에 따름)
*/
function extensionFilter($attr, $path, $data, $volume)
{	/* 확장자 필터 : 파일 목록 */
	// 파일명 추출
	$name	= basename($path);
	
	// 반환값(기본 : elFinder 설정에 따름)
	$isMatch = false;

	/* dot(.)로 시작하는 파일 및 폴더 숨기기 */
	if( strpos($name, '.')===0 )
		$isMatch = true;

	/* 메타문자(\/:*?"<>|) 필터링 */
	if(preg_match('/^.*[\/:*?"<>|].*$/i', $name))
		$isMatch = true;

	/* 확장자 필터링 */
	// 모듈 컨트롤러를 통해 환경설정 변수 읽어오기
	$oElfinderController = &getController('elfinderxe');
	$extension_mode = $oElfinderController->module_info->extension_mode;
	$extension_list = $oElfinderController->module_info->extension_list;

	// 확장자 필터의 동작 모드
	$isAllow = ($extension_mode=='allow');

	// 정규식 Test
	$pregMatch = preg_match(sprintf('/^.*\.(%s)$/i', $extension_list), $name);

	// 확장자 필터 사용 && 파일 && ('허용' 모드 패턴 불일치 or '차단' 모드 패턴 일치)
	if( $extension_mode!='disable' && is_file($path) && ($isAllow^$pregMatch) )
		$isMatch = true;

	// 필터링된 경우 권한에 따른 결과를 반환하고, 그렇지 않은 경우 판단을 유보한다.
	return ($isMatch)?(!in_array($attr, array('read','write'))):(null);
}

function extensionRenameFilter($name)
{	/* 확장자 필터 : 이름 바꾸기 */
	$isValidName = true;

	/* dot(.)로 시작하는 파일/폴더명 차단 */
	if( strpos($name, '.')===0 )
		$isValidName = false;

	/* 메타문자(\/:*?"<>|) 필터링 */
	if(preg_match('/^.*[\/:*?"<>|].*$/i', $name))
		$isValidName = false;

	/* 확장자 필터링 */
	// 모듈 컨트롤러를 통해 환경설정 변수 읽어오기
	$oElfinderController = &getController('elfinderxe');
	$extension_mode = $oElfinderController->module_info->extension_mode;
	$extension_list = $oElfinderController->module_info->extension_list;

	// 확장자 필터의 동작 모드
	$isAllow = ($extension_mode=='allow');

	// 정규식 Test
	$pregMatch = preg_match(sprintf('/^.*\.(%s)$/i', $extension_list), $name);

	// 확장자 필터 사용 && ('허용' 모드 패턴 불일치 or '차단' 모드 패턴 일치)
	if( $extension_mode!='disable' && ($isAllow^$pregMatch) )
		$isValidName = false;

	// 필터링된 경우 권한에 따른 결과를 반환하고, 그렇지 않은 경우 판단을 유보한다.
	return $isValidName;
}

?>
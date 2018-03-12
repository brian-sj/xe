/**
 * @file   modules/elfinderxe/tpl/js/elfinderxe_admin.js
 * @author TUW (tuw@hanmail.net)
 * @brief  elfinderxe 모듈의 관리자 페이지 javascript
 **/

function completeInsertFileManager(ret_obj)
{	/* 파일 관리기 생성 완료 */
    var error = ret_obj['error'];
    var message = ret_obj['message'];

    var page = ret_obj['page'];
    var module_srl = ret_obj['module_srl'];

    alert(message);

    var url = current_url.setQuery('act','dispElfinderxeAdminFileManagerInfo');
    if(module_srl) url = url.setQuery('module_srl',module_srl);
    if(page) url.setQuery('page',page);
    location.href = url;
}

function completeDeleteElfinderxe(ret_obj)
{	/* 파일 관리기 삭제 마무리 */
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var page = ret_obj['page'];
    alert(message);

    var url = current_url.setQuery('act','dispElfinderxeAdminContent').setQuery('module_srl','');
    if(page) url = url.setQuery('page',page);
    location.href = url;
}

function doUpdateCategory(category_srl, mode, message)
{	/* 카테고리 업데이트 */
    if(typeof(message)!='undefined'&&!confirm(message)) return;

    var fo_obj = xGetElementById('fo_category_info');
    fo_obj.category_srl.value = category_srl;
    fo_obj.mode.value = mode;

    procFilter(fo_obj, update_category);
}

function completeUpdateCategory(ret_obj)
{	/* 카테고리 변경 */
    var error = ret_obj['error'];
    var message = ret_obj['message'];
    var module_srl = ret_obj['module_srl'];
    var page = ret_obj['page'];
    alert(message);

    var url = current_url.setQuery('module_srl',module_srl).setQuery('act','dispElfinderxeAdminCategoryInfo');
    if(page) url.setQuery('page',page);
    location.href = url;
}

function doCartSetup(url)
{	/* 선택한 모듈 관리 */
    var module_srl = new Array();
    jQuery('#fo_list input[name=cart]:checked').each(function() {
        module_srl[module_srl.length] = jQuery(this).val();
    });

    if(module_srl.length<1) return;

    url += "&module_srls="+module_srl.join(',');
    popopen(url,'modulesSetup');
}

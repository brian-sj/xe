// 로드 이벤트
document.addEventListener('DOMContentLoaded', function()
{
	// 파일 브라우저 가로 크기 - 자동 체크박스 컨트롤
	document.getElementById('elfinderxe_width_auto').addEventListener('change', function()
	{
		if(this.checked)
		{
			document.getElementById('elfinderxe_width').setAttribute('disabled', 'disabled');
		}
		else
		{
			document.getElementById('elfinderxe_width').disabled = false;
		}
	}, false);

}, false);
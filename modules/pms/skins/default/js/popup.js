var wpopup = function(option){
	var myPop;
	var opts ={selector :'#popup_content'
			, mode : "right"
			}
	jQuery.extend(opts, option);
console.log(opts);
	function showPopup(inp) {
		
		return ;
		
		if (!myPop) {
			myPop = new dhtmlXPopup({mode: opts.mode});
			myPop.attachHTML( jQuery(opts.selector).html());
		}
		if (myPop.isVisible()) {
			myPop.hide();
		} else {
			var x = dhx4.absLeft(inp);
			var y = dhx4.absTop(inp); 
			var w = inp.offsetWidth;
			var h = inp.offsetHeight;
			myPop.show(x,y,w,h);
		}
	}
}


var AllowedfileType = "|audio/mpeg|audio/x-mpeg|audio/mp3|audio/x-mp3|audio/mpeg3|audio/x-mpeg3|audio/mpg|audio/x-mpg|audio/x-mpegaudio|application/x-shockwave-flash|video/mp4|";
var PREVENT_FILE_TYPE = "php|jsp|bat|sys|cmd";
function sendFileToServer(formData,status)
{
    var uploadURL ="/index.php"; //Upload URL
    var extraData ={}; //Extra Data.
    var jqXHR=jQuery.ajax({
            xhr: function() {
            var xhrobj = jQuery.ajaxSettings.xhr();
            if (xhrobj.upload) {
                    xhrobj.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position;
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                        //Set progress
                        status.setProgress(percent);
                    }, false);
                }
            return xhrobj;
        },
    url: uploadURL,
    type: "POST",
    contentType:false,
    processData: false,
        cache: false,
        data: formData,
        success: function(data){
            status.setProgress(100);
            //jQuery("#status1").append("업로드성공<br>"); 
        }
    }); 
 
    status.setAbort(jqXHR);
}
 
var rowCount=0;
function createStatusbar(obj)
{
     rowCount++;
     var row="odd";
     if(rowCount %2 ==0) row ="even";
     this.statusbar = jQuery("<div class='statusbar "+row+"'></div>");
     this.filename = jQuery("<div class='filename'></div>").appendTo(this.statusbar);
     this.size = jQuery("<div class='filesize'></div>").appendTo(this.statusbar);
     this.progressBar = jQuery("<div class='progressBar'><div></div></div>").appendTo(this.statusbar);
     this.abort = jQuery("<div class='abort'>Abort</div>").appendTo(this.statusbar);
     obj.after(this.statusbar);
 
    this.setFileNameSize = function(name,size)
    {
        var sizeStr="";
        var sizeKB = size/1024;
        if(parseInt(sizeKB) > 1024)
        {
            var sizeMB = sizeKB/1024;
            sizeStr = sizeMB.toFixed(2)+" MB";
        }
        else
        {
            sizeStr = sizeKB.toFixed(2)+" KB";
        }
 
        this.filename.html(name);
        this.size.html(sizeStr);
    }
    this.setProgress = function(progress)
    {       
        var progressBarWidth =progress*this.progressBar.width()/ 100;  
        this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
        if(parseInt(progress) >= 100)
        {
            this.abort.hide();
        }
    }
    this.setAbort = function(jqxhr)
    {
        var sb = this.statusbar;
        this.abort.click(function()
        {
            jqxhr.abort();
            sb.hide();
        });
    }
}
function handleFileUpload(files,obj, upload_target_srl)
{
   for (var i = 0; i < files.length; i++) 
   {
   
	console.log( files[i].type );
	console.log(   AllowedfileType.indexOf(files[i].type)   );
	console.log( upload_target_srl ); 
	if(1){
	// FIle 의 Type 을 잘 못가져옴... 	
	//if(PREVENT_FILE_TYPE.indexOf(files[i].type) < 0  ){
	//if( AllowedfileType.indexOf(files[i].type)  > -1 ){
		var fd = new FormData();
		fd.append('upload', files[i]);
		fd.append('act' , 'procPmsFileUpload');
		fd.append('mid' , mid);
		fd.append('project_srl', params.project_srl );
		fd.append('prefix', params.prefix);
		fd.append('upload_target_srl', upload_target_srl);
console.log( upload_target_srl );			
		var status = new createStatusbar(obj); //Using this we can set progress.
		status.setFileNameSize(files[i].name,files[i].size);
	
		sendFileToServer(fd , status); 
	}else{
		jQuery("#status1").append( "<span class='abort'>"+ files[i].name +"은 올릴 수 없는 파일입니다.</span> <br>");         
	}
   }
}


jQuery(document).ready(function()
{
var obj = jQuery("#dragandrophandler,#memodragandrophandler");
var selector = "#dragandrophandler,#memodragandrophandler";
jQuery('body').on('dragenter',selector , function (e) 
{
    e.stopPropagation();
    e.preventDefault();
    jQuery(this).css('border', '2px solid #0B85A1');
});
jQuery('body').on('dragover', selector , function (e) 
{
     e.stopPropagation();
     e.preventDefault();
});
jQuery('body').on('drop',selector, function (e) 
{
     jQuery(this).css('border', '2px dotted #0B85A1');
     e.preventDefault();
     var files = e.originalEvent.dataTransfer.files;
     //We need to send dropped files to Server
     handleFileUpload(files, $(this) , $(this).attr("data-comment-srl") );
});
jQuery(document).on('dragenter', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
});
jQuery(document).on('dragover', function (e) 
{
  e.stopPropagation();
  e.preventDefault();
  obj.css('border', '2px dotted #0B85A1');
});
jQuery(document).on('drop', function (e) 
{
    e.stopPropagation();
    e.preventDefault();
});
 
});

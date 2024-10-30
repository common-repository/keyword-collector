if (!window.jQuery) {
    var script = document.createElement("SCRIPT");
    script.src = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js';
    script.type = 'text/javascript';
    document.getElementsByTagName("head")[0].appendChild(script);
    var checkReady = function(callback) {
        if (window.jQuery) {
            callback(jQuery);
        }
        else {
            window.setTimeout(function() { checkReady(callback); }, 20);
        }
    };
    checkReady(function(jQuery) {
        jQuery(function() {
            attachHandlers()
        });
    });
}else{
    attachHandlers()
}

function attachHandlers() {
	jQuery(document).ready(function(){
	    jQuery(".keyword-tabs:not(.active)").hide();
	    jQuery(document)
	    .on("click", ".keyword-tabs-btn", function(){ 
	        jQuery(".keyword-tabs-btn").removeClass("active");
	        jQuery(this).addClass("active");
	        jQuery(".keyword-tabs").hide();
	        jQuery(".keyword-tabs[data-keyword-tab-target='" + jQuery(this).data("keyword-tab-item") + "']").show().addClass("active");
	    });
	});

}

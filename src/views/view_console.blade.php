<!-- LARAVEL INSPECTOR START -->
<script type="text/javascript">
@if(isset($redirectData))
	{!!$redirectData !!}
@endif
{!!$collectorsData !!}
(function(XHR) {
	"use strict";

	var send = XHR.prototype.send;

	XHR.prototype.send = function(data) {
		var self = this;
		var oldOnReadyStateChange;
		var url = this._url;
		this.setRequestHeader('Laravel-Inspector', 'interceptor-present');
		function onReadyStateChange() {
			if(self.readyState == 4 /* complete */ && this.getResponseHeader("Content-Type") == "application/json") {
		  		
				var response = JSON.parse(this.response);
				if (typeof response.LARAVEL_INSPECTOR !== 'undefined') {
					if(typeof response.LARAVEL_INSPECTOR === 'string')
					{
						eval(response.LARAVEL_INSPECTOR);
					} else {
						console.log('LARAVEL INSPECTOR ', response);	
					 }
				 }   
			}
			if(oldOnReadyStateChange) {
				oldOnReadyStateChange();
			}
		}
		if(!this.noIntercept) {            
			if(this.addEventListener) {
				this.addEventListener("readystatechange", onReadyStateChange, false);
			} else {
				oldOnReadyStateChange = this.onreadystatechange; 
				this.onreadystatechange = onReadyStateChange;
			}
		}

		send.call(this, data);
	}
})(XMLHttpRequest);
</script>
<!-- LARAVEL INSPECTOR END -->

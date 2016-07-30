<!-- LARAVEL INSPECTOR START -->
<script type="text/javascript">
@if(isset($redirectData))
	{!!$redirectData !!}
			
@endif
{!!$collectorsData !!}
console.groupEnd();

(function(XHR) {
	"use strict";

	var send = XHR.prototype.send;

	XHR.prototype.send = function(data) {
		var self = this;
		var oldOnReadyStateChange;
		var url = this._url;
		this.setRequestHeader('Laravel-Inspector', 'interceptor-present');
		function onReadyStateChange() {
			if(self.readyState == 4 /* complete */) {
		  
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

function recurseMessages(bag)
{
	   
	for(var key in bag) 
	{
		var style = key.substr(0, key.indexOf('_'));
		var name = key.substr(key.indexOf('_')+1);
		switch (style)
		{
			case "table":
				console.groupCollapsed(name);
				console.table(bag[key]);
				console.groupEnd();
				break;
			case "group":
				console.group(name+' ('+bag[key]['_time']+'ms)');
				recurseMessages(bag[key]);
				console.groupEnd();
				break;
			case "warning":
				console.log(name+' (warning):', bag[key]);
				break;
			case "success":
				console.log(name+' (success):', bag[key]);
				break;
			default :
				if(style != '')
					console[style](name+':', bag[key]);
				break;
		}
	}

}
</script>
<!-- LARAVEL INSPECTOR END -->


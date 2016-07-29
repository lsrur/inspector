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
		this.setRequestHeader('Laravel-Inspector', 'interceptor-enabled');
		function onReadyStateChange() {
			if(self.readyState == 4 /* complete */) {
		  
				var response = JSON.parse(this.response);
				if (typeof response.LARAVEL_INSPECTOR !== 'undefined') {
					if(typeof response.LARAVEL_INSPECTOR === 'string')
					{
						eval(response.LARAVEL_INSPECTOR);
					} else {
						console.log('LARAVEL INSPECTOR ', response);
						
					// 	var resp = response.LARAVEL_INSPECTOR;
					// 	console.groupCollapsed('LARAVEL INSPECTOR ' + resp.request.METHOD + ' ' + resp.request.URL +
					// 		', RAM:'+resp['allocRam']+', TIME:'+resp['time'] +', STATUS:' + this.statusText + ' (' + this.status+')' );

					// 	if(resp['exception'] !== undefined)
					// 	{
					// 		console.groupCollapsed('EXCEPTION ' + resp['exception']['class']);
					// 		console.log('Message:', resp.exception['message']);
					// 		console.log('Class:', resp.exception['class']);
					// 		console.log('Code:', resp.exception['code']);
					// 		console.log('Files:', resp.exception['files']);
					// 		console.log('Trace:', resp.exception['trace']);
					// 		console.groupEnd();
					// 	}
					// 	if(resp['messageCount'] >0)
					// 	{
					// 		console.groupCollapsed('MESSAGES ('+resp['messageCount']+')');
					// 		recurseMessages(resp['messages']);
					// 		console.groupEnd();
					// 	}

					// 	if(resp['payload'] !== undefined)
					// 	{
					// 		console.groupCollapsed('PAYLOAD');
					// 		console.log( resp['payload']);
					// 		console.groupEnd();
					// 	}

					// 	if(resp['sql'] !== undefined)
					// 	{
					// 		console.groupCollapsed('SQL ('+resp.sql.count+', '+resp.sql.time+'ms)');
					// 		for(var i=0; i<resp.sql.items.length;i++)
					// 		{
					// 			console.groupCollapsed(resp.sql.items[i]['sql'].substr(0,40) + ' ('+resp.sql.items[i]['time']+')ms' );
					// 			console.log(resp.sql.items[i]['sql']);
					// 			console.log('Connection:', resp.sql.items[i]['connection']);
					// 			console.log('Files:', resp.sql.items[i]['files']);
					// 			console.groupEnd();
					// 		}
					// 		console.groupEnd();
					// 	}

					// 	var keys = ['request', 'server', 'response', 'session'];
					// 	for(var i=0; i<keys.length;i++)
					// 	{
					// 		if(resp[keys[i]] !== undefined)
					// 		{    
					// 			console.groupCollapsed(keys[i].toUpperCase());
					// 			for(var key in resp[keys[i]])
					// 			{
					// 				console.log(key+':', resp[keys[i]][key]);
					// 			}
					// 			console.groupEnd();
					// 		}
					// 	}
					// 	console.groupEnd();
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


@if($isScript)
<script>
@endif
@include('inspector::debuginfo',['data'=>$data])
@if($isScript && !$isRedirect)
(function(XHR) {
    "use strict";
    var send = XHR.prototype.send;
    XHR.prototype.send = function(data) {
        var self = this;
        var oldOnReadyStateChange;
        var url = this._url;
        function onReadyStateChange() {
            if(self.readyState == 4 /* complete */) {
                var response = JSON.parse(this.response);
                eval(response.LARAVEL_INSPECTOR);
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
@endif
@if($isScript)
</script>
</body>
@endif

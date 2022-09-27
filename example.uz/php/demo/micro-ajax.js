/* 
 * added this.request as secong argument for callback func
 */
function microAjax(B, A) {
    this.bindFunction = function (E, D) {
        return function () {
            return E.apply(D, [D])
        }
    };
    this.stateChange = function (D) {
        if (this.request.readyState == 4) {
            this.callbackFunction(this.request.responseText, this.request)
        }
    };
    this.getRequest = function () {
        if (window.ActiveXObject) {
            return new ActiveXObject("Microsoft.XMLHTTP")
        } else {
            if (window.XMLHttpRequest) {
                return new XMLHttpRequest()
            }
        } return false
    };
    this.postBody = (arguments[2] || "");
    this.callbackFunction = A;
    this.url = B;
    this.request = this.getRequest();
    if (this.request) {
        var C = this.request;
        C.onreadystatechange = this.bindFunction(this.stateChange, this);
        if (this.postBody !== "") {
            C.open("POST", B, true);
            C.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            C.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
            C.setRequestHeader("Connection", "close")
        } else {
            C.open("GET", B, true)
        }
        C.send(this.postBody)
    }
};



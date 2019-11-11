function replacePassword(fieldId){
	var fieldObj = document.getElementById("visible"+fieldId);
	if(fieldObj.value.length > 0){
		var hashedPassword = md5(fieldObj.value+"Coucou les Shtroupfs!");
		fieldObj.value = "";
		document.getElementById(fieldId).value = hashedPassword;
		return true;
	} else {
		fieldObj.focus();
		fieldObj.className = "shakedDiv";
		setTimeout(function(){
			fieldObj.className = "";
		}, 1000);
		return false;
	}
	
}


function callServeur(url, callbackSuccess, callbackError, inGet, postData){
	inGet = typeof inGet !== 'undefined' ? inGet : true;
	var xhr;
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		xhr=new XMLHttpRequest();
	} else {// code for IE6, IE5
		xhr=new ActiveXObject("Microsoft.XMLHTTP");
	}
     
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4){
        	if (xhr.status == 200 || xhr.status == 0) {
        		try{
        			var jsonRep = eval(xhr.responseText);
	        		callbackSuccess(jsonRep);
        		} catch ( i) {
        			callbackError("Oups : "+i);
				}
        	} else {
        		callbackError(xhr.status);
        	}
        } else if (xhr.readyState < 4) {
        	//Un petit indicateur pour dire que Ajax tourne ?
   		}
    };
     
    if(inGet){
    	xhr.open("GET", url, true);
	    xhr.send(null);
    } else {
    	xhr.open("POST", url);
    	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    	xhr.send(postData);
	}
}


function removeOptions(selectbox)
{
    var i;
    for(i=selectbox.options.length-1;i>=0;i--)
    {
        selectbox.remove(i);
    }
}
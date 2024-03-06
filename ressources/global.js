function hashPassword(clearPassword){
	if(!clearPassword){
		return "";
	}
		
	if(clearPassword.length == 0){
		return "";
	}
	
	return md5(clearPassword+"Coucou les Shtroupfs!");
}

function replacePassword(fieldId){
	var fieldObj = document.getElementById("visible"+fieldId);
	if(fieldObj.value.length > 0){
		var hashedPassword = hashPassword(fieldObj.value);
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
        			var jsonRep = JSON.parse(xhr.responseText);
	        		callbackSuccess(jsonRep);
        		} catch ( i) {
					console.log(i);
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
    	xhr.open("GET", url+"&random="+new Date().getTime(), true);
	    xhr.send(null);
    } else {
    	xhr.open("POST", url+"&random="+new Date().getTime());
    	xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    	xhr.send(postData);
	}
}

function callServeurPostJson(url, callbackSuccess, callbackError, postJson){
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
        			var jsonRep = JSON.parse(xhr.responseText);
	        		callbackSuccess(jsonRep);
        		} catch ( i) {
					console.log(i);
        			callbackError("Oups : "+i);
				}
        	} else {
        		callbackError(xhr.status);
        	}
        } else if (xhr.readyState < 4) {
        	//Un petit indicateur pour dire que Ajax tourne ?
   		}
    };
     
    xhr.open("POST", url+"&random="+new Date().getTime());
	xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
	xhr.send(JSON.stringify(postJson));
}


function removeOptions(selectbox)
{
    var i;
    for(i=selectbox.options.length-1;i>=0;i--)
    {
        selectbox.remove(i);
    }
}

var inMailling = null;
var cloRep = null;

var callBackFncPoolingEffortRequest = function(rep){
	cloRep = JSON.parse(JSON.stringify(rep));
	console.log(cloRep);
	if(cloRep.emails){

		cloRep.groups = [];

		for(var i = 0; i < cloRep.emails.length; i++){
			var email = cloRep.emails[i];
			//email.hash =  md5(email.message); MD5 renvoie une promesse... odnc arrive trop tard...
			email.hash = email.message.length * (email.objet.length * 20);

			if(cloRep.groups[email.hash] === undefined){
				cloRep.groups[email.hash] = JSON.parse(JSON.stringify(email));
				cloRep.groups[email.hash].destinataire = new Array();
			}

			cloRep.groups[email.hash].destinataire.push(email.destinataire);
		}


		inMailling = cloRep.emails;
		mailStartTransfert();



	}
};

var mailPoolingEffort = function(){
	
	callServeur('/index.php?mailSessionSpool&action=request', 
		callBackFncPoolingEffortRequest, 
		function(p){console.log("Erreur sur le retours de MailPoolingEffort", p)}, 
			true 
			)
}

function mailEndConfirm(rep){
	console.log(rep);
}

function mailEndTransfert(rep){
	if (this.readyState == 4 && (true || this.status < 300)) {
 
		// Response
		var response = this.responseText; 
		
		console.log(response);

		var url = "/index.php?mailSessionSpool&action=confirm&idMails=0";
		
		inMailling.forEach(function(aMail){
			url += ","+aMail.idMail;
		});


		callServeur(url, 
			mailEndConfirm, 
			mailEndConfirm, 
			true 
		)

	 }
}

function mailStartTransfert(){

	try {
		var xhttp = new XMLHttpRequest();
		xhttp.open("POST", "https://api.sendinblue.com/v3/smtp/email", true);
		xhttp.setRequestHeader('api-key', cloRep.key);
		xhttp.setRequestHeader('accept', 'application/json');
		xhttp.setRequestHeader('content-type', 'application/json');
		
		xhttp.onreadystatechange = mailEndTransfert;
	/*
		var firstMail = dt.emails[0];
	
		var dests = [];
	
		dt.emails.forEach(function(aMail){
			dests.push({email: aMail.destinataire ,name: aMail.destinataire});
		});
		
		var data = {
			sender:{email:"visa30@free.fr",name:"Association VISA30"},
			subject: firstMail.objet,
			htmlContent: firstMail.message,
			messageVersions:[
				{
					to: dests,
					subject : firstMail.objet
				}
			]
		};
		*/

		var data = {
			sender:{email:"visa30@free.fr",name:"Association VISA30"},
			subject: "sujet de base",
			htmlContent: "<p>Message de base</p>",
			messageVersions: new Array()
		};



		cloRep.groups.forEach(function(aMail){


			var destinatairesDuGroupe = new Array();

			if(cloRep.redirect){
				destinatairesDuGroupe.push({
					email : cloRep.redirect,
					name : cloRep.redirect
				});

				aMail.destinataire.forEach(function(desti){
					aMail.message += "<p>Email hors production. Destinataire Original = "+desti+"</p>";
				});

			} else {
				aMail.destinataire.forEach(function(desti){
					destinatairesDuGroupe.push({
						email : desti,
						name : desti
					});
				});
			}

			destinatairesDuGroupe.forEach(function(desti){
				data.messageVersions.push({
					htmlContent : aMail.message,
					subject : aMail.objet,
					to : [desti]
				});
			});

			
		});


		console.log(data);

		xhttp.send(JSON.stringify(data));
	} catch (e){
		console.log(e);
	}

	


}
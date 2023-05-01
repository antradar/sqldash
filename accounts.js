setaccountpass=function(lastdarkmode){
	var ooldpass=gid('accountpass');
	var opass1=gid('accountpass1');
	var opass2=gid('accountpass2');
			
	if (opass1.value!=''||opass2.value!=''){
		if (!valstr(ooldpass)) return;
		if (!valstr(opass1)) return;
		if (!valstr(opass2)) return;		
	}

	var oldpass=encodeHTML(ooldpass.value);
	var pass1=encodeHTML(opass1.value);
	var pass2=encodeHTML(opass2.value);
		
	var usega=0;
	if (gid('myaccount_usega').checked) usega=1;
	
	var useyubi=0;
	if (gid('myaccount_useyubi').checked) useyubi=1;
	var yubimode=0;
	if (gid('myaccount_yubimode')&&gid('myaccount_yubimode').checked) yubimode=2;
		
	if (pass1!=''&&pass1!=pass2){
		salert(document.dict['mismatching_password']);
		return;
	}
		
	var darkmode=gid('myaccount_darkmode').value;
	
	if (!lastdarkmode) lastdarkmode=0;
	
	var rq=xmlHTTPRequestObject();
	rq.open('POST',document.appsettings.fastlane+'?cmd=setaccount&usega='+usega+'&useyubi='+useyubi+'&yubimode='+yubimode+'&darkmode='+darkmode,true);
	rq.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	rq.onreadystatechange=function(){
		if (rq.readyState==4){
			salert(rq.responseText);
			refreshtab('account',1);
			//setTimeout(function(){marktabsaved('account',rq.responseText);},100);
						
			//if (lastdarkmode!=darkmode && self.resetdarkmode) resetdarkmode(darkmode);
			
		}	
	}
	
	rq.send('oldpass='+oldpass+'&pass='+pass1);
}

testgapin=function(){
	var opin=gid('myaccount_gatestpin');
	opin.value=opin.value.replace(/[^\d]/g,'',opin.value);
	//if (!valint(opin)) return;
	ajxpgn('statusc',document.appsettings.codepage+'?cmd=testgapin',0,0,'pin='+opin.value,function(rq){
		salert(decodeURIComponent(rq.getResponseHeader('pinres')));	
	});	
}

resetgakey=function(gskey){
	if (!sconfirm('Are you sure you want to reset the authenticator?\nResetting this code will nullify your existing authenticator accounts.\nMake sure you sync up again.')) return;
	reloadtab('account','','resetgakey',null,null,null,gskey);	
}

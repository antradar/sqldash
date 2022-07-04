addquery=function(dbname,tablename,fromid){
	if (!tablename) tablename='';
	
	if (!document.appsettings.queryidx) document.appsettings.queryidx=0;
	document.appsettings.queryidx++;
	
	var myidx=document.appsettings.queryidx;
	
	addtab('query_'+myidx,'<img src="imgs/t.gif" class="ico-query">#'+myidx,'showquery&queryidx='+myidx+'&dbname='+dbname+'&tablename='+tablename,function(){
		if (fromid!=null&&gid('query_'+fromid)) gid('query_'+myidx).value=gid('query_'+fromid).value;
	});
	
}

runquery=function(queryidx,dbname){
	var oquery=gid('query_'+queryidx);
	var query=oquery.value;
	
	var shortview=0;
	if (gid('shortview_'+queryidx).checked) shortview=1;
	var usemacros=0;
	if (gid('usemacros_'+queryidx).checked) usemacros=1;
	if (!valstr(oquery)) return;
	
	var sela=oquery.selectionStart;
	var selb=oquery.selectionEnd;
	if (selb!=null&&sela!=null&&selb-sela>1){
		var sel=oquery.value.substring(sela,selb);
		if (sel!='') query=sel;
	}
	
	
	ajxpgn('queryresult_'+queryidx,document.appsettings.codepage+'?cmd=runquery&queryidx='+queryidx+'&dbname='+dbname+'&shortview='+shortview+'&usemacros='+usemacros,0,0,'query='+encodeHTML(query),null,null,1);
		
}

updatecell=function(dbname,tablename,pkey,pval,fkey,defsetnull){
	var fval='';
	if (gid('celllookupfval')) fval=gid('celllookupfval').value;
	var pfval='';
	if (gid('celllookuppfval')) pfval=gid('celllookuppfval').value;
	
	var setnull=0;
	if (defsetnull) setnull=defsetnull;
	
	var os=document.getElementsByClassName('cell_'+dbname+'_'+tablename+'_'+fkey+'_'+pval);
	for (var i=0;i<os.length;i++){
		var o=os[i];
		o.style.backgroundColor='#ffab00';
		//o.innerHTML=fval.replace(/</g,'&lt;').replace(/>/g,'&gt;');
	}//for
	
	ajxpgn('celllookupupdater',document.appsettings.codepage+'?cmd=updatecell&dbname='+dbname+'&table='+tablename+'&pkey='+pkey+'&pval='+pval+'&fkey='+fkey+'&fval='+encodeHTML(fval)+'&setnull='+setnull,0,0,'pfval='+encodeHTML(pfval),function(rq){
		for (var i=0;i<os.length;i++){
			var o=os[i];
			o.style.backgroundColor='#abffaa';
			o.innerHTML=rq.responseText.replace(/</g,'&lt;').replace(/>/g,'&gt;');
			if (defsetnull) o.innerHTML='<em>NULL</em>';
		}//for
		
		if (gid('celllookupfval')) gid('celllookupfval').value=rq.responseText;
		if (gid('celllookuppfval')) gid('celllookuppfval').value=rq.responseText;
		
	});
}

decodelookupcell=function(decoder,persist){
	var fval='';
	if (gid('celllookupfval')) fval=gid('celllookupfval').value;
	var pfval='';
	if (gid('celllookuppfval')) pfval=gid('celllookuppfval').value;

	if (!persist&&gid('celllookupdeflater')) gid('celllookupdeflater').style.display='none';	
	ajxpgn('celllookupdecoder',document.appsettings.codepage+'?cmd=decodelookupcell&decoder='+decoder+'&fval='+encodeHTML(fval),0,0,'&pfval='+encodeHTML(pfval));	
}

bindecodelookupcell=function(decoder,dbname,tablename,pkey,pval,fkey){
	if (gid('celllookupdeflater')) gid('celllookupdeflater').style.display='none';
	ajxpgn('celllookupdecoder',document.appsettings.codepage+'?cmd=bindecodelookupcell&decoder='+decoder+'&dbname='+dbname+'&table='+tablename+'&pkey='+pkey+'&pval='+pval+'&fkey='+fkey);	
}


resolvecell=function(d,lookupquery,tablename,pkey,pval,fkey,lookuptitle,dbname,reckv){
	var metakey=0;
	if (document.keyboard['key_17']||document.keyboard['key_91']||document.keyboard['key_224']) metakey=1;
	if (metakey&&reckv!=null&&reckv!=''){
		addquery(dbname,tablename,null,'',1,reckv);
	} else {
		lookupentity(d,lookupquery+'&table='+tablename+'&pkey='+pkey+'&pval='+pval+'&fkey='+fkey,lookuptitle);
	}
}


query_remove_spaces = function(query, keep_tabs) {
    let stack = [];
    let result = '';
    let inQuotes = false;
    let buffer = '';
    let prevChar = '';
    let prevPrevChar = '';

    for (let i = 0; i < query.length; i++) {
        const char = query[i];

        if (['"', "'"].includes(char) && prevChar !== '\\') {
            if (prevPrevChar === '\\') {
                buffer = buffer.slice(0, -1);
            } else if (inQuotes && stack[stack.length - 1] === char) {
                stack.pop();
                inQuotes = false;
            } else if (!inQuotes) {
                stack.push(char);
                inQuotes = true;
            }
            buffer += char;
        } else if (inQuotes) {
            buffer += char;
        } else {
            if (/\s/.test(char) && (char !== '\t' || !keep_tabs)) {
                if (buffer.length > 0) {
                    result += buffer + (char === '\n' ? '\n' : ' ');
                    buffer = '';
                }
            } else {
                buffer += char;
            }
        }

        prevPrevChar = prevChar;
        prevChar = char;
    }
    
    if (buffer.length > 0) {
        result += buffer;
    }

    return result.trim();
}





addquery=function(dbname,tablename,fromid,sqlmode,instant,reckv){
	if (!tablename) tablename='';
	if (!sqlmode) sqlmode='';
	if (!reckv) reckv='';
	
	if (!document.appsettings.queryidx) document.appsettings.queryidx=0;
	document.appsettings.queryidx++;
	
	var myidx=document.appsettings.queryidx;
	
	addtab('query_'+myidx,'<img src="imgs/t.gif" class="ico-query">#'+myidx,'showquery&queryidx='+myidx+'&dbname='+dbname+'&tablename='+tablename+'&sqlmode='+sqlmode+'&reckv='+reckv,function(){
		if (fromid!=null&&gid('query_'+fromid)) gid('query_'+myidx).value=gid('query_'+fromid).value;
		if (instant) runquery(myidx,dbname,sqlmode);
	});
	
}

exportcsv=function(queryidx){
	gid('csvquery_'+queryidx).value=gid('query_'+queryidx).value;
	gid('csvqueryform_'+queryidx).submit();
}
	
runquery=function(queryidx,dbname,sqlmode,explain){
	if (!explain) explain=0;
	
	var oquery=gid('query_'+queryidx);
	var query=oquery.value;
	if (!sqlmode) sqlmode='';
	
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
	
	if (!document.querydims) document.querydims={};
	document.querydims[queryidx]={};
	
	if (!document.sqlmodes) document.sqlmodes={};
	document.sqlmodes[queryidx]=sqlmode;
	
	ajxpgn('queryresult_'+queryidx,document.appsettings.codepage+'?cmd=runquery&queryidx='+queryidx+'&dbname='+dbname+'&shortview='+shortview+'&usemacros='+usemacros+'&sqlmode='+sqlmode+'&explain='+explain,0,0,'query='+encodeHTML(query),null,null,1);
		
}

updatecell=function(dbname,tablename,pkey,pval,fkey,defsetnull){
	var fval='';
	if (gid('celllookupfval')) fval=gid('celllookupfval').value;
	var pfval='';
	if (gid('celllookuppfval')) pfval=gid('celllookuppfval').value;
	
	var setnull=0;
	if (defsetnull) setnull=defsetnull;
	console.log('cell_'+dbname+'_'+tablename+'_'+fkey+'_'+pval);
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

////

renderquerydims=function(queryidx){
	if (!document.querydims) return;
	if (!document.querydims[queryidx]) return;
	var dimgroups=document.querydims[queryidx];

	var html=[];
	
	var dimtypenames={
		'dim':'Discrete Dimension',
		'range':'Numeric Range',
		'daterange':'Date Range',
		'attr':'<em style="color:#999999;">Non-Dim Attribute</em>',	
	}
	
	var hasdims=0;
	
	for (dimtypekey in dimgroups){
		hasdims=1;
		var dims=dimgroups[dimtypekey];
		var dimtypename=dimtypenames[dimtypekey];

		html.push('<div><b>'+dimtypename+':</b></div><div style="padding:5px 10px;margin-bottom:10px;">');
		for (dim in dims){
			html.push('<nobr><u>'+dim+'</u> <a onclick="delquerydim('+queryidx+',\''+dimtypekey+'\',\''+dim+'\');"><img src="imgs/t.gif" class="img-del"></a></nobr> &nbsp;&nbsp;');
		}
		
		html.push('</div>');
	}
	
	if (hasdims){
		html.push('<div class="inputrow buttonbelt"><button onclick="explorequerydims('+queryidx+');">Explore</button></div>');	
	}
	
	gid('querydims_'+queryidx).innerHTML=html.join('');
	
}

addquerydim=function(queryidx,dimtype,fkey){
	if (!document.querydims) document.querydims={};
	if (!document.querydims[queryidx]) document.querydims[queryidx]={};
	
	if (!document.querydims[queryidx][dimtype]) document.querydims[queryidx][dimtype]={};
	document.querydims[queryidx][dimtype][fkey]=fkey;
	
	renderquerydims(queryidx);
	
}

delquerydim=function(queryidx,dimtype,fkey){
	if (!document.querydims) return;
	if (!document.querydims[queryidx]) return;
	if (!document.querydims[queryidx][dimtype]) return;
	
	delete document.querydims[queryidx][dimtype][fkey];
	
	var emptynode=1;
	for (k in document.querydims[queryidx][dimtype]) emptynode=0;
	
	if (emptynode) delete document.querydims[queryidx][dimtype];
	
	renderquerydims(queryidx);
}

explorequerydims=function(queryidx){
	if (!document.querydims) return;
	if (!document.querydims[queryidx]) return;
	
	var oquery=gid('query_'+queryidx);
	var query=encodeHTML(oquery.value);
	
	var sqlmode=document.sqlmodes[queryidx];
		
		
	var strdims=JSON.stringify(document.querydims[queryidx]);
	
	closetab('querynav_'+queryidx);
	addtab('querynav_'+queryidx,'Nav #'+queryidx,'dashquerydims&queryidx='+queryidx,function(){
		nav_loadcharts('dashquerydims','dashquerydimkey','dashquerydims',queryidx);
	},'dims='+encodeHTML(strdims)+'&query='+query+'&sqlmode='+sqlmode);
}
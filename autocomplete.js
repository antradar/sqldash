picklookup=function(val){
	if (document.hotspot==null) return;
	
	var args=arguments;

	if (document.hotspot.id) document.hotspot=gid(document.hotspot.id);
		
	for (var i=0;i<args.length;i++){
		if (i==0) continue;
		document.hotspot['value'+(i+1)]=args[i];		
	}
	document.hotspot['valuecount']=args.length-1;
		
	document.hotspot.value=val;

	if (document.hotspot.lookupview) document.hotspot.lookupview.style.display='none';		
	if (document.hotspot.id) {
		var v2c=gid(document.hotspot.id+'_val2');
		if (v2c){
				gid(document.hotspot.id).disabled='disabled';
				v2c.innerHTML='<a class="labelbutton" href=# onclick="cancelpickup(\''+document.hotspot.id+'\');return false;">'+document.dict['edit']+'</a>';
		}
	}
	if (gid(document.hotspot.id+'_lookup')) gid(document.hotspot.id+'_lookup').style.display='none';
	if (document.hotspot.attributes['keeplookup']==null) hidelookup(); //place "keeplookup" on the trigger
	if (document.hotspot.onchange) document.hotspot.onchange();
	
	if (document.hotspot){
		document.onclick=document.hotspot.lastonclick;
		document.hotspot.lastonclick=null;
	}		
}

selectpickup=function(sf,title){
	if (!document.hotspot) return;
	if (document.hotspot.id) document.hotspot=gid(document.hotspot.id);
	
	if (document.hotspot){
		document.onclick=document.hotspot.lastonclick;
		document.hotspot.lastonclick=null;
	}
	
	var d=document.hotspot;
	
	sf.seltitle=title;
	
	var sels=[]
	var os=gid('lkvc').getElementsByTagName('input');
	
	if (document.iphone_portrait&&d.id&&gid(d.id+'_lookup')) os=gid(d.id+'_lookup').getElementsByTagName('input');
	
	var dtitle='';
	for (var i=0;i<os.length;i++) if (os[i].className=='lksel'&&os[i].checked) {sels.push(os[i].value);dtitle=os[i].seltitle;}
	
	if (sels.length==0) {cancelpickup(d.id,true);return;}
		
	if (sels.length==1) d.value=dtitle; else d.value='('+sels.length+' items selected)';
	d.value2=sels.join(',');
	
	if (document.hotspot.id) {
		var v2c=gid(document.hotspot.id+'_val2');
		if (v2c){
				gid(document.hotspot.id).disabled='disabled';
				v2c.innerHTML='<a class="labelbutton" href=# onclick="cancelpickup(\''+document.hotspot.id+'\');return false;">'+document.dict['edit']+'</a>';
		}
	}
		
	if (d.onchage) d.onchange();
}

pickupalllookups=function(sf){
	if (!document.hotspot) return;

	if (document.hotspot.id) document.hotspot=gid(document.hotspot.id);
	
	if (document.hotspot){
		document.onclick=document.hotspot.lastonclick;
		document.hotspot.lastonclick=null;
	}	

	var d=document.hotspot;
		
	var sels=[]
	var os=gid('lkvc').getElementsByTagName('input');
	
	if (document.iphone_portrait&&d.id&&gid(d.id+'_lookup')) os=gid(d.id+'_lookup').getElementsByTagName('input');
	
	if (!sf.allchecked){
		for (var i=0;i<os.length;i++) if (os[i].className=='lksel') {os[i].checked='checked';sels.push(os[i].value);}
		sf.allchecked=true;
		sf.innerHTML='unselect all items';
	} else {
		sf.allchecked=null;
		for (var i=0;i<os.length;i++) if (os[i].className=='lksel') {os[i].checked='';}
		sf.innerHTML='select all items';
	}
	
	if (sels.length==0) {cancelpickup(d.id,true);return;}
	
	d.value='('+sels.length+' items selected)';
	d.value2=sels.join(',');
	
	if (document.hotspot.id) {
		var v2c=gid(document.hotspot.id+'_val2');
		if (v2c){
				gid(document.hotspot.id).disabled='disabled';
				v2c.innerHTML='<a class="labelbutton" style="color:#ffffff;" onclick="cancelpickup(\''+document.hotspot.id+'\');">'+document.dict['edit']+'</a>';
		}
	}
		
	if (d.onchage) d.onchange();
}


cancelpickup=function(c,unlockonly){
	if (unlockonly) {
		gid(c).disabled='';
		gid(c).value='';
		if (gid(c+'_val2')) gid(c+'_val2').innerHTML='';
		if (gid(c).valuecount) for (var i=0;i<gid(c).valuecount;i++) delete gid(c)['value'+(i+2)];
		return;	
	}
	
	if (gid(c)) {gid(c).disabled=''; gid(c).value='';gid(c).focus();}
	if (gid(c+'_val2')) gid(c+'_val2').innerHTML='';
	if (gid(c).valuecount) for (var i=0;i<gid(c).valuecount;i++) delete gid(c)['value'+(i+2)];
	
	if (document.hotspot&&document.hotspot.id) document.hotspot=gid(document.hotspot.id);
	if (document.hotspot&&document.hotspot.onchange) document.hotspot.onchange();
		
}

listlookup=function(d,title,command,mini,data){
	if (document.iphone_portrait) mini=1;
	if (document.tabafloat) mini=1;
	if (document.widen) mini=1;
	if (document.hotspot&&document.hotspot.id) document.hotspot=gid(document.hotspot.id);
	if (document.hotspot&&!d) d=document.hotspot;
	if (mini&&!d) return;
		
	var lookupdismiss=function(e){
		if (e==null||e.target==null) return;
		var p=e.target;
		var isself=0;
		while (p!=null&&p!=document.body){
			if ((p==d||p==gid(d.id+'_lookup')||p==document.hotspotref)&&p!=gid(d.id+'_lookup_closer')) isself=1;
			p=p.parentNode;	
		}
		if (!isself){
			document.onclick=d.lastonclick;
			d.lastonclick=null;
			gid(d.id+'_lookup').style.display='none';
		}
	}
	
	
	if (mini&&d.id&&gid(d.id+'_lookup')){
		if (document.hotspot&&gid(document.hotspot.id)&&gid(document.hotspot.id+'_lookup')) gid(document.hotspot.id+'_lookup').style.display='none';
		if (document.hotspot&&document.hotspot.lookupview) {
			document.hotspot.lookupview.style.display='none';
			if (d!=document.hotspot) document.hotspot.lookupview.innerHTML='';
		}
		gid(d.id+'_lookup').style.display='block';
		gid(d.id+'_lookup_view').style.display='block';
		ajxpgn(d.id+'_lookup_view',document.appsettings.codepage+'?cmd='+command,0,0,data);	
		d.lookupview=gid(d.id+'_lookup_view');
		
		document.hotspot=d;
		if (d.lastonclick==null){
			setTimeout(function(){
				d.lastonclick=document.onclick;
				document.onclick=lookupdismiss;
			},100);
		}
		return;	
	}

	if (document.iphone_portrait&&!document.portraitlock){
		if (gid('rotate_indicator')){
			gid('rotate_indicator').style.display='block';
			setTimeout(function(){
				gid('rotate_indicator').style.display='none';
			},1000);	
		}
		return;	
	}
	
	document.hotspot=d;
	
	if (gid('lkv')){
		gid('lkvt').innerHTML=title;
		gid('lkvc').innerHTML='';
		showlookup();
		ajxpgn('lkvc',document.appsettings.codepage+'?cmd='+command,0,0,data);
	} else {	
		var view;
		gid('tooltitle').innerHTML='<a>'+title+'</a>';
		if (document.viewindex!=null){
			stackview();
			view=document.appsettings.viewcount-1;
		} else {
			view=1;
			showview(1);
		}
		
		ajxpgn('lv'+view,document.appsettings.codepage+'?cmd='+command,true,true,data);
		
	}	
	
		
}

showrelrec=function(id,showfunc,defid){
	var d=gid(id);
	if (d.disabled) showfunc(d.value2?d.value2:defid,d.value,arguments);
}

pickmonth=function(d,defyear){
	if (!defyear) defyear=d.value;
	listlookup(d,'Calendar','pickdatemonths&defyear='+defyear+'&mode=dir');	
}

pickdate=function(d,opts,def){
	var key='';
	if (d) key=encodeHTML(d.value);
	else key=def;

	if (!opts) opts={mini:0}
	if (!opts.mini) opts.mini=0;
	if (!opts.tz) opts.tz='';
	if (!opts.params) opts.params='';
	
	if (self.portrait_ignore&&!opts.mini) portrait_ignore();
		
	listlookup(d,'Calendar','pkd&key='+key+'&tz='+opts.tz+'&mini='+(opts.mini?'1':'0')+'&'+opts.params,opts.mini);
}

_pickdate=function(d,opts){
	if (d.timer) clearTimeout(d.timer);
	var f=function(d,opts){return function(){
		pickdate(d,opts,null);
	}}
	d.timer=setTimeout(f(d,opts),200);
}

pickdatetime=function(d,opts,def){
	var key='';
	if (d) key=encodeHTML(d.value);
	else key=def;
	
	if (!opts) opts={start:8,end:22,mini:null}
	if (opts.start==null) opts.start=8;
	if (opts.end==null) opts.end=22; 
	if (!opts.mini) opts.mini=null;
	if (!opts.tz) opts.tz='';
	if (!opts.params) opts.params='';

	if (self.portrait_ignore&&!opts.mini) portrait_ignore();
	
	listlookup(d,'Calendar','pkd&mode=datetime&key='+key+'&hstart='+opts.start+'&hend='+opts.end+'&tz='+opts.tz+'&mini='+(opts.mini?'1':'0')+'&'+opts.params,opts.mini);
}

_pickdatetime=function(d,opts,def){
	if (d.timer) clearTimeout(d.timer);
	var f=function(d,opts){return function(){
		pickdatetime(d,opts);
	}}
	d.timer=setTimeout(f(d,opts),200);
}

picktime=function(d,opts,def){
	var key='';
	if (d) key=encodeHTML(d.value);
	else key=def;

	if (!opts) opts={start:8,end:22,y:0,m:0,d:0,mini:null}
	if (!opts.mini) opts.mini=null;

	if (self.portrait_ignore) portrait_ignore();
	
	listlookup(d,'Calendar','pkd&mode=datetime&nodate=1&key='+key+'&hstart='+opts.start+'&hend='+opts.end+'&tz='+opts.tz+'&y='+opts.y+'&m='+opts.m+'&d='+opts.d+'&mini='+(opts.mini?'1':'0'),opts.mini);
}

_picktime=function(d,opts,def){
	if (d.timer) clearTimeout(d.timer);
	var f=function(d,opts){return function(){
		picktime(d,null,opts);
	}}
	d.timer=setTimeout(f(d,opts),200);
}

lookupentity=function(d,entity,title,data,mini){
	if (!d.value) d.value='';
	if (d.disabled) return;
	var gval=encodeHTML(d.value);
	if (d.type=='textarea'){
		if (data==null) { 
			data='key='+gval;
		} else {
			data+='&key='+gval;	
		}
		gval='';
	}
	listlookup(d,title,'lookup'+entity+'&key='+gval,mini,data);	
}

_lookupentity=function(d,entity,title,data,mini){
	if (d.disabled) return;
	if (d.timer) clearTimeout(d.timer);
	d.timer=setTimeout(function(){
		lookupentity(d,entity,title,data,mini);
	},200);
}

inpbackspace=function(id){
	var d=gid(id);
	if (!d) return;
	if (d.value=='') return;
	
	var parts=d.value.trim().split(' ');
	var nparts=[];
	for (var i=0;i<parts.length-1;i++){
		nparts.push(parts[i]);
			
	}
	d.value=nparts.join(' ');
	d.focus();
	
}

//hook this event on textarea::onfocus
filterkeys=function(d){
	if (d.onkeydown!=null) return;
		
	d.onkeydown=function(e){
		var keycode;
		if (e) keycode=e.keyCode; else keycode=event.keyCode;
		if (keycode==9) {
			var start=d.selectionStart;
			var end=d.selectionEnd;
			if (start==null){
				if (document.selection){
					var r=document.selection.createRange();
					if (r==null) return 0;
					var re = d.createTextRange();
					var rc = re.duplicate();
					re.moveToBookmark(r.getBookmark());
					rc.setEndPoint('EndToStart',re);
					start=rc.text.length;
					var lastchar=d.value.substring(start,start+1).replace(/\s/g,'');
					if (lastchar=='') start=start+2;
					end=start;
				}
			}
						
			if (start!=null){
				var val=d.value;
				d.value=val.substring(0,start)+"\t"+val.substring(end);
			}
			
			d.focus();
			if (d.selectionStart) d.setSelectionRange(start+1,start+1);
			return false;	
		}
	}	
}

function pastetotextarea(id,text){
	
	var oobj=gid(id);
	var ovalue=oobj.value;
	
	var sela=oobj.selectionStart;
	var selb=oobj.selectionEnd;
	if (selb!=null&&sela!=null&&selb-sela>1){
		var sel=oobj.value.substring(sela,selb);
		if (sel!='') {
			oobj.value=ovalue.substring(0,sela)+text+ovalue.substring(selb);
		}
	} else {
		oobj.value=ovalue.substring(0,sela)+text+ovalue.substring(sela);
	}
	
}


// svn merge boundary bed99e5db57749f375e738c1c0258047 - 


// svn merge boundary 182eb2eb0c3b7d16cf92c0972fe64bcc - 


// svn merge boundary 4d373b247a04253ee05a972964f7a7f3 -


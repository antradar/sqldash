document.tabcount=0;
document.tabviews=[];
document.tabkeys=[];
document.tabtitles=[];

document.currenttab=-1;
document.flashcolor='#ffffc0';

gettabid=function(key){
  var i;
  for (i=0;i<document.tabcount;i++) if (document.tabkeys[i]==key) return i;
  return -1;	
}


showtab=function(key,opts){
  var i;
  var tabid=gettabid(key);
  if (tabid==-1) return;

  document.currenttab=tabid;

  if (!document.tabhistory) document.tabhistory=[];
  
  var lasttab=null;
  if (document.tabhistory.length>0) lasttab=document.tabhistory[document.tabhistory.length-1];
  
  if (lasttab!=key) document.tabhistory.push(key);
  
  for (i=0;i<document.tabcount;i++){
	  if (i==tabid) continue;
	  document.tabviews[i].style.display='none';
	  document.tabtitles[i].className='dulltab';
  }	
  document.tabviews[tabid].style.display='block';
  document.tabtitles[tabid].className='activetab';
  
//wrapping
  var t=document.tabtitles[document.tabcount-1];
  if (document.lasttab) t=document.lasttab;
  var topmargin=0; //change this if changing tab style

      document.rowcount=(t.offsetTop-topmargin)/38+1;
      if (!document.lastrowcount) document.lastrowcount=1;
      if (document.lastrowcount!=document.rowcount) {
        gid('tabtitles').style.height=38*document.rowcount+'px';
        gid('tabviews').style.top=122+38*(document.rowcount-1)+'px';
        //gid('tabviews').setAttribute("scale:ch",105+30*(document.rowcount-1));
		gid('tabviews').scalech=147+38*(document.rowcount-1);
        scaleall(document.body);
      }
      document.lastrowcount=document.rowcount;
      
      if (opts&&opts.bookmark) gototabbookmark(opts.bookmark);

      var keyparts=key.split('_');
      var ckey=keyparts[0];
      if (self['tabviewfunc_'+ckey]) self['tabviewfunc_'+ckey](keyparts[1]);
      if (self.livechat_updatesummary&&document.chatstatus=='online') livechat_updatesummary();
      
}

tablock=false;

function settabtitle(key,title,opts){
	var tabid=gettabid(key);
	if (tabid==-1) return;
	
	var tabhtml="<nobr><a class=\"tt\" ondblclick=\"refreshtab('"+key+"');\" onclick=\"showtab('"+key+"');\">"+title+"</a><a onclick=\"closetab('"+key+"')\"><span class=\"tabclose\"></span></a></nobr>";
    if (opts!=null&&opts.noclose) tabhtml="<nobr><a class=\"tt\" ondblclick=\"refreshtab('"+key+"');\" onclick=\"showtab('"+key+"');\">"+title+"</a><span class=\"noclose\"></span></nobr>";
	if (title) document.tabtitles[tabid].innerHTML=tabhtml;		

	autosize();
}

function reloadtab(key,title,params,loadfunc,data,opts,gskey){
	
  var tabid=gettabid(key);
  if (tabid==-1) return;
  
  if (document.tabtitles[tabid].tablock) return;
  document.tabtitles[tabid].tablock=1;
    
  var rq=xmlHTTPRequestObject();

  var scn=document.appsettings.codepage+'?cmd=';
  if (opts&&opts.fastlane) scn=document.appsettings.fastlane+'?cmd=';
  
  if (document.wssid) params=params+'&wssid_='+document.wssid;
  
  rq.open('POST',scn+params+'&hb='+hb(),true);
  rq.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
  if (gskey!=null) {
	  //rq.setRequestHeader('X-GSREQ-KEY',gskey);
	  if (data==null) data='X-GSREQ-KEY='+gskey;
	  else data+='&X-GSREQ-KEY='+gskey;
	  
  }
  
	var ct=document.tabviews[tabid];
	
	ct.slowtimer=setTimeout(function(){
		var first=ct.firstChild;
		if (ct.gswi) return;
		var wi=document.createElement('img'); wi.src='imgs/hourglass.gif'; ct.gswi=wi;
		if (gid('statusc')!=ct) wi.style.margin='10px';
		if (first==null) ct.appendChild(wi); else ct.insertBefore(wi,first);
		ct.style.opacity=0.5; ct.style.filter='alpha(50)'; ct.style.color='#999999';
	},800);
	
	
    
  rq.onreadystatechange=function(){
    if (rq.readyState==4){
	    
	  if (ct.slowtimer) clearTimeout(ct.slowtimer);
	    
	  var xtatus=rq.getResponseHeader('X-STATUS');
	  if (rq.status==403||(xtatus|0)==403){
		    if (self.skipconfirm) skipconfirm(); 
		  	window.location.href='login.php';
		    return;
      }
      
      
	   
    document.tabtitles[tabid].tablock=null;
      
	cancelgswi(ct);
	var apperror=rq.getResponseHeader('apperror');
	if (apperror!=null&&apperror!=''){
		if (opts&&opts.errfunc&&opts.errfunc!=null&&opts.errfunc!='') opts.errfunc(rq,decodeURIComponent(apperror));
		else salert('Error: '+decodeURIComponent(apperror));
		return;	
	}
	
	var newkey=rq.getResponseHeader('newkey');

	if (newkey!=null&&newkey!='') {		
		var newparams=rq.getResponseHeader('newparams');
		if (newparams==null||newparams==''){
			salert('Incomplete key change');
			return;	
		}
		
		var newloadfunc=rq.getResponseHeader('newloadfunc');
		if (newloadfunc!=null&&newloadfunc!='') loadfunc=function(){eval(newloadfunc)};
		
		document.tabtitles[tabid].reloadinfo={params:newparams,loadfunc:loadfunc,data:null,opts:null};
		
		document.tabkeys[tabid]=newkey;
		
		if (document.tabhistory){
			for (i=0;i<document.tabcount;i++) if (document.tabkeys[i]==key) {console.warn('key collision; new key ignored');newkey=key;}
			
			for (var i=0;i<document.tabhistory.length;i++){
				if (document.tabhistory[i]==key) document.tabhistory[i]=newkey;
			}
		}
		key=newkey;
		
	}  
	
	var parenttab=rq.getResponseHeader('parenttab');
	if (parenttab!=null&&parenttab!='') {		
		document.tabviews[tabid].parenttab=parenttab;
	}
	
	
	var newtitle=rq.getResponseHeader('newtitle');
	if (newtitle!=null&&newtitle!=''){
		title=decodeURIComponent(newtitle);	
	}	       
	
	if (opts&&opts.persist) document.tabtitles[tabid].reloadinfo={params:params,loadfunc:loadfunc,data:data,opts:opts};
	
	var tabhtml="<nobr><a class=\"tt\" ondblclick=\"refreshtab('"+key+"');\" onclick=\"showtab('"+key+"');\">"+title+"</a><a onclick=\"closetab('"+key+"')\"><span class=\"tabclose\"></span></a></nobr>";
	if (opts!=null&&opts.noclose) tabhtml="<nobr><a class=\"tt\" ondblclick=\"refreshtab('"+key+"');\" onclick=\"showtab('"+key+"');\">"+title+"</a><span class=\"noclose\"></span></nobr>";
	if (title) document.tabtitles[tabid].innerHTML=tabhtml;
	
	document.tabviews[tabid].innerHTML=rq.responseText;
	if (loadfunc!=null) loadfunc(rq);
	if (opts&&opts.bookmark) gototabbookmark(opts.bookmark);
	autosize();
	}
  }
  rq.send(data);
}

function refreshtab(key,skipconfirm){
	
  var tabid=gettabid(key);
  if (tabid==-1) return;
  
  if (!skipconfirm&&!sconfirm(document.dict['confirm_refresh_tab'])) return;
 
  var tab=document.tabtitles[tabid];
  if (!tab.reloadinfo) return;
  tab.style.color='#000000';
  reloadtab(key,null,tab.reloadinfo.params,tab.reloadinfo.loadfunc,tab.reloadinfo.data,tab.reloadinfo.opts);
}

function addtab(key,title,params,loadfunc,data,opts){
	
  var i;
  
  if (document.tablock!=null) return;
  document.tablock=true;
  
  for (i=0;i<document.tabcount;i++) {

	if (document.tabkeys[i]==key) {
        showtab(key,opts);
        document.tablock=null;
		return;
	}
  }


  gid('tabviews').className='bgflash';
  setTimeout(function(){gid('tabviews').className='bgready';},250);      

  var rq=xmlHTTPRequestObject();
  var scn=document.appsettings.codepage+'?cmd=';
  if (opts&&opts.fastlane) scn=document.appsettings.fastlane+'?cmd=';
  if (document.wssid) params=params+'&wssid_='+document.wssid;
  
  rq.open('POST',scn+params+'&hb='+hb(),true);
  rq.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
  
  var c=document.createElement('div');
  c.style.display='none'; c.style.width="100%"; c.style.height="100%"; c.style.overflow="auto";
  
  c.slowtimer=setTimeout(function(){c.innerHTML='<image class="hourglass" src="imgs/hourglass.gif">';},800);

  var t=document.createElement('span');
  var tabhtml="<nobr><a class=\"tt\" ondblclick=\"refreshtab('"+key+"');\" onclick=\"showtab('"+key+"');\">"+title+"</a><a onclick=\"closetab('"+key+"')\"><span class=\"tabclose\"></span></a></nobr>";
  if (opts!=null&&opts.noclose) tabhtml="<nobr><a class=\"tt\" ondblclick=\"refreshtab('"+key+"');\" onclick=\"showtab('"+key+"');\">"+title+"</a><span class=\"noclose\"></span></nobr>";
  if (opts!=null&&opts.closeall) tabhtml="<nobr><a title=\""+document.dict['close_all_tabs']+"\" onclick=\"resettabs('"+key+"');\" class=\"closeall\"></a><a class=\"tt\" ondblclick=\"refreshtab('"+key+"');\" style=\"padding-left:1px;\" onclick=\"showtab('"+key+"');\">"+title+"</a><span class=\"noclose\"></span></nobr>";
  t.innerHTML=tabhtml;
  gid('tabtitles').appendChild(t);
  gid('tabviews').appendChild(c);
  
  t.onmousedown=function(e){
	var metakey=0;
	if (document.keyboard['key_17']||document.keyboard['key_91']||document.keyboard['key_224']) metakey=1;
	if (!metakey) return;
	
	if (t.reloadinfo&&t.reloadinfo.opts&&t.reloadinfo.opts.noclose) return;
	  
	var shadow=gid('tabshadow');
	shadow.style.left=t.offsetLeft+'px';
	shadow.style.top=(t.offsetTop)+'px'; //-t.offsetHeight
	shadow.innerHTML=t.innerHTML;
	shadow.style.display='block';
	shadow.style.opacity=0.6;
	shadow.style.filter='sepia(1)';
	shadow.style.overflow='hidden';
	//shadow.style.width='80px';
	var ox=e?e.clientX:event.clientX;
	var oy=e?e.clientY:event.clientY;
	
	var posx=t.offsetLeft;
	var posy=t.offsetTop;//-t.offsetHeight;
		
	document.tabmovesrc=t;
	t.onmousemove=function(e){
		var x=e?e.clientX:event.clientX;
		var y=e?e.clientY:event.clientY;
		var nx=posx+x-ox;
		var ny=posy+y-oy;
		shadow.style.left=nx+'px';
		shadow.style.top=ny+'px';
		
		if (document.tabmovedst){
			document.tabmovedst.style.border='none';
			document.tabmovedst=null;	
		}

		var mybox=shadow.getBoundingClientRect();
				
		for (var i=0;i<document.tabkeys.length;i++){
			var dst=document.tabtitles[i];
			if (t==dst) continue; //skip self
			if (dst.reloadinfo&&dst.reloadinfo.opts&&dst.reloadinfo.opts.noclose) continue;
			
			var box=document.tabtitles[i].getBoundingClientRect();

			if (mybox.x>box.x&&mybox.x<box.x+box.width && mybox.y>box.y&&mybox.y<box.y+box.height){
	  			document.tabmovedst=dst;
	  			dst.style.borderLeft='solid 3px #ffab00';
				break;
			}
		}
		
	}
		
	t.onmouseup=function(){
		if (document.tabmovedst) document.tabmovedst.style.border='none';
		if (document.tabmovesrc&&document.tabmovedst){
			var p=document.tabmovedst.parentNode;
			p.removeChild(document.tabmovesrc);
			p.insertBefore(document.tabmovesrc,document.tabmovedst);
		}
		shadow.style.display='none';
		t.onmousemove=null;
		document.onmousemove=null;
		document.tabmovesrc=null;
		document.tabmovedst=null;
		
	}
	document.onmousemove=t.onmousemove;
	document.onmouseup=t.onmouseup;
	
	document.lasttab=null;
	var os=gid('tabtitles').getElementsByTagName('span');
	for (var i=0;i<os.length;i++) if (os[i].reloadinfo) document.lasttab=os[i];
	
	
  }

  t.reloadinfo={params:params,loadfunc:loadfunc,data:data,opts:opts};

  document.tabviews[document.tabcount]=c;
  document.tabtitles[document.tabcount]=t;
  document.tabkeys[document.tabcount]=key;
  document.tabcount++;
  showtab(key,opts);
  
  if (document.tabcount>2&&gid('closeall')) gid('closeall').style.display='block';  
    
  rq.onreadystatechange=function(){
    if (rq.readyState==4){
	  if (c.slowtimer) clearTimeout(c.slowtimer);
	  var xtatus=rq.getResponseHeader('X-STATUS');
	  if (rq.status==403||(xtatus|0)==403){
		    if (self.skipconfirm) skipconfirm(); 
		  	window.location.href='login.php';
		    return;
      }
      
	  if (rq.status==401||(xtatus|0)==401){
		  ajxjs(self.showgssubscription,'gssubscriptions.js');
		  showgssubscription();
	      return;
	  }            

		var newtitle=rq.getResponseHeader('newtitle');
		if (newtitle!=null&&newtitle!=''){
			settabtitle(key,decodeURIComponent(newtitle));	
		}	       
	      
	var apperror=rq.getResponseHeader('apperror');
	if (apperror!=null&&apperror!=''){
		salert('Error: '+decodeURIComponent(apperror));
		document.tablock=null;
		return;	
	}      
      
		var parenttab=rq.getResponseHeader('parenttab');
		if (parenttab!=null&&parenttab!='') {		
			t.parenttab=parenttab;
		}
      
      c.innerHTML=rq.responseText; //'<input id="rightview_'+key+'" style="position:absolute;top:-60px;left:0;" title='+encodeHTML(title)+'>'+

      document.tablock=null;
      if (loadfunc!=null) loadfunc(rq);
      if (opts&&opts.bookmark) gototabbookmark(opts.bookmark);
      
    }
  }
  rq.send(data);
}


function resettabs(key){
	if (!sconfirm(document.dict['confirm_close_all_tabs'])) return;
	if (gid('closeall')) gid('closeall').style.display='none';
	
	var tabid=gettabid(key);
	for (var i=0;i<document.tabcount;i++){
		if (tabid==i) continue;
				
		if (document.tabtitles[i]!=null) gid('tabtitles').removeChild(document.tabtitles[i]);
		if (document.tabviews[i]!=null) gid('tabviews').removeChild(document.tabviews[i]);
		
		document.tabtitles[i]=null;
		document.tabviews[i]=null;
		document.tabkeys[i]=null;
	}
	
	document.tabcount=1;
	document.currenttab=tabid;
	
	document.tabhistory=[];	
	
	showtab(key);
	
}

closetab=function(key){
  var tabid=gettabid(key);
  if (tabid==-1) return;
      
  gid('tabtitles').removeChild(document.tabtitles[tabid]);
  gid('tabviews').removeChild(document.tabviews[tabid]);
  document.tabtitles[tabid]=null;
  document.tabviews[tabid]=null;  

  var i;
  for (i=tabid;i<document.tabcount-1;i++){
	  document.tabtitles[i]=document.tabtitles[i+1];
	  document.tabviews[i]=document.tabviews[i+1];
	  document.tabkeys[i]=document.tabkeys[i+1];	  
  }
  document.tabcount--;
  
  if (document.tabcount<=2&&gid('closeall')) gid('closeall').style.display='none';
  
	if (document.tabhistory){
		for (var i=0;i<document.tabhistory.length;i++) if (document.tabhistory[i]==key) document.tabhistory[i]=null;
	}
		
	if (document.currenttab==tabid) {
		document.currenttab=0;
		var lasttab=null;
		while (lasttab==null&&document.tabhistory.length>0){
			lasttab=document.tabhistory.pop();	
		}  
		  
		if (lasttab!=null) {
			showtab(lasttab);
			return;	
		}
	}
	
	if (document.tabcount==0) {document.currenttab=-1; return;}
	showtab(document.tabkeys[document.currenttab]);	
    if (self.livechat_updatesummary&&document.chatstatus=='online') livechat_updatesummary();
	
}

function closetabs(rectype){
	if (!document.tabkeys) return;
	
	var cf=function(tk){return function(){
		closetab(tk);	
	}}
	
	for (var i=0; i<document.tabkeys.length; i++){
		var tabkey=document.tabkeys[i];
		var id=tabkey.replace(rectype+'_','');
		if (parseInt(id,10)==id) setTimeout(cf(tabkey),i*50);	
	}
	
}

function closetabtree(root,sub){
	if (!document.tabkeys) return;
	
	if (!sub) document.toclose=[];

	for (var i=0; i<document.tabkeys.length; i++){
		var tab=document.tabtitles[i];
		var tabkey=document.tabkeys[i];
		if (tab&&tab.parenttab&&tab.parenttab==root) closetabtree(tabkey,1);
	}
	
	document.toclose.push(root);
	
	if (!sub) {

		var cf=function(tk){return function(){
			closetab(tk);	
		}}
					
		for (var i=0; i<document.toclose.length; i++){
			var tabkey=document.toclose[i];
			setTimeout(cf(tabkey),i*50);	
		}
	}
	
      if (self.livechat_updatesummary&&document.chatstatus=='online') livechat_updatesummary();

}


function sconfirm(msg){
	var a=hb();
	var res=confirm(msg);
	var b=hb();
	if (b-a<50&&gid('diagwarn')) {gid('diagwarn').style.display='inline';flashstatus('Warning: dialogs suppressed');}
	return res;
}

function salert(msg){
	var a=hb();
	alert(msg);
	var b=hb();
	if (b-a<50&&gid('diagwarn')) {gid('diagwarn').style.display='inline';
		flashstatus(msg);
		setTimeout(function(){flashstatus('Warning: dialogs suppressed');},1000);
	}
}

function sprompt(title,def){
	var a=hb();
	var res=prompt(title,def);
	var b=hb();
	if (b-a<50&&gid('diagwarn')) {gid('diagwarn').style.display='inline';flashstatus('Warning: dialogs suppressed');}
	return res;
}

function gototabbookmark(id){
	if (!gid(id)||document.currenttab==null||!document.tabviews||!document.tabviews[document.currenttab]) return;
	document.tabviews[document.currenttab].scrollTop=gid(id).offsetTop-30;
}

function marktabchanged(tabkey,hide){
	var mode='block';
	if (hide) mode='none';
	if (gid('changebar_'+tabkey)) gid('changebar_'+tabkey).style.display=mode;
}



Array.prototype.push = function() {
    var n = this.length >>> 0;
    for (var i = 0; i < arguments.length; i++) {this[n] = arguments[i]; n = n + 1 >>> 0;}
    this.length = n;
    return n;
};

Array.prototype.pop = function() {
    var n = this.length >>> 0, value;
    if (n) {value = this[--n]; delete this[n];}
    this.length = n;
    return value;
};

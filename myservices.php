<?php

include 'lb.php';

include 'connect.php'; 
include 'settings.php';
include 'xss.php';

xsscheck();	

include 'evict.php';
evict_check();

login(true); //silent mode

$cmd=$_GET['cmd'];

header('Cache-Control: no-store');

$user=userinfo();
$userid=intval($user['userid']??0);

if (1==SQLDASH_AUTH_MODE&&!in_array($cmd,array('showaccount','setaccountpass','pump','imgqrcode','testgapin','resetgapin','addyubikey','testyubikey','setaccount'))){
	$query="select usega,useyubi from users where userid=$userid";
	$rs=$sdb->query($query);
	$myrow=$rs->fetchArray(SQLITE3_ASSOC);
	$usega=$myrow['usega'];
	$useyubi=$myrow['useyubi'];
	
	if (!$usega&&!$useyubi) {
		$missing2fa=true;
		
		if ($cmd=='wk') $cmd='showaccount';
		else die();
	}
	
	
}//2fa enforcement




switch($cmd){
	

//Connections

	case 'slv_codegen__conns': include 'icl/listconns.inc.php'; listconns(); break;
	case 'dash_codegen__conns': include 'icl/dashconns.inc.php'; dashconns(); break;
	case 'showconn': include 'icl/showconn.inc.php'; showconn(); break;
	case 'newconn': include 'icl/newconn.inc.php'; newconn(); break;
	case 'addconn': include 'icl/addconn.inc.php'; addconn(); break;
	case 'delconn': include 'icl/delconn.inc.php'; delconn(); break;
	case 'updateconn': include 'icl/updateconn.inc.php'; updateconn(); break;
	
	case 'setactiveconn': include 'icl/setactiveconn.inc.php'; setactiveconn(); break;

	case 'imgqrcode': include 'icl/imgqrcode.inc.php'; imgqrcode(); break;

	case 'lookupquerydim': include 'icl/lookupquerydim.inc.php'; lookupquerydim(); break;
	case 'dashquerydims': include 'icl/dashquerydims.inc.php'; dashquerydims(); break;
	
	case 'testgapin': include 'icl/testgapin.inc.php'; testgapin(); break;
	case 'resetgakey': include 'icl/resetgakey.inc.php'; resetgakey(); break;
	
	case 'addyubikey': include 'icl/addyubikey.inc.php'; addyubikey(); break;
	case 'testyubikey': include 'icl/testyubikey.inc.php'; testyubikey(); break;
	
//SQLite
	case 'slv_sqldash__sqlite': include 'icl/slite_listdbs.inc.php'; slite_listdbs(); break;
	case 'slite_showdb': include 'icl/slite_showdb.inc.php'; slite_showdb(); break;
		
//Ghost Bridge
	case 'slv_ghostsql': include 'icl/ghost_listfiles.inc.php'; ghost_listfiles(); break;
	case 'ghost_listtables': include 'icl/ghost_listtables.inc.php'; ghost_listtables(); break;
	
//Databases
	case 'slv_sqldash__databases': include 'icl/listdatabases.inc.php'; listdatabases(); break;
	case 'setdatabase': include 'icl/setdatabase.inc.php'; setdatabase(); break;
	case 'lookuptablecol': include 'icl/lookuptablecol.inc.php'; lookuptablecol(); break;

//Tables
	case 'slv_sqldash__tables': include 'icl/listtables.inc.php'; listtables(); break;
	case 'showtable': include 'icl/showtable.inc.php'; showtable(); break;
	case 'lookupcell': include 'icl/lookupcell.inc.php'; lookupcell(); break;
	case 'updatecell': include 'icl/updatecell.inc.php'; updatecell(); break;
	
	case 'showtablesizes': include 'icl/showtablesizes.inc.php'; showtablesizes(); break;
	
//Queries

	case 'showquery': include 'icl/showquery.inc.php'; showquery(); break;
	case 'runquery': include 'icl/runquery.inc.php'; runquery(); break;
	
	case 'addblankrow': include 'icl/addblankrow.inc.php'; addblankrow(); break;
	
	case 'decodelookupcell': include 'icl/decodelookupcell.inc.php'; decodelookupcell(); break;
	case 'bindecodelookupcell': include 'icl/bindecodelookupcell.inc.php'; bindecodelookupcell(); break;
	case 'lookupcolnav': include 'icl/lookupcolnav.inc.php'; lookupcolnav(); break;
	
	case 'exportcsv': include 'icl/exportcsv.inc.php'; exportcsv(); break;
		
//Accounts
	case 'showaccount': include 'icl/showaccount.inc.php'; showaccount(); break;
	case 'setaccount': include 'icl/setaccountpass.inc.php'; setaccountpass(); break;
  
	case 'slv_core__users': include 'icl/listusers.inc.php'; listusers(); break;
	case 'showuser': include 'icl/showuser.inc.php'; showuser(); break;
	case 'newuser': include 'icl/newuser.inc.php'; newuser(); break;
	case 'adduser': include 'icl/adduser.inc.php'; adduser(); break;
	case 'deluser': include 'icl/deluser.inc.php'; deluser(); break;
	case 'updateuser': include 'icl/updateuser.inc.php'; updateuser(); break;
	case 'reauth': include 'icl/reauth.inc.php'; reauth(); break;
	case 'installmods': include 'icl/installmods.inc.php'; installmods(); break;
	
//Reports & Audit
	case 'slv_core__reports': include 'icl/listreports.inc.php'; listreports(); break;
	case 'rptactionlog': include 'icl/rptactionlog.inc.php'; rptactionlog(); break;  	

	case 'rptsqlcomp': include 'icl/rptsqlcomp.inc.php'; rptsqlcomp(); break;  
	
	case 'slv_core__settings': include 'icl/listsettings.inc.php'; listsettings(); break;
			
// svn merge boundary 80dd22a0883aaa1f8cd09b09e81bdd9b - 


// svn merge boundary bed99e5db57749f375e738c1c0258047 - 


// svn merge boundary 182eb2eb0c3b7d16cf92c0972fe64bcc - 


// svn merge boundary 4d373b247a04253ee05a972964f7a7f3 -

	
  
//Codegen
	case 'codegen_makeform': include 'help/codegen_makeform.inc.php'; codegen_makeform(); break;
	case 'codegen_makecode': include 'help/codegen_makecode.inc.php'; codegen_makecode(); break;  
	
	case 'pkd': include 'icl/lookup.inc.php'; showdatepicker(); break; //lookup
	case 'showtimepicker'; include 'icl/lookup.inc.php'; showtimepicker(); break;
	case 'pump': include 'icl/utils.inc.php'; authpump(); break; //comment this out to disable authentication
	
	case 'wk': include 'icl/showwelcome.inc.php'; showwelcome(); break;
	case 'updategyroscope': include 'icl/updater.inc.php'; updategyroscope(); break;
	case 'showhelp': include 'icl/showhelp.inc.php'; showhelp(); break;
	
	default: echo 'unspecified interface:'.$cmd;
}

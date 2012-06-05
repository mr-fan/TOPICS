function changepic(wie) {
	if (wie == 1) {
		var bildname = document.modify.picture.value;		
		if (bildname.substr(0,7) != 'http://') { bildname = topicpicloc + bildname;}
		document.images['memberpic'].src = bildname;		
	} else {
		var bildname = document.modify.picture.options[document.modify.picture.selectedIndex].value;
		o = 6 + bildname.indexOf(".gif") +  bildname.indexOf(".jpg")  +  bildname.indexOf(".png")+ bildname.indexOf(".GIF") +  bildname.indexOf(".JPG")  +  bildname.indexOf(".PNG");
		
		if (o > 0) {
			document.images['memberpic'].src = topicpicloc + bildname;
			document.images['memberpic'].style.display = "block";
		} else {
			document.images['memberpic'].style.display = "none";
		}
	}
}


function makevisible(what) {
	document.getElementById(what).style.display="block";
	if (what != 'getfromtable' && document.getElementById("getfromtable")) document.getElementById("getfromtable").style.display="none";
	if (what != 'presetstable' && document.getElementById("presetstable")) document.getElementById("presetstable").style.display="none";
	
	
}
function topicfieldchanged() {
	var changed = 1 + parseInt(document.getElementById('topicchangedfields').value);
	document.getElementById('topicchangedfields').value = changed;
	//alert(document.getElementById('topicchangedfields').value);
}

function topictimefieldchanged (why,what) {
	if (why==1) { 
		if (what==3) { 
			document.getElementById("autoarchivetr").style.display=""; 
		} else {
			document.getElementById("autoarchivetr").style.display="none"; 
		}	
	 }
	 
	if (why==2) { 
		if (what>0) { 
			document.getElementById("autoarchive_sectionspan").style.display="inline"; 
		} else {
			document.getElementById("autoarchive_sectionspan").style.display="none"; 
		}	
	 }
}


function changesettings(sid) {

	if( !document.createElement ) {
 		alert('No createElement, sorry');
  		return;
 	}
	
	var script = document.createElement( 'script' );
	if ( script ) {
    	script.setAttribute( 'type', 'text/javascript' );
    	script.setAttribute( 'src', theurl + sid);
 		//alert(theurl + sid);
	
    	var head = document.getElementsByTagName( 'head' )[ 0 ];
    	if ( head ) {
     		head.appendChild( script );
    	}
   	}

}

function changepresets(thefile) {
	
	if (!thelanguage) {thelanguage = "en";}
	
	if( !document.createElement ) {
 		alert('No createElement, sorry');
  		return;
 	}
	fn = 'presets-'+thelanguage+'/'+thefile+'.js';	
	if (thefile.substr(0,3) == '../') {fn = thefile; }
		
	if (script) { 
		head.Child( script ).setAttribute( 'src', fn ); 
	} else {
		var script = document.createElement( 'script' );
		if ( script ) {
			script.setAttribute( 'type', 'text/javascript' );
			script.setAttribute( 'src', fn );
	 
		
			var head = document.getElementsByTagName( 'head' )[ 0 ];
			if ( head ) {
				head.appendChild( script );
			}
		}
	}
}


function selectDropdownOption(element,wert) {
	for (var i=0; i<element.options.length; i++) {
		if (element.options[i].value == wert) {
			element.options[i].selected = true;		
		} else	{
			element.options[i].selected = false;	
		}
	}
}

function selectRadioButtons (element,wert) {
	for  (var i=0; i<element.length; i++) {
		element[i].checked = false; 
		if ( element[i].value == wert) { element[i].checked = true;	}
	}	
}

//-----------------------------------------------------------------------------------
function openpicturepreviews() {
	
	document.getElementById('picturechooser').style.display = "block";
	document.getElementById('picturechooser').innerHTML = "<h2>wird geladen</h2>";
	
	getpicturepreviews()
	
}


//Ajax Script based on http://www.degraeve.com/reference/simple-ajax-example.php

function getpicturepreviews() {
    var xmlHttpReq = false;
    var self = this;
    // Mozilla/Safari
    if (window.XMLHttpRequest) {
        self.xmlHttpReq = new XMLHttpRequest();
    }
    // IE
    else if (window.ActiveXObject) {
        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
    }
	
	strURL = "picupload/modify_topic.pictures.php";
    self.xmlHttpReq.open('POST', strURL, true);
    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    self.xmlHttpReq.onreadystatechange = function() {
        if (self.xmlHttpReq.readyState == 4) {
            showpicturepreviews(self.xmlHttpReq.responseText);
        }
    }
    self.xmlHttpReq.send(getresults());
}

function getresults() {   
	qstr = 's=' + topicsection+'&p='+topicpage; // NOTE: no '?' before querystring
	//alert(qstr);
    return qstr;
}

function showpicturepreviews(str){
    //document.getElementById("suggestbox").innerHTML = '<div class="ajax">'+str+'</div>';
	document.getElementById('picturechooser').innerHTML = str;
	document.getElementById('choosertable').style.display = "block";
}


function choosethispicture(picfile) {
	//alert (picfile);
	
	document.images['memberpic'].style.display = "block";
	document.getElementById('choosertable').style.display = "none";
	if (picfile!=0) {
		document.images['memberpic'].src = topicpicloc + picfile;	
		document.getElementById('picture').value=picfile;
	}
}

function copythistopic() {
	document.getElementById('copytopic').value = 1;
	document.modify.submit();
}

function showuploader() {
	document.getElementById('picturechooser').innerHTML = '<iframe src="picupload/uploader.php?section_id='+topicsection+'&page_id='+topicpage+'" frameborder="0" class="" style="width:600px; height:300px;" scrolling="auto"></iframe>';
}

function showtabarea(nr) {
	i=0;
	while (i < 7) {
		i++;
		if (i == nr) {			
			document.getElementById('tabarea'+i).style.display = "block";
			document.getElementById('linktabarea'+i).style.borderBottom = "0";
			document.getElementById('linktabarea'+i).style.backgroundColor = "#fff";			
		} else {
		
			document.getElementById('tabarea'+i).style.display = "none";
			if (document.getElementById('linktabarea'+i)) {
				document.getElementById('linktabarea'+i).style.borderBottom = "1px solid #666";			
				document.getElementById('linktabarea'+i).style.backgroundColor = "transparent";
			}
		}
	}
}
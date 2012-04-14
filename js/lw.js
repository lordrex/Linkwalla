
// Functions that are called when the page loads
Event.observe(window, 'load', formfocus, false);
Event.observe(window, 'load', hideOldArchives, false);
Event.observe(window, 'load', findLinks, false);


// Finds the links that trigger javascript actions
function findLinks() {
    var lightboxLinks = document.getElementsByClassName("lbOn");
    for(i = 0; i < lightboxLinks.length; i++) {
        var href = lightboxLinks[i].getAttribute('href');
        lightboxLinks[i].setAttribute('href',href + '&lightbox=1');
    }
    var checkSubmitForms = document.getElementsByClassName("checkSubmit");
    for(i = 0; i < checkSubmitForms.length; i++) {
        Event.observe(checkSubmitForms[i], 'submit', checkForm, true);
    }
    var lwResizeAreas = document.getElementsByClassName("lwResize");
    for(i = 0; i < lwResizeAreas.length; i++) {
        Event.observe(lwResizeAreas[i], 'keyup', lwResize);
        Event.observe(lwResizeAreas[i], 'focus', lwResize);
    }
}

// OnLoad hides the older archives and ads a "show all" link
function hideOldArchives() {
    var oldArchiveLinks = document.getElementsByClassName("lwOldArchives");
    for(i = 0; i < oldArchiveLinks.length; i++) {
        oldArchiveLinks[i].style.display="none";
        var showOldAText = document.createTextNode("show all");
        var showOldASpan = document.createElement("span");
        showOldASpan.setAttribute("class","showOldArchives");
        showOldASpan.appendChild(showOldAText);
        oldArchiveLinks[i].parentNode.appendChild(showOldASpan);
		Event.observe(showOldASpan, 'click', showOldArchives);
    }
}

// On clicking 'show all' reveals the older archive links
function showOldArchives(e) {
    var target = window.event ? window.event.srcElement : e ? e.currentTarget : null;
    Element.show(target.previousSibling);
    Element.remove(target);
}

// Selects the first form input, if there is one
function formfocus()
{
    var inputs = document.getElementsByTagName("input");
    if(inputs[0] != null) {
        inputs[0].select();
    }
}

// Simple form validation script
function checkForm(e) {
    var inputlist = document.getElementsByClassName("required");   // Make an array of required inputs
    var empties = new Array();   // Create a blank list for empty fields
    // Check for empty fields, an add them to the empty list
    for( var i = 0; i < inputlist.length; i++ )
        if(Form.Element.getValue(inputlist[i]) == ""){
            empties.push($(inputlist[i]).getAttribute("id"));
        }
        else {
            $(inputlist[i]).style.border="1px solid #333";
        }
    // If there are empty fields, highight them
    if(empties.length > 0) {
        var i = 0;
        while( i < empties.length ) {
            $(empties[i]).style.border="2px solid #a00";
            i++;
        }
        $("formerror").style.display="inline";
        Event.stop(e);
    }
    else {
        // Cosmetic: Reset all of the checked fields to the regular style
        for( var i = 0; i < inputlist.length; i++ )
                    $(inputlist[i]).style.border="1px solid #333";
        // If there aren't empty fields, submit the form
        return true;
    }
}



// Resizable textarea
function lwResize(e) {
    var target = window.event ? window.event.srcElement : e ? e.currentTarget : null;
	a = target.value.split('\n');
	b=0;
	for (x=0;x < a.length; x++) {
		if (a[x].length >= 55) b+= Math.floor(a[x].length/55);
	 }
	b+= a.length;
    //target.value += b;
	if (b > 5) target.rows = 12;
	else  target.rows = 6;
}





/* THIS CODE MAKES LIGHTBOXS WORK
Created By: Chris Campbell
Website: http://particletree.com
Date: 2/1/2006

Inspired by the lightbox implementation found at http://www.huddletogether.com/projects/lightbox/
*/

/*-------------------------------GLOBAL VARIABLES------------------------------------*/

var detect = navigator.userAgent.toLowerCase();
var OS,browser,version,total,thestring;

/*-----------------------------------------------------------------------------------------------*/

//Browser detect script origionally created by Peter Paul Koch at http://www.quirksmode.org/

function getBrowserInfo() {
	if (checkIt('konqueror')) {
		browser = "Konqueror";
		OS = "Linux";
	}
	else if (checkIt('safari')) browser 	= "Safari"
	else if (checkIt('omniweb')) browser 	= "OmniWeb"
	else if (checkIt('opera')) browser 		= "Opera"
	else if (checkIt('webtv')) browser 		= "WebTV";
	else if (checkIt('icab')) browser 		= "iCab"
	else if (checkIt('msie')) browser 		= "Internet Explorer"
	else if (!checkIt('compatible')) {
		browser = "Netscape Navigator"
		version = detect.charAt(8);
	}
	else browser = "An unknown browser";

	if (!version) version = detect.charAt(place + thestring.length);

	if (!OS) {
		if (checkIt('linux')) OS 		= "Linux";
		else if (checkIt('x11')) OS 	= "Unix";
		else if (checkIt('mac')) OS 	= "Mac"
		else if (checkIt('win')) OS 	= "Windows"
		else OS 								= "an unknown operating system";
	}
}

function checkIt(string) {
	place = detect.indexOf(string) + 1;
	thestring = string;
	return place;
}

/*-----------------------------------------------------------------------------------------------*/

Event.observe(window, 'load', initialize, false);
Event.observe(window, 'load', getBrowserInfo, false);
Event.observe(window, 'unload', Event.unloadCache, false);

var lightbox = Class.create();

lightbox.prototype = {

	yPos : 0,
	xPos : 0,

	initialize: function(ctrl) {
		this.content = ctrl.href;
		Event.observe(ctrl, 'click', this.activate.bindAsEventListener(this), false);
		ctrl.onclick = function(){return false;};
	},
	
	// Turn everything on - mainly the IE fixes
	activate: function(){
		if (browser == 'Internet Explorer'){
			this.getScroll();
			this.prepareIE('100%', 'hidden');
			this.setScroll(0,0);
			this.hideSelects('hidden');
		}
		this.displayLightbox("block");
	},
	
	// Ie requires height to 100% and overflow hidden or else you can scroll down past the lightbox
	prepareIE: function(height, overflow){
		bod = document.getElementsByTagName('body')[0];
		bod.style.height = height;
		bod.style.overflow = overflow;
  
		htm = document.getElementsByTagName('html')[0];
		htm.style.height = height;
		htm.style.overflow = overflow; 
	},
	
	// In IE, select elements hover on top of the lightbox
	hideSelects: function(visibility){
		selects = document.getElementsByTagName('select');
		for(i = 0; i < selects.length; i++) {
			selects[i].style.visibility = visibility;
		}
	},
	
	// Taken from lightbox implementation found at http://www.huddletogether.com/projects/lightbox/
	getScroll: function(){
		if (self.pageYOffset) {
			this.yPos = self.pageYOffset;
		} else if (document.documentElement && document.documentElement.scrollTop){
			this.yPos = document.documentElement.scrollTop; 
		} else if (document.body) {
			this.yPos = document.body.scrollTop;
		}
	},
	
	setScroll: function(x, y){
		window.scrollTo(x, y); 
	},
	
	displayLightbox: function(display){
		$('overlay').style.display = display;
		$('lightbox').style.display = display;
		if(display != 'none') this.loadInfo();
	},
	
	// Begin Ajax request based off of the href of the clicked linked
	loadInfo: function() {
		var myAjax = new Ajax.Request(
        this.content,
        {method: 'get', parameters: "", onComplete: this.processInfo.bindAsEventListener(this)}
		);
		
	},
	
	// Display Ajax response
	processInfo: function(response){
		info = "<div id='lbContent'>" + response.responseText + "</div>";
		new Insertion.Before($('lbLoadMessage'), info)
		$('lightbox').className = "done";	
		this.actions();
		// this part is added by Ben, adds validation to a form
        findLinks();
	},
	
	// Search through new links within the lightbox, and attach click event
	actions: function(){
		lbActions = document.getElementsByClassName('lbAction');

		for(i = 0; i < lbActions.length; i++) {
			Event.observe(lbActions[i], 'click', this[lbActions[i].rel].bindAsEventListener(this), false);
			lbActions[i].onclick = function(){return false;};
		}

	},
	
	// Example of creating your own functionality once lightbox is initiated
	insert: function(e){

	   link = Event.element(e).parentNode;

	   Element.remove($('lbContent'));

	 

	   var myAjax = new Ajax.Request(

			  link.href,

			  {method: 'get', parameters: "", onComplete: this.processInfo.bindAsEventListener(this)}

	   );

	 

	},
	
	// Example of creating your own functionality once lightbox is initiated
	deactivate: function(){
		Element.remove($('lbContent'));
		
		if (browser == "Internet Explorer"){
			this.setScroll(0,this.yPos);
			this.prepareIE("auto", "auto");
			this.hideSelects("visible");
		}
		
		this.displayLightbox("none");
	}
}

/*-----------------------------------------------------------------------------------------------*/

// Onload, make all links that need to trigger a lightbox active
function initialize(){
	addLightboxMarkup();
	lbox = document.getElementsByClassName('lbOn');
	for(i = 0; i < lbox.length; i++) {
		valid = new lightbox(lbox[i]);
	}
}

// Add in markup necessary to make this work. Basically two divs:
// Overlay holds the shadow
// Lightbox is the centered square that the content is put into.
function addLightboxMarkup() {
	bod 				= document.getElementsByTagName('body')[0];
	overlay 			= document.createElement('div');
	overlay.id		= 'overlay';
	lb					= document.createElement('div');
	lb.id				= 'lightbox';
	lb.className 	= 'loading';
	lb.innerHTML	= '<div id="lbLoadMessage">' +
						  '<p>Loading</p>' +
						  '</div>';
	bod.appendChild(overlay);
	bod.appendChild(lb);
}
/* END OF LIGHTBOX CODE*/
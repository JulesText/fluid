var delay = (function(){
	var timer = 0;
	return function(callback, ms){
		clearTimeout (timer);
		timer = setTimeout(callback, ms);
	};
})();

// save Editable so matrix html element is focused and saveable
function sE(editableObj) {

	$(editableObj).css("background","#edd");
	document.execCommand('selectAll', false, null); // select text

	$(editableObj).keydown(function (e) {
			// User can cancel edit by pressing escape
			if (e.which == 27) {
					$(editableObj).css("background","#ddd");
			}
			// User can submit by pressing enter (shift enter is carriage return)
			if (e.which == 13 && !e.shiftKey) {
					$(editableObj).trigger('blur');
			}
	});

}

// focus and save Editable fluid (non-matrix) html element
function sEf(editableObj,table,updCol,pcol1,pid1) {

	// cursor to end if input field textarea
	// var eType = editableObj.tagName;
	// eType = eType.toLowerCase();
	// if(eType == 'textarea') {
	//   editableObj.selectionStart = editableObj.selectionEnd = editableObj.value.length;
	// }

	// workaround to stop lastpass interfering with enter key
	$(editableObj).keydown(function (e) {
			// otherwise error: assertion failed: input argument is not an htmlinputelement
			if (e.which == 13) {
					e.stopPropagation();
			}
		});

	// autosave if x seconds after last keystroke
	// just have to be careful not to close window before autosave, swapping fields or windows is fine
	var timeoutId;
	var wait = 1000;
	$(editableObj).keyup(function (e) {

		// avoid triggering on irrelevant window switch keystrokes

		// adding inclusions that fall within excluded range
		//alert(e.which);return false;
		var speccy = [8 // delete
								,46 // backspace
								,173 // - hyphen
								,191 // ?
								,219 // [ or {
								,221 // ] or }
								// , 224 // command button undo/redo/paste but also switch window
								];

		// filter irrelevant keystrokes
		if (
				(e.which < 32 || e.which > 127 // outside alphanumeric/characters (32-127)
					|| (e.which >= 37 && e.which <= 40) // cursor keys (37-40)
				)
				// adding inclusions
				&& !speccy.includes(e.which)
				) {
			return false;
		}

		// change colour to indicate editing
		// once colour disappears, content is saved
		$(editableObj).css("background","#F3F3e0");

		// if a timer was already started, clear it
		if (timeoutId) clearTimeout(timeoutId);

		// wait to send ajax save query
		timeoutId = setTimeout(function () {
				sT(editableObj,table,updCol,pcol1,pid1);
		}, wait);
	});

	// unnecessary unless wait time is long
	// save if blur away
	// $(editableObj).blur(function () {
	//     sT(editableObj,table,updCol,pcol1,pid1);
	// });

}

// saveTo from any html element type even if not in matrix.php, i.e. from fluid
function sT(editableObj,table,updCol,pcol1,pid1,col2,id2,col3,id3,col4,id4,col5,id5,updVal) { // saveTo

		//
		// define vars
		//

		var eType = editableObj.tagName;
		eType = eType.toLowerCase();
		var bgcol = "#fff";
		// alert(eType);

		// set element style
		if (eType == 'select' || eType == 'input') {
			$(editableObj).css("outline-style","dotted");
			$(editableObj).css("outline-width","medium");
			$(editableObj).css("outline-color","#c22");
		} else if (eType == 'textarea') {
			bgcol = "#fff";
		} else if (eType == 'td' || eType == 'div') {
			bgcol = "#ded";
		}

		// call element content
		if (updVal === '' || typeof updVal == 'undefined') {
			if(eType == 'textarea' || eType == 'select' || eType == 'input') {
				updVal = editableObj.value;
			} else if(eType == 'td' || eType == 'div') {
				//updVal = editableObj.innerHTML; // adds unwanted html tags
				updVal = editableObj.innerText;
			} else { alert('error: unrecognised element type ' + eType); return; }
		}

		// exception handling
		if (table == 'itemstatus' && updCol == 'dateCreated'
				&& (updVal == '\n' || updVal === '' || typeof updVal == 'undefined')) updVal = 'NULL';
		if (table == 'itemattributes' && updCol == 'deadline'
				&& (updVal == '\n' || typeof updVal == 'undefined')) updVal = 'NULL';

		// alert(updVal);

		//if (updVal == 'dateCompleteNullRandom34Lror') updVal = 'NULL';

		//alert(table+','+updCol+','+pcol1+','+pid1+','+updVal);

		// shorthand
		if (table == 'lq') table = 'lookupqualities';
		if (updCol == 'val') updCol = 'value';
		if (pcol1 == 'qaId') pcol1 = 'qaId';
		if (col2 == 'vId') col2 = 'visId';
		if (col3 == 'iId') col3 = 'itemId';
		if (col4 == 'qId') col4 = 'qId';
		if (col5 == 'iT') col5 = 'itemType';

		//
		// data validation
		//

		if (col4 == 'qId') id4 = parseInt(id4);

		// the special days calc ids, to trigger recalculation of Effort / Year, Travel / Year, and Cost / Year
		let calcDaysIds = [21, 22, 515, 516, 517, 518, 519, 520, 621, 623, 624, 625, 626, 627];
		let calcDaysFlag = false;
		if (col4 == 'qId' && calcDaysIds.includes(id4)) calcDaysFlag = true;

		// the special month calc ids, to trigger recalculation of Sum / Season
		let calcMonthFlag = false;
		// months
		let calcMonthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		let calcMonthIds = [583, 584];
		if (col4 == 'qId' && calcMonthIds.includes(id4)) {
			calcMonthFlag = true;
			updVal = updVal.replace(/(<br\s*[\/]?>|\r\n\t|\n|\r\t)/gm,""); // remove line breaks and <br>
			updVal = toTitleCase(updVal);
			if (!calcMonthNames.includes(updVal)) return;
		}
		// probability scores
		calcMonthIds = [587, 588, 589, 590, 591, 592, 593, 594, 595, 596, 597, 598];
		if (col4 == 'qId' && calcMonthIds.includes(id4)) calcMonthFlag = true;

		//
		// ajax query
		//

		var ajaxFailed = false; // flag variable to track if ajax request failed

		$.ajax({
		url: "matrixsave.php",
		type: "POST",
		data:'table='+table+'&updCol='+updCol+'&pcol1='+pcol1+'&pid1='+pid1+'&col2='+col2+'&id2='+id2+'&col3='+col3+'&id3='+id3+'&id4='+id4+'&col4='+col4+'&col5='+col5+'&id5='+id5+'&updVal='+encodeURIComponent(updVal),
		success: function(result) {
				// validate query
				// compare update request with actual recorded value in db
				$.post('matrixquery.php', {
							table: table,
							updCol: updCol,
							pcol1: pcol1,
							pid1: pid1,
							col2: col2,
							id2: id2,
							col3: col3,
							id3: id3,
							col4: col4,
							id4: id4,
							col5: col5,
							id5: id5
				}, function(resVal){
						// sanitise
						resVal = resVal.replace(/(<br\s*[\/]?>|\r\n\t|\n|\r\t)/gm,""); // remove line breaks and <br>
						updVal = updVal.replace(/(<br\s*[\/]?>|\r\n\t|\n|\r\t)/gm,""); // remove line breaks and <br>
						updVal = updVal.replace(/(&amp;)/gm, '&'); // convert html characters, ignore ",',\,/,
						updVal = updVal.replace(/(&lt;)/gm, '<');
						updVal = updVal.replace(/(&gt;)/gm, '>');
						updVal = updVal.replace(/( .LIST)/gm, '');
						updVal = updVal.replace(/( .CL)/gm, '');
						// exception when saving NULL to db
						if (resVal == '' && updVal == 'NULL') resVal = 'NULL';
						// confirm request and result match
						if (resVal == updVal) {
								// alert('Posting success\r\n\t' + resVal + '\r\n\t' +updVal);
								$('#ajaxResp').html(resVal.substring(0, 10));
								$(editableObj).css("background",bgcol);
								$(editableObj).css("outline-color","#6a6");
								if (col2 == 'visId') calcFormulae(id2);
								// if one of the special days calc ids, recalculate
								if (calcDaysFlag) calcDays(col2,id2,col3,id3,col5,id5);
								// if one of the special month calc ids, recalculate
								if (calcMonthFlag) calcMonths(col2,id2,col3,id3,col5,id5);
						// otherwise throw warning
						} else {
							// throw warning only if ajax request has not failed previously
        			if (!ajaxFailed) {
								alert('Posting failed\r\n\t' + resVal + '\r\n\t' +updVal);
								// alert('Posting failed\r\n\nManually submit item then refresh page');
								ajaxFailed = true;
							}
						}
				// if no query returned at all throw warning
				}).fail(function() {
					if (!ajaxFailed) {
						alert("Query failed");
						ajaxFailed = true;
					}
				});
		}
	 });

	 // known issue of multiple query requests sent to php when attribute edited more than once, very difficult to understand, don't bother fixing. Not harmful to database.
}

function calcDays(col2,id2,col3,id3,col5,id5) {

	let table = "lookupqualities";
	let pcol1 = "qaId";
	let updCol = "value";
	let col4 = "qId";
	let id4 = "";

	$.ajax({
	url: "matrixsavedays.php",
	type: "POST",
	data:'table='+table+'&updCol='+updCol+'&pcol1='+pcol1+'&col2='+col2+'&id2='+id2+'&col3='+col3+'&id3='+id3+'&id4='+id4+'&col4='+col4+'&col5='+col5+'&id5='+id5
	});

}

function calcMonths(col2,id2,col3,id3,col5,id5) {

	let table = "lookupqualities";
	let pcol1 = "qaId";
	let updCol = "value";
	let col4 = "qId";
	let id4 = "";

	$.ajax({
	url: "matrixsavemonths.php",
	type: "POST",
	data:'table='+table+'&updCol='+updCol+'&pcol1='+pcol1+'&col2='+col2+'&id2='+id2+'&col3='+col3+'&id3='+id3+'&id4='+id4+'&col4='+col4+'&col5='+col5+'&id5='+id5
	});

}

function cB(editableObj,table,updCol,pcol1,pid1,col2,id2,col3,id3,col4,id4,col5,id5) {

		if($(editableObj).is(":checked")) {
				var updVal = 'y';
				// this only triggers if called
				if(updCol == 'dateCompleted') {
					var now=new Date();
					var m  = now.getMonth()+1;
					var d  = now.getDate();
					var y  = now.getFullYear();
					m=(m < 10) ? ("0" + m) : m;
					d=(d < 10) ? ("0" + d) : d;
					updVal=""+y+"-"+m+"-"+d;
				}
		} else {
				var updVal = 'n';
				if(updCol == 'dateCompleted') updVal = 'NULL';
		}

		sT(editableObj,table,updCol,pcol1,pid1,col2,id2,col3,id3,col4,id4,col5,id5,updVal);

}

function calcCL(checklistId) {
		delay(function(){ // call CL calculation within delay cycle to allow CL item write to occur first
				$.ajax({
				url: "matrixsaveCL.php",
				type: "POST",
				data:'listId='+checklistId,
				success: function(result) {
						// notification action?
						// alert(result);
				}
			 });
		}, 200 );
}

function editableCol(tableClass,nthChild,isEdit) {
		// untested
		if (nthChild.length < 1) return;
		$('table.'+tableClass+' td:nth-child('+nthChild+')').attr("contenteditable", isEdit);
}

function toTitleCase(str) {
    return str.toLowerCase().replace(/\b\w/g, function(char) {
        return char.toUpperCase();
    });
}

var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();

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

function sT(editableObj,table,updCol,pcol1,pid1,col2,id2,col3,id3,col4,id4,col5,id5,updVal) { // saveTo
    if (
        /* (updVal !== 'y' || updVal !== 'n' || updVal !== 'dateCompleteNullRandom34Lror') && */
        (/*!!updVal || */ updVal === '' || typeof updVal == 'undefined') 
        ) updVal = editableObj.innerHTML;
        
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

    $.ajax({
		url: "matrixsave.php",
		type: "POST",
		data:'table='+table+'&updCol='+updCol+'&pcol1='+pcol1+'&pid1='+pid1+'&col2='+col2+'&id2='+id2+'&col3='+col3+'&id3='+id3+'&id4='+id4+'&col4='+col4+'&col5='+col5+'&id5='+id5+'&updVal='+encodeURIComponent(updVal),
		success: function(result) { 
		    // validate query
            $.post('matrixquery.php', { 
        	        table: table,
        	        updCol: updCol,
        	        pcol1: pcol1,
        	        pid1: pid1,
        	        col2: col2,
        	        id2: id2,
        	        col3: col3,
        	        id3: id3,
        	        id4: id4,
        	        col4: col4,
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
    		    if (resVal == '' && updVal == 'NULL') resVal = 'NULL'; // exception when saving NULL to db 
    		    // validate
    		    if (resVal == updVal) {
                    $('#ajaxResp').html(resVal.substring(0, 10));
    		        $(editableObj).css("background","#ded");
            		if (col2 == 'visId') calcFormulae(id2);
    		    } else {
        		    alert('Posting failed\r\n\t' + resVal + '\r\n\t' +updVal);
    		    }
            }).fail(function() {
                alert("Query failed");
            });
		}
   });

   // known issue of multiple query requests sent to php when attribute edited more than once, very difficult to understand, don't bother fixing. Not harmful to database.
}

function cB(editableObj,table,updCol,pcol1,pid1,col2,id2,col3,id3,col4,id4,col5,id5) {

    $(editableObj).css("outline-style","solid"); 
    $(editableObj).css("outline-color","#ded"); 

    if($(editableObj).is(":checked")) { 
        var updVal = 'y';
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
    		data:'id='+checklistId,
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


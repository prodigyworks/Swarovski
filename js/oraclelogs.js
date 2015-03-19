var globalAlertCallback = null;


function call(commandName, parameters) {
	if (parameters) {
		for (var param in parameters) {
			$("#" + param).val(parameters[param]);
			
			if (param == "page") {
				$("#commandForm").attr("action", parameters[param]);
			}
		}
	}
	
	setTimeout('document.body.style.cursor = "wait";', 0);
	
	$("#command").val(commandName);
	$("#commandForm").submit();
}

function isDate(txtDate) {     
	var objDate, 	// date object initialized from the txtDate string         
	mSeconds, 		// txtDate in milliseconds         
	day,      		// day         
	month,    		// month         
	year;     		// year     
	
	// date length should be 10 characters (no more no less)     
	
	if (txtDate.length !== 10) {         
		return false;     
	}     
	
	// third and sixth character should be '/'     
	
	if (txtDate.substring(2, 3) !== '/' || txtDate.substring(5, 6) !== '/') {         
		return false;     
	}     
	
	// extract month, day and year from the txtDate (expected format is mm/dd/yyyy)     
	// subtraction will cast variables to integer implicitly (needed     // for !== comparing)     
	
	day = txtDate.substring(0, 2) - 0;      
	month= txtDate.substring(3, 5) - 1; // because months in JS start from 0     
	year = txtDate.substring(6, 10) - 0;     
	
	// test year range     
	
	if (year < 1000 || year > 3000) {         
		return false;     
	}     
	
	// convert txtDate to milliseconds     
	
	mSeconds = (new Date(year, month, day)).getTime();     
	
	// initialize Date() object from calculated milliseconds     
	
	objDate = new Date();     
	objDate.setTime(mSeconds);     
	
	// compare input date and parts from Date() object     
	// if difference exists then date isn't valid     
	
	if (objDate.getFullYear() !== year ||         
		objDate.getMonth() !== month ||         
		objDate.getDate() !== day) {         
			return false;     
	}     
	// otherwise return true     
	return true; 
} 

function isTime(txtDate) {     
	var hour, minutes;     
	
	// date length should be 10 characters (no more no less)     
	
	if (txtDate.length !== 5) {         
		return false;     
	}     
	
	// third and sixth character should be '/'     
	
	if (txtDate.substring(2, 3) !== ':') {         
		return false;     
	}     
	
	hour = txtDate.substring(0, 2);      
	minutes= txtDate.substring(3, 5); 

	if (hour < 0 || hour > 23) {         
		return false;     
	}     
	
	if (minutes < 0 || minutes > 59) {         
		return false;     
	}     

	return true; 
} 

function callAjax(url, postdata, callback, async, error) {
	url = url + "?timestamp=" + new Date();
	
	$.ajax({
			url: url,
			dataType: 'json',
			async: async,
			data: postdata,
			type: "POST",
			error: function(jqXHR, textStatus, errorThrown) {
				if (error) {
//					error(jqXHR, textStatus, errorThrown);
				} else {
//					alert("ERROR :" + errorThrown);
					$("#footer").html(errorThrown);
				}
			},
			success: function(data) {
				if (callback != null) {
					callback(data);
				}
			}
		});
}

function addRow(tableID, cells) {
	  // Get a reference to the table
	  var tableRef;
	  
	  tableRef = document.getElementById(tableID);
	  var body = tableRef.appendChild(document.createElement('tbody')) 
	  var tr = body.appendChild(document.createElement("tr"));

	  // Append a text node to the cell
	  for (var i = 0; i < cells; i++) {
		  var newCell = tr.appendChild(document.createElement("td"));
		  newCell.innerHTML = "<br>";
	  }
}

function envelopeCode(node) {
	$(node).each(function(){
	 	$(this).html(function(index, html) {
		 	output = html.replace(new RegExp("<BR>", 'g'),"\n");
		 	output = output.replace(/^(.*)$/mg, "<li><pre>$1</pre></li>");
	 	});
	 	$(this).replaceWith('<ol class="lncode">'+output+'</ol>');
	 });
	
}

$(document).ready(function() {
	   $('.menubuttons a').click(function (e) {
	   		if ($(this).attr("disabled") == true || $(this).attr("disabled") == "true")
		        e.preventDefault();
	    });						
	    
	
		envelopeCode(".codepreview pre");
		 
		$("#alertdialog").dialog({
				modal: true,
				autoOpen: false,
				width: 300,
				show:"fade",
				title: "Information",
				hide:"fade",
				buttons: {
					Ok: function() {
						$(this).dialog("close");
						
						if (globalAlertCallback) {
							globalAlertCallback();
						}
					}
				}
			});
	
		$(".grid tbody tr").hover(
				function() {
					$(this).addClass("highlight");
				},
				function() {
					$(this).removeClass("highlight");
				}
			);
		
		try {
			$(".datepicker").datepicker({dateFormat: "dd/mm/yy"});
			
		} catch (error) {}
		
		try {
			$(".timepicker").timepicker();
			
		} catch (error) {}
		
		$('.mega-menu').dcMegaMenu({
			rowItems: '2',
			speed: 'fast',
			effect: 'fade',
			fullWidth: false
		});
		
		$(".entryform input").each(function() {
			$(this).after("<div class='bubble' title='Required field' />");
			$(this).blur(function() {
				if ($(this).attr("required") != null && $(this).attr("required") == "true" && $(this).val() == "") {
					$(this).addClass("invalid");
					$(this).next().css("visibility", "visible");
					
				} else {
					$(this).removeClass("invalid");
					$(this).next().css("visibility", "hidden");
				}
			});

		});
	
		$(".entryform select").each(function() {
			$(this).after("<div class='bubble' title='Required field' />");
			
			$(this).blur(function() {
				if ($(this).attr("required") != null && $(this).attr("required") == "true" && $(this).find("option:selected").text() == "") {
					$(this).addClass("invalid");
					$(this).next().css("visibility", "visible");
					
				} else {
					$(this).removeClass("invalid");
					$(this).next().css("visibility", "hidden");
				}
			});

		});
	
		$(".grid").each(
				function() {
					if ($(this).attr("id") != "") {
						var rows = $(this).find("tbody").children().length;
						var cells = $(this).find("thead tr").children('td').length;
						var maxrows = 24;
						
						if ($(this).attr("maxrows")) {
							maxrows = $(this).attr("maxrows");
						}
						
						if (rows < maxrows) {
							for (var i = rows; i < maxrows; i++) {
								addRow($(this).attr("id"), cells);
							}					
						}
					}
				}
			);
	});

function navigate(url) {
	window.location.href = url;
}

function populateCombo(selectid, data, insertBlank) {
	var select = $(selectid);
	var options = select.attr('options');
	  
    $('option', select).remove();  
    
    if (insertBlank) {
        options[options.length] = new Option("", 0);  
    }
	
    $.each(data, function(index, array) {  
         options[options.length] = new Option(array['name'], array['id']);  
    });  
	
}

function getJSONData(url, selectid, callback, insertBlank) {
	$.ajax({
			url: url,
			dataType: 'json',
			async: false,
			error: function(jqXHR, textStatus, errorThrown) {
				alert("ERROR:" + errorThrown);
			},
			success: function(data) {
				populateCombo(selectid, data, insertBlank);

			 	callback();
			}
		});
}

function verifyStandardForm(form) {
	var isValid = true;
	
	$(form).find("select").each(function() {
			if ($(this).attr("required") != null && $(this).attr("required") == "true" && $(this).find("option:selected").text() == "") {
				$(this).addClass("invalid");
				$(this).next().css("visibility", "visible");
				isValid = false;
				
			} else {
				$(this).removeClass("invalid");
				$(this).next().css("visibility", "hidden");
			}
		});

	
	$(form).find("input").each(function() {
			if ($(this).attr("required") != null && $(this).attr("required") == "true" && $(this).val() == "") {
				$(this).addClass("invalid");
				$(this).next().css("visibility", "visible");
				isValid = false;
				
			} else {
				$(this).removeClass("invalid");
				$(this).next().css("visibility", "hidden");
			}
		});

	return isValid;
}


function padZero(date) {
	if (("" + date).length == 1) {
		return "0" + date;
	}
	
	return date;
}

//implement JSON.stringify serialization 
JSON.stringify = JSON.stringify || function (obj) {     
	var t = typeof (obj);     
	if (t != "object" || obj === null) {         
		// simple data type         
		if (t == "string") obj = '"'+obj+'"';         
		return String(obj);     
	} else {         
		// recurse array or object         
		var n, v, json = [], arr = (obj && obj.constructor == Array);         
		for (n in obj) {             
			v = obj[n]; 
			t = typeof(v);             
			
			if (t == "string") v = '"'+v+'"';             
			else if (t == "object" && v !== null) v = JSON.stringify(v);             
			
			json.push((arr ? "" : '"' + n + '":') + String(v));         
		}         
		
		return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");     
	} 
};  

function workingDaysBetweenDates(startDate, endDate) {      
	// Validate input    
	
	if (isNaN(startDate) || isNaN(endDate)) {
		return 0;
	}
	
	if (endDate < startDate)        
		return 0;        
		
	// Calculate days between dates    
	var millisecondsPerDay = 86400 * 1000; // Day in milliseconds    
	startDate.setHours(0,0,0,1);  // Start just after midnight    
	endDate.setHours(23,59,59,999);  // End just before midnight    
	var diff = endDate - startDate;  // Milliseconds between datetime objects        
	var days = Math.ceil(diff / millisecondsPerDay);        
	
	// Subtract two weekend days for every week in between    
	var weeks = Math.floor(days / 7);    
	var days = days - (weeks * 2);    
	// Handle special cases    
	var startDay = startDate.getDay();    
	var endDay = endDate.getDay();        
	
	// Remove weekend not previously removed.       
	if (startDay - endDay > 1)                 
		days = days - 2;              
		
	// Remove start day if span starts on Sunday but ends before Saturday    
	if (startDay == 0 && endDay != 6)        
		days = days - 1                  
		
	// Remove end day if span ends on Saturday but starts after Sunday    
	if (endDay == 6 && startDay != 0)        
		days = days - 1          
		
	return days;
}

function getWeek(date) {
	var onejan = new Date(date.getFullYear(),0,1);
	
	return Math.ceil((((date - onejan) / 86400000) + onejan.getDay()+1)/7);
} 
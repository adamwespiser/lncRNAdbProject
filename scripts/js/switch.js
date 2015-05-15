var buttonStates = new Array();

/*
 * Creates an html button and places it at a certain location in the window
 *
 * @param String id The button's ID
 * @param String loc The desired location in the window for the button
 * @param String chart The button's corresponding chart
 * @param boolean hasState Whether or not the button changes 
 */
function buttonMake(id, loc, chart, hasState) {
	if (hasState) {
		buttonStates[id] = 1; //Default on
	}
	var button = "<button id = '" + id + "' value = '" + id + "' onclick = 'buttonPressed(" + chart + ", this)'>" + id + "</button>";
	$("#" + loc).append(button);
}

/*
 * Changes the state of the button, either on to off or off to on
 * 
 * @param Object button The desired button
 */
function buttonFlip(button) {
	var ID = button.id;
	if (buttonStates[ID] == 1) {
		buttonOff(button);
	}
	else if (buttonStates[ID] == 0) {
		buttonOn(button);
	}
}

/*
 * Turns the button to the on position
 *
 * @param Object button The desired button
 */
function buttonOn(button) {
	var ID = button.id;
	buttonStates[ID] = 1;
	button.style.backgroundColor = "#EBFFEB";
}

/*
 * Turns the button to the off position
 *
 * @param Object button The desired button
 */
function buttonOff(button) {
	var ID = button.id;
	buttonStates[ID] = 0;
	button.style.backgroundColor = "#6B6B6B";
}

/*
 * Flips the button and toggles the visibility of a chart series
 *
 * @param String chart The button's corresponding chart
 * @param Object button The desired button
 */
function buttonPressed(chart, button) {
	var ID = button.id;	
	buttonFlip(button);
	toggleSeries(chart, ID, buttonStates[ID]);
}

/*
 * Switches the visibility of the series of given id based on a button's state
 *
 * @param Object chart The desired chart
 * @param String id The id of the desired series
 * @param boolean bVal The value (on/off) of the button, used to show or hide the series, respectively
 */
function toggleSeries(chart, id, bVal) {
	chart.showLoading();
	var series = chart.series;

	if(hasKey(buttonStates, id)) {
		var polySerie = chart.get(id + " Long Poly A");
		var nonPolySerie = chart.get(id + " Long Non Poly A");
		if(bVal) {
			polySerie.show();
			nonPolySerie.show();
		}
		else {
			polySerie.hide();
			nonPolySerie.hide();
		}
	}
	else if (id == "None" || id == "All") {
		for (i in series) {
			if (id == "None") series[i].hide();
			else series[i].show();
		}
		for (i in buttonStates) {
			if (id == "None") buttonOff(document.getElementById(i));
			else buttonOn(document.getElementById(i));
		}
	}
	chart.hideLoading();
}

/*
 * Returns true or false depending on whether the array contains a certain key
 * 
 * @param arr The array to be searched
 * @param key The desired key
 * @return True/False if the value is in the array
 */
function hasKey(arr, key) {
	for (i in arr) {
		if (i == key) return true;
	}
	return false;
}

/*
 * Returns true or false depending on whether the array contains a certain value
 * 
 * @param arr The array to be searched
 * @param value The desired value
 * @return True/False if the value is in the array
 */
function hasValue(arr, value) {
	return (arr.indexOf(value) != -1);
}

/*
 * Returns the input value as a string with commas where necessary
 * 
 * @param value The desired number without commas
 * @return The same number with commas 
 */

function addComma(value) {
	value = value.toString();
	var len = value.length;

	for (var i = len-3; i > 0; i-=3) {
		value = value.slice(0, i) + "," + value.slice(i);
	}
	return value;
}

/*
 * Returns the input value as an integer without any commas
 * 
 * @param value The desired numbers with commas
 * @return The same number without commas
 */

function removeComma(value) {
	var rValue = value;
	var index = rValue.indexOf(",");
	while(index !== -1) {
		var begin = rValue.substring(0, index);
		var end = rValue.substring(index + 1);
		rValue = begin + end;
		index = rValue.indexOf(",");
	}
	return parseInt(rValue);
}

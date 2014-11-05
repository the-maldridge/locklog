function toggleCollapse(checkbox, id) {
    if(document.getElementById(checkbox).checked) {
	document.getElementById(id).style.visibility="visible"
    } else {
	document.getElementById(id).style.visibility="collapse"
    }
}
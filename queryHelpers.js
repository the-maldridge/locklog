function toggleCollapse(checkbox, id, inner, offState) {
    if(document.getElementById(checkbox).checked) {
	document.getElementById(id).style.visibility="visible"
    } else {
	document.getElementById(id).style.visibility="collapse"
	document.getElementById(inner).value=offState
    }
}
function useName() {
    if(document.getElementById('useName').checked) {
	document.getElementById('name').innerHTML= "Name: <input type='text' name='name'></input>"
    } else {
	document.getElementById('name').innerHTML= "Name: <input type='text' name='name'></input>"
    }
}
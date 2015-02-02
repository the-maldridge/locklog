#!/usr/bin/env python

import logging, os, json
from flask import Flask, session, redirect, url_for, escape, request, render_template
app = Flask(__name__, static_url_path='/static/')
app.secret_key = os.urandom(24)

log = logging.getLogger('werkzeug')
log.setLevel(logging.ERROR)

@app.route("/", methods=["GET","POST"])
def index():
	if request.method == 'GET':
		if 'username' in session:
			return render_template('form.html', PA_NAME=session['username'])
		else:
			return 'You are not logged in'
	elif request.method == 'POST':
		PA = request.form['PA_name']
		res_name = request.form['Res_name']
		res_id = request.form['Res_id']
		logging.info("Logging lockout for %s by %s", res_name, PA)
		return "Success!"

@app.route("/authenticate", methods=["GET","POST"])
def authenticate():
	if request.method == 'POST':
		session['username']=request.form['username']
		#session['building']=request.form['building']
		return redirect(url_for('index'))
	else:
		return app.send_static_file('login.html')

@app.route('/logout')
def logout():
	    # remove the username from the session if it's there
	    session.pop('username', None)
	    return redirect(url_for('index'))

if __name__ == "__main__":
	logging.basicConfig(level=logging.DEBUG)
	app.debug=True
        app.run(host='0.0.0.0')

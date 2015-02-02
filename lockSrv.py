#!/usr/bin/env python

import logging, os, json
from flask import Flask, session, redirect, url_for, escape, request, render_template
app = Flask(__name__, static_url_path='/static/')
app.secret_key = os.urandom(24)

@app.route("/")
def index():
	if 'username' in session:
		return render_template('form.html', PA_NAME=session['username'])
	return 'You are not logged in'

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

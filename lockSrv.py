#!/usr/bin/env python

import logging, os, json, db
from flask import Flask, session, redirect, url_for, escape, request, render_template
app = Flask(__name__, static_url_path='/static/')
app.secret_key = os.urandom(24)
app.uconfig = json.load(open('config.json'))
app.database = db.Database(app.uconfig)
log = logging.getLogger('werkzeug')
log.setLevel(logging.INFO)


@app.route("/", methods=["GET","POST"])
def index():
	if request.method == 'GET':
		if 'username' in session:
			return render_template('form.html', PA_NAME=session['username'])
		else:
			return redirect(url_for('authenticate'))
	elif request.method == 'POST':
		PA = request.form['PA_name']
		res_name = request.form['Res_name']
		res_id = request.form['Res_id']
		logging.info("Logging lockout for %s by %s in %s", res_name, PA, session['building'])
		return redirect(url_for('index'))

@app.route("/authenticate", methods=["GET","POST"])
def authenticate():
	if request.method == 'POST':
		session['username']=request.form['username']
		session['building']=request.form['building']
		password = request.form['password']
		return redirect(url_for('index'))
	else:
		bldgs=[]
		for key in app.uconfig['buildings'].keys():
			bldgs.append((key, app.uconfig['buildings'][key]['disptext']))
		logging.info("keys for login page: %s", bldgs)
		return render_template('login.html', BLDGS=bldgs)

@app.route('/logout')
def logout():
	    # remove the username from the session if it's there
	    session.pop('username', None)
	    return redirect(url_for('index'))

@app.route('/static/<filename>')
def serveStatic(filename):
	return app.send_static_file(filename)

if __name__ == "__main__":
	logging.basicConfig(level=logging.DEBUG)
	app.debug=True
        app.run(host='0.0.0.0')

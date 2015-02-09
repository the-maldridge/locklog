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
		logLockout(PA, session['building'], res_name, res_id)
		return redirect(url_for('index'))
	
@app.route("/review", methods=["GET","POST"])
def review():
	if request.method == 'POST':
		pass
	else:
		return app.send_static_file("review.html")


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



def logLockout(PA, building, res_name, res_id):
	logging.info("Logging lockout for %s by %s in %s", res_name, PA, session['building'])
	app.database.addLockout(PA, building, res_name, res_id)
	if(app.database.checkHistory(res_id)):
		lockouts = app.database.updateHistory(res_id)
		logging.info("Update lockout for %s", res_name)
		if lockouts > app.uconfig['common']['threshold']:
			logging.info("Resident %s has exceeded the threshold with %s lockouts", res_name, lockouts)
	else:
		app.database.addToHistory(res_id)
		logging.info("First lockout for %s", res_id)

if __name__ == "__main__":
	logging.basicConfig(level=logging.DEBUG)
	app.debug=True
        app.run(host='0.0.0.0')

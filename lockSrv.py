#!/usr/bin/env python

import logging, os
from flask import Flask, session, redirect, url_for, escape, request
app = Flask(__name__)
app.secret_key = os.urandom(24)


def authorized():
	if 'username' not in session:
		logging.debug("User not logged in, redirecting")
		return false
	else:
		logging.debug("User already logged in")
		return true

@app.route("/")
def index():
	if 'username' in session:
		return 'Logged in as %s' % escape(session['username'])
	return 'You are not logged in'

@app.route("/authenticate", methods=["POST"])
def authenticate():
	if request.method == 'POST':
		session['username']=request.form['username']
		session['building']=request.form['building']
		return redirect(url_for('index'))
	else:
		return "You cannot access this page directly!"

@app.route('/logout')
def logout():
	    # remove the username from the session if it's there
	    session.pop('username', None)
	    return redirect(url_for('index'))

if __name__ == "__main__":
	logging.basicConfig(level=logging.DEBUG)
        app.run()

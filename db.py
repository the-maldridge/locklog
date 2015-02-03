import sqlite3, MySQLdb

class Database():
    def __init__(self, config):
        config = config["database"]
        

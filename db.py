import sqlite3, MySQLdb, logging
class Database():
    def __init__(self, config):
        self.logger = logging.getLogger("DB-DRVR")
        try:
            config = config["database"]
            self.logger.debug(config)
        except KeyError, e:
            self.logger.critical("Unable to load database configuration")
            raise e

        if config["mode"]=="sqlite":
            self.logger.info("Selecting SQLite database driver")
            self.conn = sqlite3.connect(config["path"])
        elif config["mode"]=="mysql":
            self.logger.info("Selecting mySQL database driver")
            host = config["host"]
            user = config["user"]
            passwd = config["pass"]
            db = config["db"]
            try:
                self.logger.info("Attempting to connect to %s on %s", db, host)
                self.conn = MySQLdb.connect(host, user, passwd, db)
                self.logger.info("Connection successful")
            except:
                self.logger.error("Serious database connection error occured")
        else:
            logging.error("Only SQLite and MySQL are supported at this time")

        try:
            self.logger.debug("Attempting to aquire DB cursor")
            self.c = self.conn.cursor()
            self.logger.debug("Aquired DB cursor")
        except:
            self.logger.error("Could not aquire DB cursor")
            

    def addLockout(self, PA, bldg, res_name, res_id):
        SQL="INSERT INTO lockouts (PA_name, bldg, res_name, res_id) VALUES (%s, %s, %s, %s)"
        self.logger.info("Logging lockout for %s by %s in %s", res_name, PA, bldg)
        self.c.execute(SQL, (PA, bldg, res_name, res_id))
        self.conn.commit()

    def addToHistory(self, res_id):
        SQL="INSERT INTO history (res_id, local_max, total_max) VALUES(%s, 1, 1)"
        self.logger.debug("First lockout for %s", res_id)
        self.c.execute(SQL, (res_id))
        self.conn.commit()

    def checkHistory(self, res_id):
      SQL="SELECT * FROM history WHERE res_id=%s"
      self.c.execute(SQL, (res_id))
      exists=False
      if self.c.fetchall() is not None:
          exists=True
      self.logger.debug("Exists? " + str(exists))
      return exists

    def updateHistory(self, res_id):
        SQL="SELECT `index`,total_max,local_max FROM history WHERE `res_id`=%s"
        self.logger.debug("Selecting data for id #%s", res_id)
        if self.c.execute(SQL, (res_id)):
            data = self.c.fetchone()
            logging.debug("Data: " + str(data))
            index = data[0]
            total = data[1] + 1
            local = data[2] + 1
            SQL="UPDATE history SET total_max=%s,local_max=%s WHERE `index`=%s"
            self.c.execute(SQL, (total, local, index))
            self.conn.commit()
            return local
        else:
            return None

    def resetLocal(self, res_id):
        SQL="UPDATE history SET local_max=0 WHERE `res_id`=%s"
        self.logger.debug("Resetting local max for %s", res_id)
        self.c.execute(SQL, (res_id))
        self.conn.commit()

if __name__ == "__main__":
    logging.basicConfig(level=logging.DEBUG)
    import json
    db = Database(json.load(open("config.json")))
    db.addLockout("michael", "RHW", "foo", "1234")
    #db.addToHistory(1234)
    db.checkHistory(1234)
    db.updateHistory(1234)
    db.resetLocal(1234)

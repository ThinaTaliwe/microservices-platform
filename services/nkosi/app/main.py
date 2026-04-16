import os
import mysql.connector
from flask import Flask, jsonify

app = Flask(__name__)

SERVICE = os.getenv("SERVICE_NAME", "nkosi")
DB_HOST = os.getenv("MYSQL_HOST", "shared-mysql")
DB_USER = os.getenv("MYSQL_USER", "nkosi")
DB_PASS = os.getenv("MYSQL_PASSWORD", "")
DB_NAME = os.getenv("MYSQL_DATABASE", "nkosi_db")

def db_conn():
    return mysql.connector.connect(
        host=DB_HOST,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        connection_timeout=5,
    )

@app.get("/health")
def health():
    return jsonify(service=SERVICE, status="ok")

@app.get("/db")
def db():
    try:
        cn = db_conn()
        cur = cn.cursor()
        cur.execute("SELECT DATABASE(), NOW()")
        row = cur.fetchone()
        cur.close()
        cn.close()
        return jsonify(service=SERVICE, db=row[0], now=str(row[1]), status="ok")
    except Exception as e:
        return jsonify(service=SERVICE, status="error", error=str(e)), 500

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8000)

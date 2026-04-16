import os
import json
import uuid
from datetime import datetime, timezone
from fastapi import FastAPI
import psycopg
import pika

app = FastAPI(title="orders-service")

def db_conn():
    return psycopg.connect(
        host=os.getenv("DB_HOST", "orders-db"),
        port=int(os.getenv("DB_PORT", "5432")),
        dbname=os.getenv("DB_DATABASE", "ordersdb"),
        user=os.getenv("DB_USERNAME", "orders"),
        password=os.getenv("DB_PASSWORD", "orderspass"),
    )


def rabbit_publish(event: dict):
    host = os.getenv("RABBITMQ_HOST", "rabbitmq")
    port = int(os.getenv("RABBITMQ_PORT", "5672"))
    user = os.getenv("RABBITMQ_USER", "guest")
    password = os.getenv("RABBITMQ_PASSWORD", "guest")

    creds = pika.PlainCredentials(user, password)
    params = pika.ConnectionParameters(host=host, port=port, credentials=creds, heartbeat=30)
    conn = pika.BlockingConnection(params)
    ch = conn.channel()
    ch.exchange_declare(exchange="events", exchange_type="topic", durable=True)

    ch.basic_publish(
        exchange="events",
        routing_key="order.created",
        body=json.dumps(event).encode("utf-8"),
        properties=pika.BasicProperties(
            content_type="application/json",
            delivery_mode=2,
            correlation_id=event.get("correlation_id"),
        ),
    )
    conn.close()


@app.get("/health")
def health():
    return {"service": "orders-service", "status": "ok"}


@app.post("/demo-create")
def demo_create():
    user_id = int(os.getenv("DEMO_DEFAULT_USER_ID", "1"))
    total = float(os.getenv("DEMO_DEFAULT_TOTAL", "199.99"))
    correlation_id = str(uuid.uuid4())
    now = datetime.now(timezone.utc).isoformat()

    with db_conn() as conn:
        with conn.cursor() as cur:
            cur.execute(
                "INSERT INTO demo_orders (user_id, total, status) VALUES (%s, %s, %s) RETURNING id;",
                (user_id, total, "CREATED"),
            )
            order_id = int(cur.fetchone()[0])

    event = {
        "event_type": "order.created",
        "order_id": order_id,
        "user_id": user_id,
        "total": total,
        "created_at": now,
        "correlation_id": correlation_id,
        "source": "orders-service",
    }

    rabbit_publish(event)

    return {"ok": True, "order": event, "message": "Order created + event published"}


@app.get("/orders")
def list_orders():
    with db_conn() as conn:
        with conn.cursor() as cur:
            cur.execute("SELECT id, user_id, total, status, created_at FROM demo_orders ORDER BY id DESC LIMIT 20;")
            rows = cur.fetchall()

    return {
        "count": len(rows),
        "orders": [
            {"id": r[0], "user_id": r[1], "total": float(r[2]), "status": r[3], "created_at": r[4].isoformat()}
            for r in rows
        ],
    }


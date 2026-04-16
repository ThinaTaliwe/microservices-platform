FROM python:3.12-slim

# 1) System deps needed to compile mysqlclient (and other wheels)
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    pkg-config \
    default-libmysqlclient-dev \
    && rm -rf /var/lib/apt/lists/*

# 2) Work inside the same directory your compose uses
WORKDIR /opt/microservices-platform/services/siya

# 3) Install python deps during build so they persist
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# 4) Copy project code (optional, since you also mount volume in compose)
COPY . .

# 5) App port
EXPOSE 8000

# 6) Run API server (always-on)
#    Use WSGI for Django: config.wsgi:application
CMD ["gunicorn", "config.wsgi:application", "--bind", "0.0.0.0:8000", "--workers", "2", "--timeout", "120"]

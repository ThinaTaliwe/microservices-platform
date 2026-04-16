FROM python:3.12-slim
WORKDIR /app
COPY app /app
RUN pip install flask mysql-connector-python
CMD ["python", "main.py"]

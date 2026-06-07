FROM python:3.11-slim
WORKDIR /app
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt
# Keeps the container alive listening for your script
CMD ["python", "-u", "monitor.py"]

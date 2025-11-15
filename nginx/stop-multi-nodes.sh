#!/bin/bash

# Script untuk menghentikan multiple Laravel nodes
# Usage: ./stop-multi-nodes.sh

PROJECT_DIR="/var/www/laravel-reverb-absensi"
LOG_DIR="$PROJECT_DIR/storage/logs"

echo "Stopping Laravel Multi-Node Setup..."

# Function to stop a service
stop_service() {
    local SERVICE_NAME=$1
    local PID_FILE="$LOG_DIR/$SERVICE_NAME.pid"
    
    if [ -f "$PID_FILE" ]; then
        PID=$(cat $PID_FILE)
        if ps -p $PID > /dev/null 2>&1; then
            echo "Stopping $SERVICE_NAME (PID: $PID)..."
            kill $PID
            rm $PID_FILE
            echo "$SERVICE_NAME stopped."
        else
            echo "$SERVICE_NAME is not running."
            rm $PID_FILE
        fi
    else
        echo "PID file not found for $SERVICE_NAME"
    fi
}

# Stop all services
stop_service "node-1"
stop_service "node-2"
stop_service "node-3"
stop_service "reverb"
stop_service "queue"

echo ""
echo "All services stopped."

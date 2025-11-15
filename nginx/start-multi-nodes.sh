#!/bin/bash

# Script untuk menjalankan multiple Laravel nodes di server
# Usage: ./start-multi-nodes.sh

PROJECT_DIR="/var/www/laravel-reverb-absensi"
LOG_DIR="$PROJECT_DIR/storage/logs"

echo "Starting Laravel Multi-Node Setup..."

# Check if project directory exists
if [ ! -d "$PROJECT_DIR" ]; then
    echo "Error: Project directory not found: $PROJECT_DIR"
    exit 1
fi

cd $PROJECT_DIR

# Create log directory if not exists
mkdir -p $LOG_DIR

# Function to start a node
start_node() {
    local NODE_ID=$1
    local PORT=$2
    
    echo "Starting Node $NODE_ID on port $PORT..."
    
    # Set environment variable for node ID
    export APP_NODE_ID="node-$NODE_ID"
    
    # Start PHP built-in server
    nohup php -S 127.0.0.1:$PORT -t public \
        > "$LOG_DIR/node-$NODE_ID.log" 2>&1 &
    
    echo $! > "$LOG_DIR/node-$NODE_ID.pid"
    echo "Node $NODE_ID started with PID $(cat $LOG_DIR/node-$NODE_ID.pid)"
}

# Start 3 Laravel nodes
start_node 1 8001
sleep 1
start_node 2 8002
sleep 1
start_node 3 8003

echo ""
echo "Starting Reverb WebSocket Server..."
nohup php artisan reverb:start \
    > "$LOG_DIR/reverb.log" 2>&1 &
echo $! > "$LOG_DIR/reverb.pid"
echo "Reverb started with PID $(cat $LOG_DIR/reverb.pid)"

echo ""
echo "Starting Queue Worker..."
nohup php artisan queue:work --tries=3 \
    > "$LOG_DIR/queue.log" 2>&1 &
echo $! > "$LOG_DIR/queue.pid"
echo "Queue worker started with PID $(cat $LOG_DIR/queue.pid)"

echo ""
echo "=========================================="
echo "All services started successfully!"
echo "=========================================="
echo "Node 1: http://127.0.0.1:8001"
echo "Node 2: http://127.0.0.1:8002"
echo "Node 3: http://127.0.0.1:8003"
echo "Reverb: ws://127.0.0.1:8080"
echo "=========================================="
echo ""
echo "To stop all services, run: ./stop-multi-nodes.sh"
echo "To view logs: tail -f $LOG_DIR/*.log"

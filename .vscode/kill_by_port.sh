#!/bin/bash

# 安全终止占用端口的进程
PORT=$1
if [ -z "$PORT" ]; then
  echo "Usage: $0 <port>"
  exit 1
fi

# 查找并终止进程
pids=$(lsof -ti :$PORT)
if [ -n "$pids" ]; then
  echo "Killing processes on port $PORT:"
  echo "$pids"
  kill -9 $pids
else
  echo "No processes running on port $PORT"
fi

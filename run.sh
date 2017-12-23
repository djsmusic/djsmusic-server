#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
CONTAINER_NAME=djsmusic_server

echo "Running DJs Music Server (requires docker)"

docker run --name ${CONTAINER_NAME} -p "8081:80" -v ${PWD}/:/app -v ${PWD}/mysql:/var/lib/mysql mattrayner/lamp:latest-1604
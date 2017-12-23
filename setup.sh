#!/usr/bin/env bash

CONTAINER_NAME=djsmusic_server
DB_NAME=djsmusic

echo "Setting up DJs Music DB (requires the docker image to be running)"

docker exec ${CONTAINER_NAME} mysql -uroot -e "create database ${DB_NAME}"
docker exec ${CONTAINER_NAME} sh -c "mysql ${DB_NAME} < /app/src/lib/db/structure.sql"
docker exec ${CONTAINER_NAME} sh -c "mysql ${DB_NAME} < /app/src/lib/db/testData.sql"
#!/usr/bin/env bash
set -e

test_database="${POSTGRES_TEST_DB:-video_monitor_test}"

if [ -z "${test_database}" ]; then
    echo "POSTGRES_TEST_DB is empty; skipping test database creation."
    exit 0
fi

if psql --username "${POSTGRES_USER}" --dbname "${POSTGRES_DB}" --tuples-only --command "SELECT 1 FROM pg_database WHERE datname = '${test_database}'" | grep -q 1; then
    echo "Test database ${test_database} already exists."
else
    echo "Creating test database ${test_database}."
    psql --username "${POSTGRES_USER}" --dbname "${POSTGRES_DB}" --command "CREATE DATABASE \"${test_database}\""
fi

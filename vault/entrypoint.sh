#!/bin/sh
# Starts Vault (dev mode), loads the default config (init.sh), then keeps running.
set -e

: "${VAULT_DEV_ROOT_TOKEN_ID:?VAULT_DEV_ROOT_TOKEN_ID is not set (vault.env)}"

rm -f /tmp/vault-init-done

docker-entrypoint.sh server -dev &
VAULT_PID=$!
trap 'kill -TERM $VAULT_PID; wait $VAULT_PID' TERM INT

VAULT_TOKEN="$VAULT_DEV_ROOT_TOKEN_ID" /vault-init/init.sh
touch /tmp/vault-init-done

wait $VAULT_PID

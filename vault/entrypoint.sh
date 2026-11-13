#!/bin/sh
# Starts Vault (dev mode), loads the default config (init.sh), then keeps running.
set -e

: "${ROOT_VAULT_TOKEN_ID:?ROOT_VAULT_TOKEN_ID is not set (vault/Dockerfile)}"

rm -f /tmp/vault-init-done

docker-entrypoint.sh server -dev -dev-root-token-id="$ROOT_VAULT_TOKEN_ID" &
VAULT_PID=$!
trap 'kill -TERM $VAULT_PID; wait $VAULT_PID' TERM INT

VAULT_TOKEN="$ROOT_VAULT_TOKEN_ID" /vault-init/init.sh
touch /tmp/vault-init-done

wait $VAULT_PID

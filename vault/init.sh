#!/bin/sh
# Loads the default E-IMZO API keys into Vault and creates a read-only token for the proxy.
set -e

: "${NGINX_VAULT_TOKEN:?NGINX_VAULT_TOKEN is not set (vault.env)}"

until vault status >/dev/null 2>&1; do
  echo "waiting for vault..."
  sleep 1
done

vault kv put secret/eimzo/api-keys @/vault-init/api-keys.json
vault policy write eimzo-nginx /vault-init/nginx-policy.hcl

vault token revoke "$NGINX_VAULT_TOKEN" >/dev/null 2>&1 || true
vault token create -id="$NGINX_VAULT_TOKEN" -policy=eimzo-nginx -period=768h >/dev/null

# Hand only the read-only token to the proxy through the shared volume
mkdir -p /run/vault-token
umask 077
printf '%s' "$NGINX_VAULT_TOKEN" > /run/vault-token/nginx-token
chmod 444 /run/vault-token/nginx-token

echo "vault init done"

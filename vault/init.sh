#!/bin/sh
# Loads the default E-IMZO API keys into Vault and creates a read-only token for nginx.
set -e

until vault status >/dev/null 2>&1; do
  echo "waiting for vault..."
  sleep 1
done

vault kv put secret/eimzo/api-keys @/vault-init/api-keys.json
vault policy write eimzo-nginx /vault-init/nginx-policy.hcl

# Fixed-id token so nginx can use it from its env (dev setup)
vault token revoke "$NGINX_VAULT_TOKEN" >/dev/null 2>&1 || true
vault token create -id="$NGINX_VAULT_TOKEN" -policy=eimzo-nginx -period=768h >/dev/null

echo "vault init done"

#!/bin/sh
# Fetches E-IMZO API keys from HashiCorp Vault and writes them as
# /etc/nginx/eimzo/api-keys.json -> ["domain","key","domain","key",...]
# Then refreshes them in the background every VAULT_REFRESH_INTERVAL seconds.

OUT=/etc/nginx/eimzo/api-keys.json
VAULT_ADDR=${VAULT_ADDR:-http://vault:8200}
VAULT_SECRET_PATH=${VAULT_SECRET_PATH:-secret/data/eimzo/api-keys}
VAULT_REFRESH_INTERVAL=${VAULT_REFRESH_INTERVAL:-300}

fetch_keys() {
  resp=$(curl -sf -H "X-Vault-Token: $VAULT_TOKEN" "$VAULT_ADDR/v1/$VAULT_SECRET_PATH") || return 1
  echo "$resp" | jq -c '.data.data // {} | to_entries | map([.key, .value]) | add // []' > "$OUT.tmp" || return 1
  mv "$OUT.tmp" "$OUT"
}

[ -f "$OUT" ] || echo '[]' > "$OUT"

i=0
until fetch_keys; do
  i=$((i + 1))
  if [ "$i" -ge 30 ]; then
    echo "$0: WARNING: could not read API keys from Vault, serving defaults only" >&2
    break
  fi
  echo "$0: waiting for Vault..."
  sleep 2
done
[ "$i" -lt 30 ] && echo "$0: API keys loaded from Vault"

(
  while true; do
    sleep "$VAULT_REFRESH_INTERVAL"
    fetch_keys || echo "$0: WARNING: Vault refresh failed, keeping previous keys" >&2
  done
) &

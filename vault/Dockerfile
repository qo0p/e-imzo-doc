FROM hashicorp/vault:1.18

COPY api-keys.json nginx-policy.hcl init.sh entrypoint.sh /vault-init/
RUN chmod +x /vault-init/*.sh

ENV VAULT_DEV_LISTEN_ADDRESS=0.0.0.0:8200 \
    VAULT_ADDR=http://127.0.0.1:8200

EXPOSE 8200

HEALTHCHECK --interval=5s --timeout=3s --start-period=10s --retries=10 \
    CMD test -f /tmp/vault-init-done && vault status >/dev/null || exit 1

ENTRYPOINT ["/vault-init/entrypoint.sh"]

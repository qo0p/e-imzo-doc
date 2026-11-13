FROM hashicorp/vault:1.18

COPY api-keys.json nginx-policy.hcl init.sh entrypoint.sh /vault-init/

RUN chmod +x /vault-init/*.sh

ENV VAULT_DEV_LISTEN_ADDRESS=0.0.0.0:8200 \
    VAULT_ADDR=http://127.0.0.1:8200 \
    ROOT_VAULT_TOKEN_ID=8cd349bb3959eebf18588e0f9b502acb76166b6bfbb6c8e7

EXPOSE 8200

ENTRYPOINT ["/vault-init/entrypoint.sh"]

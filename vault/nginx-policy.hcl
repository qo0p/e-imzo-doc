# nginx can only read the E-IMZO API keys
path "secret/data/eimzo/api-keys" {
  capabilities = ["read"]
}

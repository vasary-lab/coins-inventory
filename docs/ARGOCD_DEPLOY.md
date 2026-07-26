# ArgoCD quick deploy

## Chart

Helm repository:

```text
https://vasary-lab.github.io/coins-inventory
```

Chart name:

```text
coins-inventory
```

Use the release tag as the chart `targetRevision`, for example `1.2.3`.

Minimal ArgoCD Application:

```yaml
apiVersion: argoproj.io/v1alpha1
kind: Application
metadata:
  name: coins-inventory
  namespace: argocd
spec:
  project: default
  source:
    repoURL: https://vasary-lab.github.io/coins-inventory
    chart: coins-inventory
    targetRevision: 1.2.3
    helm:
      values: |
        image:
          repository: ghcr.io/vasary-lab/coins-inventory:sha-REPLACE_WITH_COMMIT_SHORT_SHA
          pullPolicy: Always
        ingress:
          enabled: true
          className: nginx
          hosts:
            - host: coins-inventory.example.org
              paths:
                - path: /
                  pathType: ImplementationSpecific
  destination:
    server: https://kubernetes.default.svc
    namespace: coins-inventory
  syncPolicy:
    automated:
      prune: true
      selfHeal: true
    syncOptions:
      - CreateNamespace=true
```

The chart packaged by CI replaces the image with the pushed commit SHA in the format `ghcr.io/vasary-lab/coins-inventory:sha-<short-sha>`. If Argo uses the packaged chart from the Helm repo, normally only `targetRevision`, ingress host and secret values need to be changed.

## Required secret

The chart mounts Kubernetes secret `coins-inventory-config` as `/app/.env.prod`.

Create it in the application namespace:

```bash
kubectl -n coins-inventory create secret generic coins-inventory-config \
  --from-literal=.env.prod='APP_ENV=prod
APP_DEBUG=false
APP_SECRET=replace-with-random-secret
DB_HOST=redis-master.coins-inventory.svc.cluster.local
DB_PORT=6379
DB_PASSWORD=replace-with-redis-password
DB_USER=default'
```

Required environment variables:

```text
APP_ENV=prod
APP_DEBUG=false
APP_SECRET=<random Symfony secret>
DB_HOST=<Redis host>
DB_PORT=6379
DB_PASSWORD=<Redis password>
DB_USER=default
```

## Required services

Application service:

```text
coins-inventory:80
```

Redis:

```text
Redis 7.x, TCP 6379, AUTH enabled
```

External egress:

```text
https://xaus.com/api/v1/
```

Health checks:

```text
GET /api/maintenance/health
```

Public API:

```text
GET /api/inventory
PUT /api/inventory
GET /api/inventory/profitability
POST /_mcp
```

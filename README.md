# ⚡ DevFlow — Laravel DevOps Practice App

A **production-grade** Laravel task management app built specifically for practising real DevOps tools. Every part of the stack is wired up, documented, and ready to use.

---

## 📦 What's Inside

```
devflow/
├── app/                        # Laravel application
│   ├── Http/Controllers/       # Web + API controllers
│   ├── Models/                 # Eloquent models with policies
│   └── Jobs/                   # Queue jobs (Redis)
├── database/
│   ├── migrations/             # All table migrations
│   └── seeders/                # Demo data seeder
├── resources/views/            # Blade templates (dark UI)
├── routes/
│   ├── web.php                 # Web routes (auth protected)
│   └── api.php                 # REST API (Sanctum)
│
├── 🐳 DOCKER
│   ├── Dockerfile              # Multi-stage build (dev + prod targets)
│   ├── docker-compose.yml      # Full stack: app, nginx, mysql, redis, workers
│   └── docker/
│       ├── php/                # PHP-FPM, OPcache, Xdebug configs
│       ├── mysql/              # MySQL tuning + init SQL
│       ├── redis/              # Redis persistence + memory config
│       ├── supervisor/         # Supervisor (PHP-FPM + queue workers)
│       └── entrypoint.sh       # Container startup script
│
├── 🔄 CI/CD
│   └── .github/workflows/
│       └── ci.yml              # Full pipeline: lint → test → build → deploy
│
├── ☸️  KUBERNETES
│   └── k8s/
│       └── devflow.yml         # Namespace, Deployments, Services, Ingress, HPA
│
├── 📊 MONITORING
│   ├── prometheus/
│   │   ├── prometheus.yml      # Scrape configs (nginx, mysql, redis, app)
│   │   └── rules/alerts.yml    # Alerting rules
│   └── grafana/
│       └── provisioning/       # Auto-provisioned datasource + dashboards
│
├── 🌍 INFRASTRUCTURE AS CODE
│   └── terraform/
│       └── main.tf             # Full AWS stack (VPC, ECS, RDS, ElastiCache, ALB)
│
└── nginx/
    ├── nginx.conf              # Main Nginx config (gzip, rate limiting, logging)
    └── default.conf            # Virtual host (PHP-FPM proxy, security headers)
```

---

## 🚀 Quick Start

### 1. Clone and configure
```bash
git clone <your-repo>
cd devflow
cp .env.example .env
```

### 2. Start the entire stack
```bash
docker compose up -d
```

This starts **10 services**: nginx, php-fpm, mysql, redis, queue worker (×2), scheduler, mailpit, prometheus, grafana + exporters.

### 3. That's it — everything is auto-configured
The entrypoint script automatically:
- Waits for MySQL and Redis to be ready
- Runs migrations
- Seeds demo data (5 users, 5 projects, ~60 tasks)

---

## 🌐 Access Points

| Service     | URL                          | Credentials        |
|-------------|------------------------------|--------------------|
| **App**     | http://localhost              | admin@devflow.local / password |
| **Grafana** | http://localhost:3000         | admin / admin      |
| **Prometheus** | http://localhost:9090      | —                  |
| **Mailpit** | http://localhost:8025         | —                  |
| **MySQL**   | localhost:3306                | devflow / secret   |
| **Redis**   | localhost:6379                | —                  |

---

## 🐳 Docker Practice

### Basic operations
```bash
# View all running containers
docker compose ps

# View logs in real-time
docker compose logs -f app
docker compose logs -f worker
docker compose logs -f nginx

# Shell into the app container
docker compose exec app sh

# Run Artisan commands
docker compose exec app php artisan tinker
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan queue:work --once

# Scale queue workers up/down
docker compose up --scale worker=4 -d
docker compose up --scale worker=1 -d
```

### Health checks
```bash
# Check container health status
docker compose ps

# Hit the health endpoint
curl http://localhost/health | jq

# View container resource usage
docker stats
```

### Image inspection
```bash
# Build production image
docker build --target production -t devflow:prod .

# Inspect image layers
docker history devflow:prod

# Scan for vulnerabilities
docker scout cves devflow:prod
```

---

## 🔄 CI/CD Practice

The GitHub Actions pipeline (`.github/workflows/ci.yml`) runs:

1. **Code Quality** — PHP CS (Pint), static analysis
2. **Tests** — PHPUnit on PHP 8.2 + 8.3, with real MySQL + Redis services
3. **Security** — Composer audit + Trivy vulnerability scan
4. **Build** — Multi-stage Docker build, push to GHCR
5. **Deploy Staging** — SSH deploy on `develop` branch
6. **Deploy Production** — Manual approval gate on `main` branch

### Setup
1. Push to GitHub
2. Set repository secrets:
   - `STAGING_HOST`, `STAGING_USER`, `STAGING_SSH_KEY`
   - `PROD_HOST`, `PROD_USER`, `PROD_SSH_KEY`
   - `SLACK_WEBHOOK_URL`
3. Create `staging` and `production` environments in GitHub with protection rules

---

## 📊 Monitoring Practice

### Prometheus
Visit http://localhost:9090 and try these queries:

```promql
# HTTP requests per second
rate(nginx_http_requests_total[5m])

# 95th percentile response time
histogram_quantile(0.95, rate(nginx_request_duration_seconds_bucket[5m]))

# MySQL connections
mysql_global_status_threads_connected

# Redis memory usage
redis_memory_used_bytes / redis_memory_max_bytes

# Queue depth (requires custom metric)
redis_list_length{key="queues:default"}
```

### Grafana
Visit http://localhost:3000 (admin/admin).

Import these community dashboards:
- **NGINX**: Dashboard ID `12708`
- **MySQL**: Dashboard ID `7362`
- **Redis**: Dashboard ID `11835`
- **Node Exporter**: Dashboard ID `1860`
- **PHP-FPM**: Dashboard ID `4912`

### Trigger alerts for practice
```bash
# Simulate high CPU (run many requests)
ab -n 10000 -c 100 http://localhost/

# Flood the queue
docker compose exec app php artisan tinker
>>> for ($i=0; $i<500; $i++) { \App\Jobs\SendProjectNotification::dispatch(\App\Models\Project::first(), 'test'); }

# Stop the worker to let queue pile up
docker compose stop worker
```

---

## ☸️ Kubernetes Practice

### Local (Minikube/Kind)
```bash
# Start local cluster
minikube start

# Apply all manifests
kubectl apply -f k8s/

# Watch pods come up
kubectl get pods -n devflow -w

# View the app
minikube service nginx-service -n devflow

# Scale the app
kubectl scale deployment devflow-app --replicas=5 -n devflow

# Trigger a rolling update
kubectl set image deployment/devflow-app app=ghcr.io/yourorg/devflow:newtag -n devflow
kubectl rollout status deployment/devflow-app -n devflow

# Rollback
kubectl rollout undo deployment/devflow-app -n devflow

# View HPA in action (run load test first)
kubectl get hpa -n devflow -w
```

### Useful kubectl commands
```bash
# Get all resources
kubectl get all -n devflow

# Exec into a pod
kubectl exec -it <pod-name> -n devflow -- sh

# View logs
kubectl logs -f deployment/devflow-app -n devflow

# Describe a resource (great for debugging)
kubectl describe pod <pod-name> -n devflow

# Port forward (test without ingress)
kubectl port-forward service/nginx-service 8080:80 -n devflow
```

---

## 🌍 Terraform Practice

```bash
cd terraform

# Initialize (download providers)
terraform init

# Preview what will be created
terraform plan -var="db_password=secret" -var="app_key=base64:xxx"

# Apply (creates real AWS resources — costs money!)
terraform apply

# Destroy everything
terraform destroy

# Format and validate
terraform fmt
terraform validate
```

**Resources created:** VPC, public/private subnets, NAT gateway, security groups, ECR, RDS MySQL, ElastiCache Redis, ECS cluster, ALB.

---

## 🔗 REST API

The app exposes a full REST API at `/api/v1/`. All endpoints require a Sanctum Bearer token.

### Get a token
```bash
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@devflow.local","password":"password"}'
```

### Use the token
```bash
TOKEN="your_token_here"

# List projects
curl http://localhost/api/v1/projects \
  -H "Authorization: Bearer $TOKEN"

# Create a project
curl -X POST http://localhost/api/v1/projects \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"New Project","status":"active","priority":"high"}'

# Get project stats
curl http://localhost/api/v1/projects/1/stats \
  -H "Authorization: Bearer $TOKEN"

# List tasks
curl http://localhost/api/v1/projects/1/tasks \
  -H "Authorization: Bearer $TOKEN"

# Update task status
curl -X PATCH http://localhost/api/v1/tasks/1/status \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status":"in_progress"}'
```

---

## 🧪 Running Tests

```bash
# Run all tests
docker compose exec app php artisan test

# Run with coverage
docker compose exec app vendor/bin/phpunit --coverage-html coverage/

# Run specific test
docker compose exec app php artisan test --filter ProjectTest
```

---

## 💡 Practice Scenarios

| Scenario | What to do |
|----------|-----------|
| **Zero-downtime deploy** | `docker compose up --no-deps --scale app=2 app`, then `--scale app=1` |
| **DB migration in prod** | Add a new migration, run rolling deploy |
| **Queue failure** | Stop worker, dispatch jobs, restart, watch retry |
| **Cache invalidation** | Flush Redis, observe cache miss in Grafana |
| **HPA scaling** | Run `ab` load test, watch `kubectl get hpa` |
| **Alertmanager** | Break a service, see alert fire in Prometheus |
| **Log aggregation** | Pipe Nginx JSON logs to ELK or Loki |
| **SSL/TLS** | Uncomment HTTPS block in nginx config |
| **DB replica** | Add read replica in docker-compose, update Laravel config |

---

## 🔐 Security

- All secrets via environment variables (never hardcoded)
- CSRF protection on all forms
- Rate limiting on login (5/min) and API (30/min)
- Content Security Policy headers
- SQL injection prevention via Eloquent ORM
- Sanctum API token auth
- Non-root Docker user (`www`)
- Trivy vulnerability scanning in CI

---

*Built for DevOps practice. Every config file is heavily commented to explain the why, not just the what.*

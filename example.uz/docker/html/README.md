
# E-Imzo Demo Application Setup

This guide provides step-by-step instructions to set up the E-Imzo demo application on a development environment using Docker and NGINX.

## Prerequisites

- **Docker**: Ensure Docker is installed on your system.
- **TCP Ports**: Verify that TCP ports `80`, `8080`, and `9123` are not occupied by other services.

## Setup Instructions

### Step 1: Copy the Demo Directory

1. Locate the `demo` directory in `example.uz/php`.
2. Copy the contents of the `demo` directory into the `html` directory within your Docker project structure.

   Your directory structure should look like this:

   ```plaintext
   example.uz/
   ├── docker/
   │   ├── html/            <-- Place the `demo` contents here
   │   │   ├── auth.php
   │   │   ├── cabinet.php
   │   │   ├── config.php
   │   │   └── ... other files from demo
   └── php/
       └── demo/
           ├── auth.php
           ├── cabinet.php
           ├── config.php
           └── ... other files
   ```

### Step 2: Build the Docker Image

Navigate to the `docker` directory in your terminal and build the Docker image using the following command:

```bash
docker build -t e-imzo-demo:latest .
```

This command creates a Docker image tagged as `e-imzo-demo:latest`.

### Step 3: Configure NGINX

Set up the NGINX configuration to serve the application. Ensure that the NGINX configuration file points to the correct document root and includes any necessary proxy settings.

#### Example NGINX Configuration

Open the `nginx.conf` file (located in `c:\nginx\conf\nginx.conf` or your custom location) and edit the `server` block to match the following example:

```nginx
server {
    listen       80;
    server_name  localhost;

    index index.php;
    root /var/www/html;

    location /frontend {
        proxy_set_header   Host             $host;
        proxy_set_header   X-Real-IP        $remote_addr;
        proxy_set_header   X-Forwarded-For  $proxy_add_x_forwarded_for;
        proxy_pass http://127.0.0.1:8080;
    }

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_index index.php;
        fastcgi_pass 127.0.0.1:9123;
    }
}
```

> **Note**: Adjust the paths in the `nginx.conf` file to fit your specific environment if needed.

### Step 4: Start NGINX and Access the Demo

1. Start NGINX using the following command:
   ```bash
   nginx
   ```
   Ensure that you run this command in the correct directory and with appropriate permissions (run as Administrator on Windows).

2. Open a web browser and go to `http://localhost/demo/index.php` to access the E-Imzo demo application.

---

## Additional Information

- **Docker Commands**:
    - To stop the container:
      ```bash
      docker stop e-imzo-demo
      ```
    - To remove the container:
      ```bash
      docker rm e-imzo-demo
      ```

- **Health Check**:
    - You can add a health check to your Dockerfile to ensure that NGINX is running. For example:
      ```dockerfile
      HEALTHCHECK --interval=30s --timeout=10s --start-period=10s --retries=3 \
        CMD curl -f http://localhost/ || exit 1
      ```

- **PHP Extensions**:
    - Ensure necessary PHP extensions (`mysqli`, `pdo`, `pdo_mysql`, etc.) are enabled if your application requires them.

## Troubleshooting

- **Port Conflicts**: Ensure that ports `80`, `8080`, and `9123` are available and not used by other applications.
- **Permissions**: If you encounter permission issues, verify that the `html` directory and its files are owned by the appropriate user (e.g., `www-data` for Apache).

---

By following these instructions, you should have the E-Imzo demo application up and running locally using Docker and NGINX. Adjust any configurations as needed to fit your specific environment setup.

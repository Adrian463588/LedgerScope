# GCP Project Settings
project_id   = "project-654b743a-b24b-45ad-85e"
region       = "asia-southeast1" # Singapore region for Southeast Asia
zone         = "asia-southeast1-b"

# VM Configuration (e2-medium is cheap, 2 vCPUs, 4GB RAM)
machine_type = "e2-medium"
disk_size_gb = 40

# SSH Configuration
ssh_user            = "coolify"
ssh_public_key_path = "~/.ssh/id_rsa.pub"

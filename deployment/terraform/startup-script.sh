#!/bin/bash
set -e

# Log output to a file for debugging
exec > >(tee -i /var/log/coolify-bootstrap.log) 2>&1

echo "=== LedgerScope Coolify Bootstrap Started ==="
date

# 1. Update system package manager
echo "Updating packages..."
apt-get update -y
apt-get upgrade -y

# 2. Setup Swap Space (4GB)
# e2-medium only has 4GB RAM, swap space is crucial to prevent OOM crash
echo "Setting up swap memory (4GB)..."
if [ ! -f /swapfile ]; then
    fallocate -l 4G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
    echo "Swap space created successfully."
else
    echo "Swap space already exists."
fi

# Verify swap status
free -h

# 3. Install Docker and dependencies
echo "Installing base utilities..."
apt-get install -y curl wget git jq software-properties-common ufw

# 4. Install Coolify (non-interactive, automated)
echo "Installing Coolify..."
curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash

echo "=== LedgerScope Coolify Bootstrap Completed ==="
date

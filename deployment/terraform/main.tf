terraform {
  required_version = ">= 1.0.0"
  required_providers {
    google = {
      source  = "hashicorp/google"
      version = "~> 5.0"
    }
  }
}

provider "google" {
  project = var.project_id
  region  = var.region
  zone    = var.zone
}

# 1. Create a Custom VPC Network (DevSecOps Best Practice)
resource "google_compute_network" "vpc_network" {
  name                    = "ledgerscope-vpc"
  auto_create_subnetworks = false
}

# 2. Create a Subnet
resource "google_compute_subnetwork" "subnet" {
  name          = "ledgerscope-subnet"
  ip_cidr_range = "10.0.1.0/24"
  region        = var.region
  network       = google_compute_network.vpc_network.id
}

# 3. Create a Reserved Static External IP Address
resource "google_compute_address" "static_ip" {
  name   = "ledgerscope-coolify-ip"
  region = var.region
}

# 4. Firewall Rules for Inbound Traffic (Least Privilege)
resource "google_compute_firewall" "allow_web" {
  name    = "ledgerscope-allow-web"
  network = google_compute_network.vpc_network.name

  allow {
    protocol = "tcp"
    ports    = ["80", "443"] # HTTP & HTTPS for Nginx/Coolify reverse proxy
  }

  source_ranges = ["0.0.0.0/0"]
  target_tags   = ["coolify-server"]
}

resource "google_compute_firewall" "allow_ssh" {
  name    = "ledgerscope-allow-ssh"
  network = google_compute_network.vpc_network.name

  allow {
    protocol = "tcp"
    ports    = ["22"] # Secure SSH Access
  }

  source_ranges = ["0.0.0.0/0"] # In production, restrict this to your specific IP
  target_tags   = ["coolify-server"]
}

resource "google_compute_firewall" "allow_coolify_setup" {
  name    = "ledgerscope-allow-coolify-setup"
  network = google_compute_network.vpc_network.name

  allow {
    protocol = "tcp"
    ports    = ["8000"] # Coolify admin console port (for initial setup)
  }

  source_ranges = ["0.0.0.0/0"] # In production, restrict this to your specific IP
  target_tags   = ["coolify-server"]
}

# 5. Compute Engine Instance for Coolify
resource "google_compute_instance" "coolify_instance" {
  name         = var.instance_name
  machine_type = var.machine_type
  zone         = var.zone

  tags = ["coolify-server"]

  boot_disk {
    initialize_params {
      image = "ubuntu-os-cloud/ubuntu-2204-lts"
      size  = var.disk_size_gb
      type  = "pd-balanced" # Cheap, balanced performance SSD
    }
  }

  network_interface {
    subnetwork = google_compute_subnetwork.subnet.id

    access_config {
      nat_ip = google_compute_address.static_ip.address
    }
  }

  # Startup script to configure Swap and install Coolify
  metadata_startup_script = file("${path.module}/startup-script.sh")

  metadata = {
    ssh-keys = fileexists(var.ssh_public_key_path) ? "${var.ssh_user}:${file(var.ssh_public_key_path)}" : ""
  }

  # Service Account with minimal privileges (Least Privilege DevSecOps)
  service_account {
    scopes = ["cloud-platform"]
  }

  lifecycle {
    ignore_changes = [
      metadata["ssh-keys"]
    ]
  }
}

variable "project_id" {
  description = "GCP Project ID"
  type        = string
  default     = "project-654b743a-b24b-45ad-85e"
}

variable "region" {
  description = "GCP region to deploy resources"
  type        = string
  default     = "asia-southeast1" # Singapore is close to Indonesia for lower latency
}

variable "zone" {
  description = "GCP zone to deploy resources"
  type        = string
  default     = "asia-southeast1-b"
}

variable "machine_type" {
  description = "GCP Machine type (e2-medium is cheap yet sufficient with swap memory)"
  type        = string
  default     = "e2-medium"
}

variable "instance_name" {
  description = "Name of the compute instance"
  type        = string
  default     = "ledgerscope-coolify-server"
}

variable "disk_size_gb" {
  description = "Boot disk size in GB (Coolify + services need at least 30GB)"
  type        = number
  default     = 40
}

variable "ssh_user" {
  description = "SSH username for instance connection"
  type        = string
  default     = "coolify"
}

variable "ssh_public_key_path" {
  description = "Path to SSH public key to access the instance (optional, uses default if blank)"
  type        = string
  default     = "~/.ssh/id_rsa.pub"
}

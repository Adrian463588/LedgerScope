output "public_ip" {
  description = "The public static IP address allocated to the Coolify instance"
  value       = google_compute_address.static_ip.address
}

output "coolify_setup_url" {
  description = "URL for the Coolify initial setup page (Available after installation completes)"
  value       = "http://${google_compute_address.static_ip.address}:8000"
}

output "ssh_command" {
  description = "SSH command to connect to the VM instance"
  value       = "ssh ${var.ssh_user}@${google_compute_address.static_ip.address}"
}

output "bootstrap_log_check" {
  description = "Command to monitor the Coolify installation progress on the VM"
  value       = "tail -f /var/log/coolify-bootstrap.log"
}
